<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Database\Eloquent\Builder;

class ScanHistoryWidget extends BaseTableWidget
{
    public ?\Illuminate\Database\Eloquent\Model $record = null;

    protected int | string | array $columnSpan = [
        'lg' => 7,
        'xl' => 7,
    ];

    public function getTableQuery(): Builder
    {
        if (!$this->record) {
            return Scan::query()->whereRaw('0 = 1');
        }

        return $this->record->scans()->getQuery()->latest('scanned_at')->take(10);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading('Son 10 Okutulma')
            ->columns([
                TextColumn::make('scanned_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->copyMessage('Kopyalandı'),
                TextColumn::make('browser')
                    ->label('Tarayıcı')
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('device_type')
                    ->label('Cihaz')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Mobile' => 'purple',
                        'Tablet' => 'blue',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Mobile' => 'Mobil',
                        'Tablet' => 'Tablet',
                        default => 'Desktop',
                    }),
                TextColumn::make('lat')
                    ->label('Konum')
                    ->formatStateUsing(function (?string $state, Scan $record): string {
                        if (!$record->lat || !$record->lng) {
                            return '-';
                        }
                        return number_format($record->lat, 4) . ', ' . number_format($record->lng, 4);
                    })
                    ->url(fn (Scan $record): ?string => $record->lat && $record->lng
                        ? "https://www.openstreetmap.org/?mlat={$record->lat}&mlon={$record->lng}&zoom=15"
                        : null)
                    ->openUrlInNewTab()
                    ->extraAttributes(fn (Scan $record): array => $record->lat && $record->lng
                        ? ['class' => 'text-primary-600 dark:text-primary-400 underline underline-offset-2']
                        : []),
            ])
            ->paginated(false)
            ->defaultSort('scanned_at', 'desc');
    }
}
