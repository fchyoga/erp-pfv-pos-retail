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
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;

class BestSellerReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Produk Terlaris';

    protected string $view = 'filament.pages.reports.best-seller-report';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        return (string) ($record instanceof \Illuminate\Database\Eloquent\Model ? $record->product_id : $record['product_id']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionItem::query()
                    ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(transaction_items.subtotal) as total_revenue'))
                    ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                    ->where('transactions.status', 'completed')
                    ->groupBy('product_id', 'product_name')
            )
            ->defaultSort('total_sold', 'desc')
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('total_sold')
                    ->label('Total Terjual (Qty)')
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->where('transactions.created_at', '>=', $date . ' 00:00:00'),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->where('transactions.created_at', '<=', $date . ' 23:59:59'),
                            );
                    }),
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('transaction.outlet', 'name')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->where('transactions.outlet_id', $value)
                        );
                    })
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
                            
                            fputcsv($handle, ['Nama Produk', 'Total Terjual (Qty)', 'Total Pendapatan']);
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    $record->product_name,
                                    $record->total_sold,
                                    $record->total_revenue,
                                ]);
                            }
                            fclose($handle);
                        }, 'laporan_produk_terlaris_' . date('Ymd_His') . '.csv');
                    }),
                Action::make('print_pdf')
                    ->label('Print PDF')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(fn () => url('/admin/reports/print?' . http_build_query([
                        'type' => 'best-seller',
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
            return ['total_sold' => 0, 'total_revenue' => 0, 'product_count' => 0];
        }

        $results = (clone $query)->get();
        return [
            'total_sold' => $results->sum('total_sold'),
            'total_revenue' => $results->sum('total_revenue'),
            'product_count' => $results->count(),
        ];
    }
}
