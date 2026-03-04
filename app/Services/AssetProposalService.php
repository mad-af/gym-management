<?php

namespace App\Services;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\ProposalStatusEnum;
use App\Models\Asset;
use App\Models\AssetProposal;
use App\Models\FundingSource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AssetProposalService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $status = null,
        ?string $opdId = null,
        ?string $categoryId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $proposedByUserId = null,
    ): array {
        $query = AssetProposal::query()
            ->with(['opd', 'proposer', 'category'])
            ->latest('proposal_date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('proposal_number', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($opdId) {
            $query->opd($opdId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($dateFrom) {
            $query->whereDate('proposal_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('proposal_date', '<=', $dateTo);
        }

        if ($proposedByUserId) {
            $query->where('proposed_by', $proposedByUserId);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $paginator,
            'status_options' => ProposalStatusEnum::toOptions(),
        ];
    }

    public function detail(AssetProposal $proposal): array
    {
        $proposal->load(['opd', 'proposer', 'category']);

        return [
            'item' => $proposal,
            'status_options' => ProposalStatusEnum::toOptions(),
        ];
    }

    public function create(array $data, string $proposedByUserId, ?string $currentOpdId): AssetProposal
    {
        return DB::transaction(function () use ($data, $proposedByUserId, $currentOpdId) {
            $opdId = $data['opd_id'];

            if ($currentOpdId && $opdId !== $currentOpdId) {
                abort(403, 'You can only create proposals for your current OPD.');
            }

            $qty = (int) $data['qty'];
            $estimatedPrice = (float) $data['estimated_price'];

            if ($qty <= 0) {
                abort(422, 'Quantity must be greater than zero.');
            }

            if ($estimatedPrice < 0) {
                abort(422, 'Estimated price must not be negative.');
            }

            $proposal = AssetProposal::create([
                'proposal_number' => $this->generateProposalNumber(),
                'opd_id' => $opdId,
                'proposed_by' => $proposedByUserId,
                'proposal_date' => Carbon::now()->toDateString(),
                'category_id' => $data['category_id'],
                'item_name' => $data['item_name'],
                'specification' => $data['specification'] ?? null,
                'qty' => $qty,
                'estimated_price' => $estimatedPrice,
                'status' => ProposalStatusEnum::SUBMITTED,
                'total_estimation' => $qty * $estimatedPrice,
                'notes' => $data['notes'] ?? null,
            ]);

            return $proposal->load(['opd', 'proposer', 'category']);
        });
    }

    public function update(AssetProposal $proposal, array $data, ?string $currentOpdId): AssetProposal
    {
        return DB::transaction(function () use ($proposal, $data, $currentOpdId) {
            $proposal->refresh();

            if ($proposal->status !== ProposalStatusEnum::SUBMITTED) {
                abort(422, 'Only submitted proposals can be updated.');
            }

            if ($currentOpdId && $proposal->opd_id !== $currentOpdId) {
                abort(403, 'You can only update proposals for your current OPD.');
            }

            $qty = isset($data['qty']) ? (int) $data['qty'] : $proposal->qty;
            $estimatedPrice = isset($data['estimated_price'])
                ? (float) $data['estimated_price']
                : (float) $proposal->estimated_price;

            if ($qty <= 0) {
                abort(422, 'Quantity must be greater than zero.');
            }

            if ($estimatedPrice < 0) {
                abort(422, 'Estimated price must not be negative.');
            }

            $updateData = [
                'category_id' => $data['category_id'] ?? $proposal->category_id,
                'item_name' => $data['item_name'] ?? $proposal->item_name,
                'specification' => $data['specification'] ?? $proposal->specification,
                'qty' => $qty,
                'estimated_price' => $estimatedPrice,
                'total_estimation' => $qty * $estimatedPrice,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $proposal->notes,
            ];

            $proposal->update($updateData);

            return $proposal->load(['opd', 'proposer', 'category']);
        });
    }

    public function delete(AssetProposal $proposal, ?string $currentOpdId): bool
    {
        return DB::transaction(function () use ($proposal, $currentOpdId) {
            $proposal->refresh();

            if ($proposal->status !== ProposalStatusEnum::SUBMITTED) {
                abort(422, 'Only submitted proposals can be deleted.');
            }

            if ($currentOpdId && $proposal->opd_id !== $currentOpdId) {
                abort(403, 'You can only delete proposals for your current OPD.');
            }

            return (bool) $proposal->delete();
        });
    }

    public function approve(AssetProposal $proposal, ?string $currentOpdId): AssetProposal
    {
        return DB::transaction(function () use ($proposal, $currentOpdId) {
            $proposal->refresh();

            if ($proposal->status !== ProposalStatusEnum::SUBMITTED) {
                abort(422, 'Only submitted proposals can be approved.');
            }

            if ($currentOpdId && $proposal->opd_id !== $currentOpdId) {
                abort(403, 'You can only approve proposals for your current OPD.');
            }

            $proposal->status = ProposalStatusEnum::APPROVED;
            $proposal->save();

            return $proposal->load(['opd', 'proposer', 'category']);
        });
    }

    public function reject(AssetProposal $proposal, ?string $currentOpdId): AssetProposal
    {
        return DB::transaction(function () use ($proposal, $currentOpdId) {
            $proposal->refresh();

            if ($proposal->status !== ProposalStatusEnum::SUBMITTED) {
                abort(422, 'Only submitted proposals can be rejected.');
            }

            if ($currentOpdId && $proposal->opd_id !== $currentOpdId) {
                abort(403, 'You can only reject proposals for your current OPD.');
            }

            $proposal->status = ProposalStatusEnum::REJECTED;
            $proposal->save();

            return $proposal->load(['opd', 'proposer', 'category']);
        });
    }

    public function complete(AssetProposal $proposal, string $completedByUserId, ?string $currentOpdId): AssetProposal
    {
        return DB::transaction(function () use ($proposal, $currentOpdId) {
            $proposal->refresh();

            if ($proposal->status !== ProposalStatusEnum::APPROVED) {
                abort(422, 'Only approved proposals can be completed.');
            }

            if ($currentOpdId && $proposal->opd_id !== $currentOpdId) {
                abort(403, 'You can only complete proposals for your current OPD.');
            }

            $qty = (int) $proposal->qty;
            $estimatedPrice = (float) $proposal->estimated_price;

            if ($qty <= 0) {
                abort(422, 'Quantity must be greater than zero.');
            }

            if ($estimatedPrice < 0) {
                abort(422, 'Estimated price must not be negative.');
            }

            $proposal->status = ProposalStatusEnum::COMPLETED;
            $proposal->total_estimation = $qty * $estimatedPrice;
            $proposal->save();

            return $proposal->load(['opd', 'proposer', 'category']);
        });
    }

    public function getStats(?string $currentOpdId): array
    {
        $baseQuery = AssetProposal::query()
            ->when($currentOpdId, function ($q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            });

        $totalProposals = (clone $baseQuery)->count();

        $submittedProposals = (clone $baseQuery)
            ->where('status', ProposalStatusEnum::SUBMITTED)
            ->count();

        $approvedProposals = (clone $baseQuery)
            ->where('status', ProposalStatusEnum::APPROVED)
            ->count();

        $completedProposals = (clone $baseQuery)
            ->where('status', ProposalStatusEnum::COMPLETED)
            ->count();

        return [
            'total_proposals' => $totalProposals,
            'submitted_proposals' => $submittedProposals,
            'approved_proposals' => $approvedProposals,
            'completed_proposals' => $completedProposals,
        ];
    }

    protected function createAssetFromProposal(AssetProposal $proposal, string $createdByUserId): Asset
    {
        $fundingSource = FundingSource::firstOrCreate(
            ['name' => 'Usulan Aset'],
            ['description' => null]
        );

        $data = [
            'asset_code' => $this->generateAssetCode(),
            'name' => $proposal->item_name,
            'category_id' => $proposal->category_id,
            'opd_id' => $proposal->opd_id,
            'room_id' => null,
            'funding_source_id' => $fundingSource->id,
            'condition' => AssetConditionEnum::GOOD,
            'purchase_date' => null,
            'purchase_price' => $proposal->estimated_price,
            'status' => AssetStatusEnum::ACTIVE,
            'notes' => $proposal->specification ?: 'Dari usulan aset '.$proposal->proposal_number,
            'created_by' => $createdByUserId,
        ];

        /** @var AssetService $assetService */
        $assetService = app(AssetService::class);

        return $assetService->create($data);
    }

    protected function generateNextNumericCode(?string $latestCode): string
    {
        if ($latestCode && preg_match('/#(\d+)/', $latestCode, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        return '#'.$number;
    }

    protected function generateProposalNumber(): string
    {
        $latest = AssetProposal::orderByDesc('created_at')->value('proposal_number');

        $number = $this->generateNextNumericCode($latest);

        while (AssetProposal::where('proposal_number', $number)->exists()) {
            $number = $this->generateNextNumericCode($number);
        }

        return $number;
    }

    protected function generateAssetCode(): string
    {
        $latest = Asset::orderByDesc('created_at')->value('asset_code');

        $code = $this->generateNextNumericCode($latest);

        while (Asset::where('asset_code', $code)->exists()) {
            $code = $this->generateNextNumericCode($code);
        }

        return $code;
    }
}
