<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Role')
                    ->required()
                    ->unique(ignorable: fn ($record) => $record)
                    ->maxLength(255),
                TextInput::make('guard_name')
                    ->label('Guard Name')
                    ->default('web')
                    ->required()
                    ->dehydrated(true)
                    ->hidden(),
                CheckboxList::make('permissions')
                    ->label('Hak Akses (Permissions)')
                    ->relationship('permissions', 'name')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                        'lg' => 4,
                    ])
                    ->gridDirection('vertical')
                    ->searchable()
                    ->bulkToggleable()
                    ->descriptions(fn () => [
                        'view_users' => 'Melihat data pengguna',
                        'create_users' => 'Menambah data pengguna',
                        'edit_users' => 'Mengubah data pengguna',
                        'delete_users' => 'Menghapus data pengguna',
                        'view_products' => 'Melihat data produk',
                        'create_products' => 'Menambah data produk',
                        'edit_products' => 'Mengubah data produk',
                        'delete_products' => 'Menghapus data produk',
                        'view_categories' => 'Melihat data kategori',
                        'create_categories' => 'Menambah data kategori',
                        'edit_categories' => 'Mengubah data kategori',
                        'delete_categories' => 'Menghapus data kategori',
                        'view_outlets' => 'Melihat data outlet/wisata',
                        'create_outlets' => 'Menambah data outlet/wisata',
                        'edit_outlets' => 'Mengubah data outlet/wisata',
                        'delete_outlets' => 'Menghapus data outlet/wisata',
                        'view_transactions' => 'Melihat riwayat transaksi penjualan',
                        'create_transactions' => 'Membuat/melakukan transaksi penjualan',
                        'edit_transactions' => 'Mengubah data transaksi penjualan',
                        'delete_transactions' => 'Menghapus data transaksi penjualan',
                        'view_shifts' => 'Melihat shift kasir',
                        'create_shifts' => 'Membuka/menutup shift kasir',
                        'edit_shifts' => 'Mengubah data shift kasir',
                        'delete_shifts' => 'Menghapus data shift kasir',
                        'view_printers' => 'Melihat data printer kasir',
                        'create_printers' => 'Menambah printer kasir',
                        'edit_printers' => 'Mengubah printer kasir',
                        'delete_printers' => 'Menghapus printer kasir',
                        'view_stock_adjustments' => 'Melihat penyesuaian stok',
                        'create_stock_adjustments' => 'Melakukan penyesuaian stok',
                        'edit_stock_adjustments' => 'Mengubah penyesuaian stok',
                        'delete_stock_adjustments' => 'Menghapus penyesuaian stok',
                        'view_stock_movements' => 'Melihat mutasi/riwayat stok',
                        'create_stock_movements' => 'Melakukan mutasi stok',
                        'view_stock_transfers' => 'Melihat transfer stok antar outlet',
                        'create_stock_transfers' => 'Melakukan transfer stok antar outlet',
                        'view_audit_logs' => 'Melihat riwayat log aktivitas audit',
                        'view_reports' => 'Melihat dan mengekspor/mencetak laporan keuangan',
                    ]),
            ]);
    }
}
