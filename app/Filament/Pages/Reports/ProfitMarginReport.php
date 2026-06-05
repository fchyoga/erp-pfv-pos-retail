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

class ProfitMarginReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Profit Margin';

    protected string $view = 'filament.pages.reports.profit-margin-report';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        return (string) ($record instanceof \Illuminate\Database\Eloquent\Model ? $record->product_id : $record['product_id']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionItem::query()
                    ->select(
                        'product_id', 
                        'product_name', 
                        DB::raw('SUM(quantity) as total_sold'),
                        DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                        DB::raw('SUM(cost_price * quantity) as total_cost'),
                        DB::raw('SUM(transaction_items.subtotal) - SUM(cost_price * quantity) as gross_profit')
                    )
                    ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                    ->where('transactions.status', 'completed')
                    ->groupBy('product_id', 'product_name')
            )
            ->defaultSort('gross_profit', 'desc')
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('total_sold')
                    ->label('Qty Terjual')
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label('Pendapatan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Total Modal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('gross_profit')
                    ->label('Gross Profit')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
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
                            
                            fputcsv($handle, ['Nama Produk', 'Qty Terjual', 'Pendapatan', 'Total Modal', 'Gross Profit']);
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    $record->product_name,
                                    $record->total_sold,
                                    $record->total_revenue,
                                    $record->total_cost,
                                    $record->gross_profit,
                                ]);
                            }
                            fclose($handle);
                        }, 'laporan_profit_margin_' . date('Ymd_His') . '.csv');
                    }),
                Action::make('print_pdf')
                    ->label('Print PDF')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(fn () => url('/admin/reports/print?' . http_build_query([
                        'type' => 'profit-margin',
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
            return ['total_revenue' => 0, 'total_cost' => 0, 'gross_profit' => 0];
        }

        $results = (clone $query)->get();
        return [
            'total_revenue' => $results->sum('total_revenue'),
            'total_cost' => $results->sum('total_cost'),
            'gross_profit' => $results->sum('gross_profit'),
        ];
    }
}
