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

class MaintenanceTest extends TestCase
{
    use RefreshDatabase; // Memastikan DB di-reset tiap kali test berjalan

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Admin Lab']);
    }

    /**
     * Skenario 1: Lapor Kerusakan
     * Barang yang Dilaporkan Rusak harus berubah statusnya menjadi 'Rusak'.
     */
    public function test_item_can_be_reported_as_damaged()
    {
        // 1. ARRANGE
        $location = Location::factory()->create();
        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');

        $item = Item::create([
            'inventory_code' => 'TKJ-001',
            'name' => 'Router Mikrotik',
            'category_id' => Category::create(['prefix_code' => 'RTR', 'name' => 'Router'])->id,
            'location_id' => $location->id,
            'condition_id' => Condition::create(['name' => 'Baik'])->id,
            'status' => 'Tersedia', 
        ]);

        // 2. ACT (Kirim request lapor kerusakan)
        $response = $this->actingAs($admin)->postJson("/api/v1/{$location->id}/maintenances", [
            'item_id' => $item->id,
            'problem_description' => 'Port LAN 1 tidak merespon'
        ]);

        // 3. ASSERT
        $response->assertStatus(201);
        
        // Cek log kerusakan terbentuk
        $this->assertDatabaseHas('maintenances', [
            'item_id' => $item->id,
            'status' => 'Dilaporkan'
        ]);

        // Cek status barang TERKUNCI menjadi 'Rusak'
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'Rusak'
        ]);
    }

    /**
     * Skenario 2: Perbaikan Selesai
     * Barang yang sudah selesai diperbaiki harus kembali berstatus 'Tersedia'.
     */
    public function test_maintenance_can_be_completed()
    {
        // 1. ARRANGE
        $location = Location::factory()->create();
        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');

        // Buat item yang berstatus 'Rusak'
        $item = Item::create([
            'inventory_code' => 'TKJ-002',
            'name' => 'Switch Hub',
            'category_id' => Category::create(['prefix_code' => 'SWT', 'name' => 'Switch'])->id,
            'location_id' => $location->id,
            'condition_id' => Condition::create(['name' => 'Rusak'])->id,
            'status' => 'Rusak', 
        ]);

        // Buat data maintenance awal
        $maintenance = \App\Models\Maintenance::create([
            'item_id' => $item->id,
            'reported_by' => $admin->id,       // <--- SUDAH DIPERBAIKI
            'problem_description' => 'Mati total',
            'reported_at' => now(),
            'status' => 'Dalam Perbaikan'
        ]);

        // 2. ACT (Kirim request perbaikan selesai)
        $response = $this->actingAs($admin)->postJson("/api/v1/{$location->id}/maintenances/{$maintenance->id}/complete", [
            'cost' => 150000,
            'resolution_notes' => 'Ganti adaptor power supply'
        ]);

        // 3. ASSERT
        $response->assertStatus(200);

        // Cek status perbaikan selesai dan biaya tercatat
        $this->assertDatabaseHas('maintenances', [
            'id' => $maintenance->id,
            'status' => 'Selesai',
            'cost' => 150000
        ]);

        // Cek status barang kembali menjadi 'Tersedia'
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'Tersedia'
        ]);
    }
}