<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DynamicMenu;
use App\Models\DynamicMenuItem;
use App\Models\DynamicTable;
use App\Models\TableColumn;
use Illuminate\Support\Facades\DB;

class DynamicMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample tables first
        $userTable = DynamicTable::create([
            'name' => 'Data Pengguna',
            'table_name' => 'dyn_users',
            'description' => 'Tabel untuk mengelola data pengguna',
            'is_active' => true,
        ]);

        $userTable->createTable();

        // Add columns to user table
        $userColumns = [
            ['name' => 'Nama Lengkap', 'column_name' => 'full_name', 'type' => 'string', 'is_required' => true, 'order' => 1],
            ['name' => 'Email', 'column_name' => 'email', 'type' => 'string', 'is_required' => true, 'order' => 2],
            ['name' => 'Nomor Telepon', 'column_name' => 'phone', 'type' => 'string', 'is_required' => false, 'order' => 3],
            ['name' => 'Tanggal Lahir', 'column_name' => 'birth_date', 'type' => 'date', 'is_required' => false, 'order' => 4],
            ['name' => 'Status', 'column_name' => 'status', 'type' => 'enum', 'options' => ['values' => ['Aktif', 'Nonaktif']], 'is_required' => true, 'order' => 5],
        ];

        foreach ($userColumns as $columnData) {
            $column = TableColumn::create(array_merge($columnData, [
                'dynamic_table_id' => $userTable->id,
                'is_searchable' => true,
                'is_sortable' => true,
                'show_in_list' => true,
                'is_active' => true,
            ]));
            $column->addColumnToTable();
        }

        // Create product table
        $productTable = DynamicTable::create([
            'name' => 'Data Produk',
            'table_name' => 'dyn_products',
            'description' => 'Tabel untuk mengelola data produk',
            'is_active' => true,
        ]);

        $productTable->createTable();

        // Add columns to product table
        $productColumns = [
            ['name' => 'Kode Produk', 'column_name' => 'product_code', 'type' => 'string', 'is_required' => true, 'order' => 1],
            ['name' => 'Nama Produk', 'column_name' => 'product_name', 'type' => 'string', 'is_required' => true, 'order' => 2],
            ['name' => 'Deskripsi', 'column_name' => 'description', 'type' => 'text', 'is_required' => false, 'order' => 3],
            ['name' => 'Harga', 'column_name' => 'price', 'type' => 'decimal', 'is_required' => true, 'order' => 4],
            ['name' => 'Stok', 'column_name' => 'stock', 'type' => 'integer', 'is_required' => true, 'order' => 5],
            ['name' => 'Kategori', 'column_name' => 'category', 'type' => 'enum', 'options' => ['values' => ['Elektronik', 'Fashion', 'Makanan', 'Lainnya']], 'is_required' => true, 'order' => 6],
        ];

        foreach ($productColumns as $columnData) {
            $column = TableColumn::create(array_merge($columnData, [
                'dynamic_table_id' => $productTable->id,
                'is_searchable' => true,
                'is_sortable' => true,
                'show_in_list' => true,
                'is_active' => true,
            ]));
            $column->addColumnToTable();
        }

        // Create sample menus
        $masterDataMenu = DynamicMenu::create([
            'name' => 'Master Data',
            'icon' => 'fas fa-database',
            'permission_key' => 'master_data',
            'category' => 'main',
            'order' => 1,
            'is_active' => true,
        ]);

        // Create menu items for master data
        DynamicMenuItem::create([
            'dynamic_menu_id' => $masterDataMenu->id,
            'name' => 'Kelola Pengguna',
            'icon' => 'fas fa-users',
            'link_type' => 'table',
            'link_value' => (string) $userTable->id,
            'permission_key' => 'users',
            'order' => 1,
            'is_active' => true,
        ]);

        DynamicMenuItem::create([
            'dynamic_menu_id' => $masterDataMenu->id,
            'name' => 'Kelola Produk',
            'icon' => 'fas fa-box',
            'link_type' => 'table',
            'link_value' => (string) $productTable->id,
            'permission_key' => 'products',
            'order' => 2,
            'is_active' => true,
        ]);

        // Create reports menu
        $reportsMenu = DynamicMenu::create([
            'name' => 'Laporan',
            'icon' => 'fas fa-chart-pie',
            'permission_key' => 'reports',
            'category' => 'main',
            'order' => 2,
            'is_active' => true,
        ]);

        // Create menu items for reports
        DynamicMenuItem::create([
            'dynamic_menu_id' => $reportsMenu->id,
            'name' => 'Laporan Pengguna',
            'icon' => 'fas fa-file-alt',
            'link_type' => 'route',
            'link_value' => 'dashboard.index',
            'permission_key' => 'user_reports',
            'order' => 1,
            'is_active' => true,
        ]);

        DynamicMenuItem::create([
            'dynamic_menu_id' => $reportsMenu->id,
            'name' => 'Analytics',
            'icon' => 'fas fa-chart-line',
            'link_type' => 'url',
            'link_value' => 'https://analytics.google.com',
            'permission_key' => 'analytics',
            'order' => 2,
            'is_active' => true,
        ]);

        // Insert sample data
        DB::table($userTable->table_name)->insert([
            [
                'full_name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '08123456789',
                'birth_date' => '1990-01-15',
                'status' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '08198765432',
                'birth_date' => '1985-06-20',
                'status' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table($productTable->table_name)->insert([
            [
                'product_code' => 'PROD001',
                'product_name' => 'Laptop Gaming',
                'description' => 'Laptop gaming dengan spesifikasi tinggi',
                'price' => 15000000.00,
                'stock' => 10,
                'category' => 'Elektronik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_code' => 'PROD002',
                'product_name' => 'Smartphone',
                'description' => 'Smartphone dengan kamera 108MP',
                'price' => 8000000.00,
                'stock' => 25,
                'category' => 'Elektronik',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        $this->command->info('Dynamic menu seeder completed successfully!');
        $this->command->info('Sample data created:');
        $this->command->info('- 2 Dynamic Tables (Users & Products)');
        $this->command->info('- 2 Dynamic Menus (Master Data, Reports)');
        $this->command->info('- 4 Menu Items with different link types');
    }
}