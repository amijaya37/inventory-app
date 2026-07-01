<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InventoryReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_report_pages(): void
    {
        $user = $this->reportUser(['reports.view']);
        $this->seedReportData($user);

        $this->actingAs($user)->get(route('reports.index'))->assertOk()->assertSee('Laporan');
        $this->actingAs($user)->get(route('reports.stock'))->assertOk()->assertSee('Laporan Stock')->assertSee('Router Test');
        $this->actingAs($user)->get(route('reports.goods-in'))->assertOk()->assertSee('Laporan Barang Masuk')->assertSee('BM-TEST-001');
        $this->actingAs($user)->get(route('reports.goods-out'))->assertOk()->assertSee('Laporan Barang Keluar')->assertSee('BK-TEST-001');
    }

    public function test_report_export_requires_export_permission(): void
    {
        $viewer = $this->reportUser(['reports.view']);
        $exporter = $this->reportUser(['reports.view', 'reports.export']);
        $this->seedReportData($exporter);

        $this->actingAs($viewer)->get(route('reports.stock.export'))->assertForbidden();
        $this->actingAs($exporter)->get(route('reports.stock.export'))->assertOk()->assertHeader('content-disposition');
    }

    /** @param list<string> $permissions */
    private function reportUser(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function seedReportData(User $user): void
    {
        $categoryId = DB::table('categories')->insertGetId(['code' => 'NET', 'name' => 'Network', 'created_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        $supplierId = DB::table('suppliers')->insertGetId(['code' => 'SUP', 'name' => 'Supplier Test', 'created_at' => now(), 'updated_at' => now()]);
        $locationId = DB::table('locations')->insertGetId(['code' => 'WH', 'name' => 'Warehouse Test', 'type' => 'warehouse', 'created_at' => now(), 'updated_at' => now()]);
        $itemId = DB::table('items')->insertGetId(['category_id' => $categoryId, 'sku' => 'RTR-001', 'name' => 'Router Test', 'unit' => 'pcs', 'minimum_stock' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('stocks')->insert(['item_id' => $itemId, 'location_id' => $locationId, 'qty_on_hand' => 5, 'qty_reserved' => 0, 'last_movement_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $receiptId = DB::table('goods_receipts')->insertGetId(['receipt_no' => 'BM-TEST-001', 'source_type' => 'purchase', 'supplier_id' => $supplierId, 'warehouse_location_id' => $locationId, 'receipt_date' => now()->toDateString(), 'status' => 'posted', 'created_by' => $user->id, 'posted_by' => $user->id, 'posted_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('goods_receipt_items')->insert(['goods_receipt_id' => $receiptId, 'item_id' => $itemId, 'qty' => 5, 'unit_price' => 100000, 'total_price' => 500000, 'condition_status' => 'new', 'created_at' => now(), 'updated_at' => now()]);
        $issueId = DB::table('goods_issues')->insertGetId(['issue_no' => 'BK-TEST-001', 'issue_date' => now()->toDateString(), 'source_location_id' => $locationId, 'recipient_type' => 'external', 'recipient_name' => 'User Test', 'target_location_id' => $locationId, 'pic_user_id' => $user->id, 'requested_by' => $user->id, 'posted_by' => $user->id, 'posted_at' => now(), 'document_no' => 'ST-TEST-001', 'status' => 'posted', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('goods_issue_items')->insert(['goods_issue_id' => $issueId, 'item_id' => $itemId, 'qty' => 1, 'condition_status' => 'good', 'created_at' => now(), 'updated_at' => now()]);
    }
}
