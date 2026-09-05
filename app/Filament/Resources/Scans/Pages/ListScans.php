<?php

namespace App\Filament\Resources\Scans\Pages;

use App\Filament\Resources\Scans\ScanResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListScans extends ListRecords
{
    protected static string $resource = ScanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->exportCsvAction(),
            CreateAction::make(),
        ];
    }

    private function exportCsvAction(): Action
    {
        return Action::make('exportCsv')
            ->label('CSV İndir')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function (): StreamedResponse {
                $query = $this->getFilteredTableQuery()
                    ->with(['item.batch.product']);

                return response()->streamDownload(function () use ($query) {
                    $output = fopen('php://output', 'w');

                    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    fputcsv($output, [
                        'Tarama ID',
                        'Tarama Zamanı',
                        'Item UUID',
                        'Ürün',
                        'Kuruluş',
                        'Cihaz Tipi',
                        'Tarayıcı',
                        'IP',
                        'Enlem',
                        'Boylam',
                    ]);

                    $query->chunk(1000, function ($scans) use ($output) {
                        foreach ($scans as $scan) {
                            fputcsv($output, [
                                $scan->id,
                                $scan->scanned_at?->format('d.m.Y H:i:s') ?? $scan->created_at?->format('d.m.Y H:i:s'),
                                $scan->item?->uuid,
                                $scan->item?->batch?->product?->getName() ?? '-',
                                $scan->item?->batch?->product?->company?->name ?? '-',
                                $scan->device_type,
                                $scan->browser,
                                $scan->ip_address,
                                $scan->lat,
                                $scan->lng,
                            ]);
                        }
                    });

                    fclose($output);
                }, 'taramalar-' . now()->format('Ymd-His') . '.csv', [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                ]);
            });
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ScansMapWidget::class,
        ];
    }
}
