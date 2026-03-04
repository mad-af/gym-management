<?php

namespace Database\Seeders;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\ProposalStatusEnum;
use App\Enums\Role as RoleEnum;
use App\Enums\StatusEnum;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetProposal;
use App\Models\Employee;
use App\Models\FundingSource;
use App\Models\Opd;
use App\Models\Role as SpatieRole;
use App\Models\Room;
use App\Models\User;
use App\Services\AssetMaintenanceService;
use App\Services\AvatarGenerator;
use App\Services\DisposalService;
use App\Services\TransferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestingMasterSeeder extends Seeder
{
    protected static function generateNanoId(int $length = 12): string
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, strlen($alphabet) - 1);
            $id .= $alphabet[$index];
        }

        return $id;
    }

    public function run(): void
    {
        $opd = Opd::firstOrCreate(
            ['code' => 'OPD-TEST'],
            [
                'name' => 'OPD Testing',
                'email' => 'opd-testing@example.com',
                'address' => 'Alamat OPD Testing',
                'phone' => '0800000000',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        $employeeHead = Employee::firstOrCreate(
            ['nip' => '198001011000001'],
            [
                'name' => 'Kepala OPD Testing',
                'opd_id' => $opd->id,
                'position' => 'Kepala OPD',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        if ($opd->head_id !== $employeeHead->id) {
            $opd->head_id = $employeeHead->id;
            $opd->save();
        }

        Employee::firstOrCreate(
            ['nip' => '198001011000002'],
            [
                'name' => 'Pegawai Testing Satu',
                'opd_id' => $opd->id,
                'position' => 'Staf',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        Employee::firstOrCreate(
            ['nip' => '198001011000003'],
            [
                'name' => 'Pegawai Testing Dua',
                'opd_id' => $opd->id,
                'position' => 'Staf',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        Room::firstOrCreate(
            ['code' => 'RM-TEST-01'],
            [
                'opd_id' => $opd->id,
                'name' => 'Ruangan Testing 1',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        Room::firstOrCreate(
            ['code' => 'RM-TEST-02'],
            [
                'opd_id' => $opd->id,
                'name' => 'Ruangan Testing 2',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        $buildingCategory = AssetCategory::firstOrCreate(
            ['code' => 'CAT-GEDUNG'],
            [
                'name' => 'Gedung & Bangunan',
                'level' => 1,
            ]
        );

        AssetCategory::firstOrCreate(
            ['code' => 'CAT-GEDUNG-KANTOR'],
            [
                'parent_id' => $buildingCategory->id,
                'name' => 'Gedung Kantor',
                'useful_life_years' => 20,
                'level' => 2,
            ]
        );

        $equipmentCategory = AssetCategory::firstOrCreate(
            ['code' => 'CAT-PERALATAN'],
            [
                'name' => 'Peralatan & Mesin',
                'level' => 1,
            ]
        );

        AssetCategory::firstOrCreate(
            ['code' => 'CAT-KOMPUTER'],
            [
                'parent_id' => $equipmentCategory->id,
                'name' => 'Peralatan Komputer',
                'useful_life_years' => 4,
                'level' => 2,
            ]
        );

        $testerRole = SpatieRole::firstOrCreate(['name' => RoleEnum::ADMIN->value]);

        $testUser = User::firstOrCreate(
            ['email' => 'tester@asset-management.com'],
            [
                'name' => 'Testing User',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        if (! $testUser->hasRole($testerRole->name)) {
            $testUser->assignRole($testerRole->name);
        }

        // Generate Avatar for Tester
        if ($testUser->media()->where('collection', 'avatar')->doesntExist()) {
            $generator = new AvatarGenerator;
            // Generate SVG avatar
            $generator->generateAndSave($testUser->name, 'svg', $testUser);
        }

        $room1 = Room::where('code', 'RM-TEST-01')->first();
        $room2 = Room::where('code', 'RM-TEST-02')->first();
        $computerCategory = AssetCategory::where('code', 'CAT-KOMPUTER')->first();
        $buildingCategory = AssetCategory::where('code', 'CAT-GEDUNG-KANTOR')->first();

        $pengadaanTestingSource = FundingSource::firstOrCreate(
            ['name' => 'Pengadaan Testing'],
            ['description' => 'Sumber pendanaan untuk pengadaan aset testing.'],
        );

        $apbdTestingSource = FundingSource::firstOrCreate(
            ['name' => 'APBD Testing'],
            ['description' => 'Sumber pendanaan APBD untuk aset testing.'],
        );

        if ($room1 && $computerCategory && $pengadaanTestingSource) {
            Asset::firstOrCreate(
                ['asset_code' => 'AST-TEST-001'],
                [
                    'name' => 'Komputer Testing 1',
                    'category_id' => $computerCategory->id,
                    'opd_id' => $opd->id,
                    'room_id' => $room1->id,
                    'condition' => AssetConditionEnum::GOOD,
                    'purchase_date' => now()->subYears(1)->toDateString(),
                    'purchase_price' => 15000000,
                    'funding_source_id' => $pengadaanTestingSource->id,
                    'status' => AssetStatusEnum::ACTIVE,
                    'qr_id' => self::generateNanoId(),
                    'notes' => 'Asset komputer untuk keperluan testing.',
                    'created_by' => $testUser->id,
                ]
            );
        }

        if ($room2 && $buildingCategory && $apbdTestingSource) {
            Asset::firstOrCreate(
                ['asset_code' => 'AST-TEST-002'],
                [
                    'name' => 'Gedung Kantor Testing',
                    'category_id' => $buildingCategory->id,
                    'opd_id' => $opd->id,
                    'room_id' => $room2->id,
                    'condition' => AssetConditionEnum::GOOD,
                    'purchase_date' => now()->subYears(5)->toDateString(),
                    'purchase_price' => 2500000000,
                    'funding_source_id' => $apbdTestingSource->id,
                    'status' => AssetStatusEnum::ACTIVE,
                    'qr_id' => self::generateNanoId(),
                    'notes' => 'Asset gedung untuk keperluan testing.',
                    'created_by' => $testUser->id,
                ]
            );
        }

        if ($room1 && $computerCategory && $pengadaanTestingSource) {
            Asset::firstOrCreate(
                ['asset_code' => 'AST-TEST-003'],
                [
                    'name' => 'Komputer Rusak Berat Testing',
                    'category_id' => $computerCategory->id,
                    'opd_id' => $opd->id,
                    'room_id' => $room1->id,
                    'condition' => AssetConditionEnum::MAJOR_DAMAGE,
                    'purchase_date' => now()->subYears(3)->toDateString(),
                    'purchase_price' => 8000000,
                    'funding_source_id' => $pengadaanTestingSource->id,
                    'status' => AssetStatusEnum::ACTIVE,
                    'qr_id' => self::generateNanoId(),
                    'notes' => 'Asset komputer rusak berat untuk keperluan testing penghapusan.',
                    'created_by' => $testUser->id,
                ]
            );
        }

        if ($room1 && $computerCategory && $pengadaanTestingSource) {
            Asset::firstOrCreate(
                ['asset_code' => 'AST-TEST-004'],
                [
                    'name' => 'Komputer Untuk Penghapusan Testing',
                    'category_id' => $computerCategory->id,
                    'opd_id' => $opd->id,
                    'room_id' => $room1->id,
                    'condition' => AssetConditionEnum::MAJOR_DAMAGE,
                    'purchase_date' => now()->subYears(4)->toDateString(),
                    'purchase_price' => 6000000,
                    'funding_source_id' => $pengadaanTestingSource->id,
                    'status' => AssetStatusEnum::ACTIVE,
                    'qr_id' => self::generateNanoId(),
                    'notes' => 'Asset komputer yang akan digunakan sebagai contoh penghapusan.',
                    'created_by' => $testUser->id,
                ]
            );
        }

        if ($computerCategory) {
            AssetProposal::firstOrCreate(
                ['proposal_number' => '#1'],
                [
                    'opd_id' => $opd->id,
                    'proposed_by' => $testUser->id,
                    'proposal_date' => now()->subDays(5)->toDateString(),
                    'category_id' => $computerCategory->id,
                    'item_name' => 'Pengadaan Komputer Testing',
                    'specification' => 'Pengadaan 5 unit komputer untuk keperluan testing.',
                    'qty' => 5,
                    'estimated_price' => 10000000,
                    'status' => ProposalStatusEnum::SUBMITTED,
                    'total_estimation' => 5 * 10000000,
                    'notes' => 'Usulan aset komputer untuk testing.',
                ]
            );

            AssetProposal::firstOrCreate(
                ['proposal_number' => '#2'],
                [
                    'opd_id' => $opd->id,
                    'proposed_by' => $testUser->id,
                    'proposal_date' => now()->subDays(10)->toDateString(),
                    'category_id' => $computerCategory->id,
                    'item_name' => 'Pengadaan Laptop Testing',
                    'specification' => 'Pengadaan 3 unit laptop untuk keperluan testing.',
                    'qty' => 3,
                    'estimated_price' => 12000000,
                    'status' => ProposalStatusEnum::APPROVED,
                    'total_estimation' => 3 * 12000000,
                    'notes' => 'Usulan aset laptop yang sudah disetujui.',
                ]
            );

            AssetProposal::firstOrCreate(
                ['proposal_number' => '#3'],
                [
                    'opd_id' => $opd->id,
                    'proposed_by' => $testUser->id,
                    'proposal_date' => now()->subDays(15)->toDateString(),
                    'category_id' => $computerCategory->id,
                    'item_name' => 'Pengadaan Printer Testing',
                    'specification' => 'Pengadaan 2 unit printer untuk keperluan testing.',
                    'qty' => 2,
                    'estimated_price' => 5000000,
                    'status' => ProposalStatusEnum::REJECTED,
                    'total_estimation' => 2 * 5000000,
                    'notes' => 'Usulan aset printer yang ditolak.',
                ]
            );

            AssetProposal::firstOrCreate(
                ['proposal_number' => '#4'],
                [
                    'opd_id' => $opd->id,
                    'proposed_by' => $testUser->id,
                    'proposal_date' => now()->subDays(20)->toDateString(),
                    'category_id' => $computerCategory->id,
                    'item_name' => 'Pengadaan Scanner Testing',
                    'specification' => 'Pengadaan 1 unit scanner untuk keperluan testing.',
                    'qty' => 1,
                    'estimated_price' => 3000000,
                    'status' => ProposalStatusEnum::COMPLETED,
                    'total_estimation' => 3000000,
                    'notes' => 'Usulan aset scanner yang sudah direalisasikan.',
                ]
            );
        }

        $destinationOpd = Opd::firstOrCreate(
            ['code' => 'OPD-TEST-DEST'],
            [
                'name' => 'OPD Testing Tujuan',
                'email' => 'opd-testing-destination@example.com',
                'address' => 'Alamat OPD Testing Tujuan',
                'phone' => '0800000001',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        $destinationRoom = Room::firstOrCreate(
            ['code' => 'RM-TEST-DEST-01'],
            [
                'opd_id' => $destinationOpd->id,
                'name' => 'Ruangan Testing Tujuan 1',
                'status' => StatusEnum::ACTIVE,
            ]
        );

        $testUser->has_all_opds = false;
        $testUser->save();

        $testUser->opds()->sync([$opd->id]);

        $assetForTransfer = Asset::where('asset_code', 'AST-TEST-001')->first();

        if ($assetForTransfer && $destinationRoom) {
            $transferService = app(TransferService::class);

            $transferService->createTransfer([
                'from_opd_id' => $assetForTransfer->opd_id,
                'to_opd_id' => $destinationOpd->id,
                'items' => [
                    [
                        'asset_id' => $assetForTransfer->id,
                        'from_room_id' => $assetForTransfer->room_id,
                        'to_room_id' => $destinationRoom->id,
                    ],
                ],
                'notes' => 'Mutasi testing dari seeder.',
            ], $testUser->id, null);
        }

        $assetForDisposal = Asset::where('asset_code', 'AST-TEST-004')->first();

        if ($assetForDisposal) {
            $disposalService = app(DisposalService::class);

            $disposalService->createDisposalDocument([
                'opd_id' => $assetForDisposal->opd_id,
                'disposal_type' => 'destruction',
                'disposal_date' => now()->toDateString(),
                'notes' => 'Penghapusan aset testing dari seeder.',
                'items' => [
                    [
                        'asset_id' => $assetForDisposal->id,
                        'reason' => 'Rusak berat, tidak layak pakai',
                        'condition_at_disposal' => 'major_damage',
                    ],
                ],
            ], $testUser->id, null);
        }

        $assetForMaintenanceActive = Asset::where('asset_code', 'AST-TEST-001')->first();
        $assetForMaintenanceDamaged = Asset::where('asset_code', 'AST-TEST-003')->first();

        if ($assetForMaintenanceActive || $assetForMaintenanceDamaged) {
            $maintenanceService = app(AssetMaintenanceService::class);

            if ($assetForMaintenanceActive && ! AssetMaintenance::where('asset_id', $assetForMaintenanceActive->id)->exists()) {
                $maintenanceService->schedule([
                    'asset_id' => $assetForMaintenanceActive->id,
                    'maintenance_type' => 'Perawatan rutin dari seeder',
                    'scheduled_date' => now()->addDays(7)->toDateString(),
                    'cost' => 0,
                    'vendor' => null,
                    'description' => 'Perawatan berkala untuk memastikan kondisi aset tetap baik.',
                ]);
            }

            if ($assetForMaintenanceDamaged && ! AssetMaintenance::where('asset_id', $assetForMaintenanceDamaged->id)->exists()) {
                $maintenance = $maintenanceService->schedule([
                    'asset_id' => $assetForMaintenanceDamaged->id,
                    'maintenance_type' => 'Perbaikan kerusakan berat dari seeder',
                    'scheduled_date' => now()->subDays(7)->toDateString(),
                    'cost' => 1000000,
                    'vendor' => 'Vendor Perbaikan Testing',
                    'description' => 'Perawatan untuk memperbaiki kerusakan berat pada komputer testing.',
                ]);

                $maintenanceService->complete($maintenance, [
                    'completed_date' => now()->subDays(3)->toDateString(),
                    'asset_condition' => AssetConditionEnum::GOOD->value,
                    'description' => 'Perawatan selesai, kondisi aset kembali baik.',
                    'log_notes' => 'Perawatan selesai dari TestingMasterSeeder.',
                ]);
            }
        }
    }
}
