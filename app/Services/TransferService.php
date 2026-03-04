<?php

namespace App\Services;

use App\Enums\TransferStatusEnum;
use App\Enums\TransferTypeEnum;
use App\Models\Asset;
use App\Models\Opd;
use App\Models\Room;
use App\Models\TransferItem;
use App\Models\TransferRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $status = null,
        ?string $type = null,
        ?string $fromOpdId = null,
        ?string $toOpdId = null
    ): array {
        $query = TransferRequest::query()
            ->with([
                'fromOpd',
                'toOpd',
                'requester',
                'approver',
                'items.asset',
            ])
            ->latest('requested_at');

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhereHas('fromOpd', function (Builder $sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('toOpd', function (Builder $sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($fromOpdId) {
            $query->opd($fromOpdId);
        }

        if ($toOpdId) {
            $query->opd($toOpdId);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $currentOpdId = $fromOpdId ?? $toOpdId ?? null;

        if ($currentOpdId) {
            $paginator->setCollection(
                $paginator->getCollection()->map(function (TransferRequest $transfer) use ($currentOpdId) {
                    $direction = null;

                    if ($transfer->from_opd_id === $currentOpdId && $transfer->to_opd_id === $currentOpdId) {
                        $direction = 'internal';
                    } elseif ($transfer->from_opd_id === $currentOpdId) {
                        $direction = 'outgoing';
                    } elseif ($transfer->to_opd_id === $currentOpdId) {
                        $direction = 'incoming';
                    }

                    $directionLabel = null;

                    if ($direction === 'incoming') {
                        $directionLabel = 'Masuk ke OPD Saya';
                    } elseif ($direction === 'outgoing') {
                        $directionLabel = 'Keluar dari OPD Saya';
                    } elseif ($direction === 'internal') {
                        $directionLabel = 'Internal OPD Saya';
                    }

                    $transfer->setAttribute('direction', $direction);
                    $transfer->setAttribute('direction_label', $directionLabel);
                    $transfer->setAttribute('is_incoming', $direction === 'incoming');
                    $transfer->setAttribute('is_outgoing', $direction === 'outgoing');
                    $transfer->setAttribute('is_internal_local', $direction === 'internal');

                    return $transfer;
                })
            );
        }

        $statusOptions = TransferStatusEnum::toOptions();
        $typeOptions = TransferTypeEnum::toOptions();

        return [
            'items' => $paginator,
            'status_options' => $statusOptions,
            'type_options' => $typeOptions,
        ];
    }

    public function findById(string $id, ?string $currentOpdId = null): TransferRequest
    {
        $transfer = TransferRequest::with([
            'fromOpd',
            'toOpd',
            'requester',
            'approver',
            'items.asset',
            'items.fromRoom',
            'items.toRoom',
        ])->findOrFail($id);

        if ($currentOpdId) {
            $direction = null;

            if ($transfer->from_opd_id === $currentOpdId && $transfer->to_opd_id === $currentOpdId) {
                $direction = 'internal';
            } elseif ($transfer->from_opd_id === $currentOpdId) {
                $direction = 'outgoing';
            } elseif ($transfer->to_opd_id === $currentOpdId) {
                $direction = 'incoming';
            }

            $directionLabel = null;

            if ($direction === 'incoming') {
                $directionLabel = 'Masuk ke OPD Saya';
            } elseif ($direction === 'outgoing') {
                $directionLabel = 'Keluar dari OPD Saya';
            } elseif ($direction === 'internal') {
                $directionLabel = 'Internal OPD Saya';
            }

            $transfer->setAttribute('direction', $direction);
            $transfer->setAttribute('direction_label', $directionLabel);
            $transfer->setAttribute('is_incoming', $direction === 'incoming');
            $transfer->setAttribute('is_outgoing', $direction === 'outgoing');
            $transfer->setAttribute('is_internal_local', $direction === 'internal');
        }

        return $transfer;
    }

    public function createTransfer(array $data, string $requestedByUserId, ?string $currentOpdId): TransferRequest
    {
        return DB::transaction(function () use ($data, $requestedByUserId, $currentOpdId) {
            $fromOpdId = $data['from_opd_id'];
            $toOpdId = $data['to_opd_id'];

            if ($currentOpdId && $fromOpdId !== $currentOpdId) {
                abort(403, 'You can only create transfers from your current OPD.');
            }

            /** @var Opd $fromOpd */
            $fromOpd = Opd::findOrFail($fromOpdId);
            /** @var Opd $toOpd */
            $toOpd = Opd::findOrFail($toOpdId);

            if ($fromOpd->id === $toOpd->id) {
                $type = TransferTypeEnum::INTERNAL;
            } else {
                $type = TransferTypeEnum::EXTERNAL;
            }

            $status = $type->isInternal()
                ? TransferStatusEnum::COMPLETED
                : TransferStatusEnum::PENDING;

            $transfer = TransferRequest::create([
                'transfer_number' => $this->generateTransferNumber(),
                'type' => $type->value,
                'from_opd_id' => $fromOpd->id,
                'to_opd_id' => $toOpd->id,
                'status' => $status->value,
                'requested_by' => $requestedByUserId,
                'requested_at' => Carbon::now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $items = $data['items'] ?? [];

            foreach ($items as $item) {
                /** @var Asset $asset */
                $asset = Asset::with(['room', 'opd'])->findOrFail($item['asset_id']);

                if ($asset->opd_id !== $fromOpd->id) {
                    abort(422, 'Asset does not belong to the source OPD.');
                }

                $fromRoomId = $item['from_room_id'] ?? $asset->room_id;
                $toRoomId = $item['to_room_id'] ?? null;

                if ($toRoomId && $fromRoomId && $toRoomId === $fromRoomId) {
                    abort(422, 'Destination room must be different from source room.');
                }

                if ($toRoomId) {
                    /** @var Room $toRoom */
                    $toRoom = Room::findOrFail($toRoomId);

                    if ($toRoom->opd_id !== $toOpd->id) {
                        abort(422, 'Destination room does not belong to the destination OPD.');
                    }
                }

                TransferItem::create([
                    'transfer_request_id' => $transfer->id,
                    'asset_id' => $asset->id,
                    'from_room_id' => $fromRoomId,
                    'to_room_id' => $toRoomId,
                ]);

                if ($type->isInternal()) {
                    $asset->room_id = $toRoomId;
                    $asset->save();
                }
            }

            if ($type->isInternal()) {
                $transfer->status = TransferStatusEnum::COMPLETED;
                $transfer->completed_at = now();
                $transfer->save();
            }

            return $this->findById($transfer->id, $currentOpdId);
        });
    }

    public function approveTransfer(TransferRequest $transfer, string $approverUserId, ?string $currentOpdId): TransferRequest
    {
        return DB::transaction(function () use ($transfer, $approverUserId, $currentOpdId) {
            if (! $transfer->type->requiresApproval()) {
                abort(422, 'Only external transfers require approval.');
            }

            if (! $transfer->status->canBeApproved()) {
                abort(422, 'This transfer cannot be approved.');
            }

            if ($currentOpdId && $transfer->to_opd_id !== $currentOpdId) {
                abort(403, 'Only destination OPD can approve this transfer.');
            }

            $items = $transfer->items()->with('asset')->get();

            foreach ($items as $item) {
                /** @var TransferItem $item */
                /** @var Asset $asset */
                $asset = $item->asset;

                $asset->opd_id = $transfer->to_opd_id;
                $asset->room_id = $item->to_room_id;
                $asset->save();
            }

            $transfer->status = TransferStatusEnum::COMPLETED;
            $transfer->approved_by = $approverUserId;
            $transfer->approved_at = Carbon::now();
            $transfer->completed_at = Carbon::now();
            $transfer->save();

            return $this->findById($transfer->id, $currentOpdId);
        });
    }

    public function rejectTransfer(TransferRequest $transfer, string $approverUserId, ?string $currentOpdId, ?string $reason = null): TransferRequest
    {
        return DB::transaction(function () use ($transfer, $approverUserId, $currentOpdId, $reason) {
            if (! $transfer->type->requiresApproval()) {
                abort(422, 'Only external transfers can be rejected.');
            }

            if (! $transfer->status->canBeRejected()) {
                abort(422, 'This transfer cannot be rejected.');
            }

            if ($currentOpdId && $transfer->to_opd_id !== $currentOpdId) {
                abort(403, 'Only destination OPD can reject this transfer.');
            }

            $transfer->status = TransferStatusEnum::REJECTED;
            $transfer->approved_by = $approverUserId;
            $transfer->approved_at = Carbon::now();

            if ($reason) {
                $transfer->notes = trim(($transfer->notes ? $transfer->notes."\n\n" : '').'Alasan penolakan: '.$reason);
            }

            $transfer->save();

            return $this->findById($transfer->id, $currentOpdId);
        });
    }

    public function cancelTransfer(TransferRequest $transfer, ?string $currentOpdId): TransferRequest
    {
        return DB::transaction(function () use ($transfer, $currentOpdId) {
            if (! $transfer->type->requiresApproval()) {
                abort(422, 'Only external transfers can be cancelled.');
            }

            if (! $transfer->status->canBeRejected()) {
                abort(422, 'This transfer cannot be cancelled.');
            }

            if ($currentOpdId && $transfer->from_opd_id !== $currentOpdId) {
                abort(403, 'Only source OPD can cancel this transfer.');
            }

            $transfer->status = TransferStatusEnum::CANCEL;
            $transfer->save();

            return $this->findById($transfer->id, $currentOpdId);
        });
    }

    protected function generateTransferNumber(): string
    {
        $prefix = 'TRF-'.now()->format('Ymd').'-';

        do {
            $number = $prefix.Str::upper(Str::random(5));
        } while (TransferRequest::where('transfer_number', $number)->exists());

        return $number;
    }

    public function getStats(?string $currentOpdId): array
    {
        $baseQuery = TransferRequest::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where(function (Builder $sub) use ($currentOpdId) {
                    $sub->where('from_opd_id', $currentOpdId)
                        ->orWhere('to_opd_id', $currentOpdId);
                });
            });

        $totalTransfers = (clone $baseQuery)->count();

        $internalTransfers = (clone $baseQuery)
            ->where('type', TransferTypeEnum::INTERNAL)
            ->count();

        $externalTransfers = (clone $baseQuery)
            ->where('type', TransferTypeEnum::EXTERNAL)
            ->count();

        $pendingExternalTransfers = (clone $baseQuery)
            ->where('type', TransferTypeEnum::EXTERNAL)
            ->where('status', TransferStatusEnum::PENDING)
            ->count();

        return [
            'total_transfers' => $totalTransfers,
            'internal_transfers' => $internalTransfers,
            'external_transfers' => $externalTransfers,
            'pending_external_transfers' => $pendingExternalTransfers,
        ];
    }
}
