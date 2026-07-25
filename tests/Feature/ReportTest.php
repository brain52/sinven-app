<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\Condition;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Admin Lab']);
    }

    /**
     * Skenario 1: Test Download Excel
     */
    public function test_admin_can_export_items_to_excel()
    {
        $location = Location::factory()->create();
        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');

        Item::create([
            'inventory_code' => 'TKJ-EXC-001',
            'name' => 'Switch Cisco',
            'category_id' => Category::create(['prefix_code' => 'SWT', 'name' => 'Switch'])->id,
            'location_id' => $location->id,
            'condition_id' => Condition::create(['name' => 'Baik'])->id,
            'status' => 'Tersedia', 
        ]);

        $response = $this->actingAs($admin)->get("/api/v1/{$location->id}/reports/excel");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Skenario 2: Test Download PDF
     */
    public function test_admin_can_export_items_to_pdf()
    {
        $location = Location::factory()->create();
        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');

        Item::create([
            'inventory_code' => 'TKJ-PDF-001',
            'name' => 'Access Point',
            'category_id' => Category::create(['prefix_code' => 'ACC', 'name' => 'Access Point'])->id,
            'location_id' => $location->id,
            'condition_id' => Condition::create(['name' => 'Baik'])->id,
            'status' => 'Tersedia', 
        ]);

        $response = $this->actingAs($admin)->get("/api/v1/{$location->id}/reports/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}