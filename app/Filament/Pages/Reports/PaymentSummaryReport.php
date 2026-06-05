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
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;

class PaymentSummaryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Ringkasan Pembayaran';

    protected string $view = 'filament.pages.reports.payment-summary-report';

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        return (string) ($record instanceof \Illuminate\Database\Eloquent\Model ? $record->method : $record['method']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->select('method', DB::raw('COUNT(payments.id) as total_transactions'), DB::raw('SUM(amount) as total_amount'))
                    ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
                    ->where('transactions.status', 'completed')
                    ->groupBy('method')
            )
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('method')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('total_transactions')
                    ->label('Jumlah Transaksi')
                    ->sortable(),
                TextColumn::make('total_amount')
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
                            
                            fputcsv($handle, ['Metode Pembayaran', 'Jumlah Transaksi', 'Total Pendapatan']);
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    strtoupper($record->method),
                                    $record->total_transactions,
                                    $record->total_amount,
                                ]);
                            }
                            fclose($handle);
                        }, 'laporan_ringkasan_pembayaran_' . date('Ymd_His') . '.csv');
                    }),
                Action::make('print_pdf')
                    ->label('Print PDF')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(fn () => url('/admin/reports/print?' . http_build_query([
                        'type' => 'payment-summary',
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
            return ['total_transactions' => 0, 'total_revenue' => 0, 'popular_method' => '-'];
        }

        $results = (clone $query)->get();
        $popular = $results->sortByDesc('total_transactions')->first();

        return [
            'total_transactions' => $results->sum('total_transactions'),
            'total_revenue' => $results->sum('total_amount'),
            'popular_method' => $popular ? strtoupper($popular->method) : '-',
        ];
    }
}
