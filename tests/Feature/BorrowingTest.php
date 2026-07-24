<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\Condition;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BorrowingTest extends TestCase
{
    use RefreshDatabase; // Reset database setiap kali test dijalankan

    protected function setUp(): void
    {
        parent::setUp();
        // Setup dasar: Pastikan role Admin Lab tersedia di memory
        Role::create(['name' => 'Admin Lab']);
    }

    /**
     * Skenario 1: Barang yang tersedia HARUS BISA dipinjam, 
     * dan status barang HARUS berubah menjadi "Dipinjam".
     */
    public function test_item_can_be_borrowed_successfully()
    {
        // --- 1. ARRANGE (Persiapan Data) ---
        $dept = Department::factory()->create(['code' => 'TKJ']);
        $location = Location::factory()->create(['department_id' => $dept->id, 'name' => 'Lab TKJ']);
        $category = Category::create(['prefix_code' => 'KMP', 'name' => 'Komputer']);
        $condition = Condition::create(['name' => 'Baik']);

        // Admin yang bertugas
        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');

        // Peminjam (Guru/Siswa)
        $borrower = User::factory()->create();

        // Barang dengan status awal "Tersedia"
        $item = Item::create([
            'inventory_code' => 'TKJ-KMP-2026-0001',
            'name' => 'Laptop Asus ROG',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'condition_id' => $condition->id,
            'status' => 'Tersedia', 
        ]);

        // --- 2. ACT (Eksekusi Peminjaman) ---
        $response = $this->actingAs($admin)->postJson("/api/v1/{$location->id}/borrowings", [
            'item_id' => $item->id,
            'user_id' => $borrower->id,
            'duration_days' => 3,
            'notes' => 'Peminjaman untuk praktek jaringan'
        ]);

        // --- 3. ASSERT (Verifikasi Hasil) ---
        // Pastikan API mengembalikan status HTTP 201 Created
        $response->assertStatus(201)->assertJsonPath('success', true);

        // Pastikan data transaksi tercatat di tabel borrowings
        $this->assertDatabaseHas('borrowings', [
            'item_id' => $item->id,
            'user_id' => $borrower->id,
            'status' => 'Dipinjam'
        ]);

        // [KRUSIAL] Pastikan status barang BERUBAH otomatis di tabel items
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'Dipinjam'
        ]);
    }

    /**
     * Skenario 2: Barang yang statusnya sedang "Dipinjam"
     * HARUS DITOLAK jika ada yang mencoba meminjamnya lagi.
     */
    public function test_borrowed_item_cannot_be_borrowed_again()
    {
        // --- 1. ARRANGE (Persiapan Data) ---
        $location = Location::factory()->create();
        $category = Category::create(['prefix_code' => 'LPT', 'name' => 'Laptop']);
        $condition = Condition::create(['name' => 'Baik']);

        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');
        $borrower = User::factory()->create();

        // Barang sengaja kita set statusnya "Dipinjam" (belum dikembalikan)
        $item = Item::create([
            'inventory_code' => 'UMUM-LPT-2026-0002',
            'name' => 'Macbook Pro',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'condition_id' => $condition->id,
            'status' => 'Dipinjam', 
        ]);

        // --- 2. ACT (Eksekusi Peminjaman) ---
        $response = $this->actingAs($admin)->postJson("/api/v1/{$location->id}/borrowings", [
            'item_id' => $item->id,
            'user_id' => $borrower->id,
            'duration_days' => 1
        ]);

        // --- 3. ASSERT (Verifikasi Hasil) ---
        // Pastikan sistem menolak dengan HTTP 422 (Unprocessable Entity)
        $response->assertStatus(422)->assertJsonPath('success', false);
    }
    /**
     * Skenario 3: Memproses pengembalian barang.
     * Transaksi HARUS tertutup (Dikembalikan) dan Barang HARUS kembali Tersedia.
     */
    public function test_borrowed_item_can_be_returned_successfully()
    {
        // --- 1. ARRANGE ---
        $location = Location::factory()->create();
        $category = Category::create(['prefix_code' => 'KMR', 'name' => 'Kamera']);
        $condition = Condition::create(['name' => 'Baik']);

        $admin = User::factory()->create(['location_id' => $location->id]);
        $admin->assignRole('Admin Lab');
        
        // Buat barang dengan status Dipinjam
        $item = Item::create([
            'inventory_code' => 'TKJ-KMR-2026-0003',
            'name' => 'Kamera Canon DSLR',
            'category_id' => $category->id,
            'location_id' => $location->id,
            'condition_id' => $condition->id,
            'status' => 'Dipinjam', 
        ]);

        // Buat data transaksi peminjaman awal
        $borrowing = \App\Models\Borrowing::create([
            'item_id' => $item->id,
            'user_id' => User::factory()->create()->id, // Peminjam acak
            'admin_id' => $admin->id,
            'borrowed_at' => now()->subDays(2), // Dipinjam 2 hari yang lalu
            'expected_return_at' => now()->addDays(1),
            'status' => 'Dipinjam'
        ]);

        // --- 2. ACT ---
        // Eksekusi API pengembalian barang
        $response = $this->actingAs($admin)->postJson("/api/v1/{$location->id}/borrowings/{$borrowing->id}/return", [
            'notes' => 'Dikembalikan dalam kondisi baik'
        ]);

        // --- 3. ASSERT ---
        $response->assertStatus(200)->assertJsonPath('success', true);

        // Pastikan transaksi tercatat sebagai 'Dikembalikan'
        $this->assertDatabaseHas('borrowings', [
            'id' => $borrowing->id,
            'status' => 'Dikembalikan'
        ]);

        // Pastikan barang kembali menjadi 'Tersedia'
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'Tersedia'
        ]);
    }
}