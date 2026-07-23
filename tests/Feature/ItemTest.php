<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Location;
use App\Models\Category;
use App\Models\Condition;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_lab_can_create_item_with_auto_generated_code()
    {
        Role::create(['name' => 'Admin Lab']);

        $dept = Department::factory()->create(['code' => 'TKJ']);
        $location = Location::factory()->create(['department_id' => $dept->id, 'name' => 'Lab TKJ']);

        $category = Category::create(['prefix_code' => 'KMP', 'name' => 'Komputer']);
        $condition = Condition::create(['name' => 'Baik']);

        // User terikat di Lab TKJ
        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');

        $response = $this->actingAs($admin)->postJson("/api/v1/{$location->id}/items", [
            'name' => 'PC Server Dell',
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'price' => 15000000
        ]);

        if ($response->status() !== 201) {
            $response->dump();
        }
        $response->assertStatus(201)->assertJsonPath('success', true);

        // Memastikan Auto-Generate Code berfungsi dengan benar (Format: DEPT-CAT-YEAR-0001)
        $year = date('Y');
        $this->assertDatabaseHas('items', [
            'inventory_code' => "TKJ-KMP-{$year}-0001",
            'name' => 'PC Server Dell'
        ]);
    }
}
