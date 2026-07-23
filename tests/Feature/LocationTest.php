<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role; // 1. Tambahkan import Role Spatie

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_location()
    {
        // Setup Role terlebih dahulu di database testing
        Role::create(['name' => 'Super Admin']); 
        
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $dept = Department::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/locations', [
            'department_id' => $dept->id,
            'name' => 'Lab Jaringan',
            'type' => 'Lab'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);
        
        $this->assertDatabaseHas('locations', ['name' => 'Lab Jaringan']);
    }

    public function test_admin_lab_cannot_create_location()
    {
        // Setup Role terlebih dahulu di database testing
        Role::create(['name' => 'Admin Lab']);

        $adminLab = User::factory()->create();
        $adminLab->assignRole('Admin Lab'); 
        
        $response = $this->actingAs($adminLab)->postJson('/api/v1/locations', [
            'name' => 'Lab Baru'
        ]);

        $response->assertStatus(403); // Memastikan akses ditolak (Forbidden)
    }
}