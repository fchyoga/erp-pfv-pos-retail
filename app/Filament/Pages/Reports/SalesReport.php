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
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Penjualan & Transaksi';

    protected string $view = 'filament.pages.reports.sales-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with(['outlet', 'user', 'payment'])
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment.method')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? '-'))
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('Diskon')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('tax')
                    ->label('Pajak')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
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
                    ->relationship('outlet', 'name'),
                SelectFilter::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name'),
                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'CASH',
                        'qris' => 'QRIS',
                        'transfer' => 'TRANSFER',
                        'card' => 'CARD',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('payment', fn($q) => $q->where('method', $value))
                        );
                    }),
                SelectFilter::make('status')
                    ->label('Status Transaksi')
                    ->options([
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                    ]),
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
                            
                            fputcsv($handle, ['Invoice', 'Tanggal', 'Outlet', 'Kasir', 'Metode Pembayaran', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status']);
                            foreach ($records as $record) {
                                fputcsv($handle, [
                                    $record->invoice_number,
                                    $record->created_at->format('Y-m-d H:i:s'),
                                    $record->outlet?->name ?? '-',
                                    $record->user?->name ?? '-',
                                    strtoupper($record->payment?->method ?? '-'),
                                    $record->subtotal,
                                    $record->discount,
                                    $record->tax,
                                    $record->total,
                                    ucfirst($record->status),
                                ]);
                            }
                            fclose($handle);
                        }, 'laporan_transaksi_penjualan_' . date('Ymd_His') . '.csv');
                    }),
                Action::make('print_pdf')
                    ->label('Print PDF')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(fn () => url('/admin/reports/print?' . http_build_query([
                        'type' => 'sales',
                        'created_from' => $this->tableFilters['date']['created_from'] ?? null,
                        'created_until' => $this->tableFilters['date']['created_until'] ?? null,
                        'outlet_id' => $this->tableFilters['outlet_id']['value'] ?? null,
                        'user_id' => $this->tableFilters['user_id']['value'] ?? null,
                        'payment_method' => $this->tableFilters['payment_method']['value'] ?? null,
                        'status' => $this->tableFilters['status']['value'] ?? null,
                    ])))
                    ->openUrlInNewTab(),
            ]);
    }

    public function getReportStats(): array
    {
        $query = $this->getFilteredSortedTableQuery();
        if (!$query) {
            return ['total_sales' => 0, 'total_transactions' => 0, 'total_discount' => 0, 'total_tax' => 0];
        }

        $results = (clone $query)->get();
        return [
            'total_sales' => $results->sum('total'),
            'total_transactions' => $results->count(),
            'total_discount' => $results->sum('discount'),
            'total_tax' => $results->sum('tax'),
        ];
    }
}
