<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;

class SlowMovingReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Produk Slow Moving';

    protected string $view = 'filament.pages.reports.slow-moving-report';

    public function table(Table $table): Table
    {
        $startDate = $this->tableFilters['date']['created_from'] ?? null;
        $endDate = $this->tableFilters['date']['created_until'] ?? null;
        $outletId = $this->tableFilters['outlet_id']['value'] ?? null;

        return $table
            ->query(
                Product::query()
                    ->select('products.id', 'products.name', 'products.stock')
                    ->selectSub(function ($query) use ($startDate, $endDate, $outletId) {
                        $query->selectRaw('COALESCE(SUM(quantity), 0)')
                            ->from('transaction_items')
                            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                            ->whereColumn('transaction_items.product_id', 'products.id')
                            ->where('transactions.status', 'completed')
                            ->when($startDate, fn($q) => $q->where('transactions.created_at', '>=', $startDate . ' 00:00:00'))
                            ->when($endDate, fn($q) => $q->where('transactions.created_at', '<=', $endDate . ' 23:59:59'))
                            ->when($outletId, fn($q) => $q->where('transactions.outlet_id', '=', $outletId));
                    }, 'total_sold')
            )
            ->defaultSort('total_sold', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('Stok Tersedia')
                    ->sortable(),
                TextColumn::make('total_sold')
                    ->label('Total Terjual')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'danger' : 'warning'),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(fn (Builder $query) => $query),
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->options(\App\Models\Outlet::pluck('name', 'id')->toArray())
                    ->query(fn (Builder $query) => $query)
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $records = $this->getFilteredSortedTableQuery()->get();
                        
                        return response()->streamDownload(function () use ($records) {
                            $handle = fopen('php://output', 'w');
                            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
                            
                            fputcsv($handle, ['Nama Produk', 'Stok Tersedia', 'Total Terjual']);
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    $record->name,
                                    $record->stock,
                                    $record->total_sold,
                                ]);
                            }
                            fclose($handle);
                        }, 'laporan_produk_slow_moving_' . date('Ymd_His') . '.csv');
                    }),
                Action::make('print_pdf')
                    ->label('Print PDF')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(fn () => url('/admin/reports/print?' . http_build_query([
                        'type' => 'slow-moving',
                        'created_from' => $this->tableFilters['date']['created_from'] ?? null,
                        'created_until' => $this->tableFilters['date']['created_until'] ?? null,
                        'outlet_id' => $this->tableFilters['outlet_id']['value'] ?? null,
                    ])))
                    ->openUrlInNewTab(),
            ]);
    }

    public function getReportStats(): array
    {
        $query = $this->getFilteredSortedTableQuery();
        if (!$query) {
            return ['total_products' => 0, 'total_stock' => 0, 'total_sold' => 0];
        }

        $results = (clone $query)->get();
        return [
            'total_products' => $results->count(),
            'total_stock' => $results->sum('stock'),
            'total_sold' => $results->sum('total_sold'),
        ];
    }
}
