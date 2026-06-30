<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TopScannedItemsWidget extends TableWidget
{
    protected int | string | array $columnSpan = [
        'lg' => 6,
        'xl' => 6,
    ];

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = \App\Models\Item::query()
            ->with('batch.product')
            ->withCount('scans');

        if ($user && !$user->isSuperAdmin()) {
            $query->whereHas('batch.product', function (Builder $q) use ($user) {
                $q->whereIn('company_id', $user->getAccessibleCompanyIds());
            });
        }

        return $table
            ->query($query->orderByDesc('scans_count')->limit(10))
            ->heading('En Çok Okutulan 10 Öğe')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable(),
                \Filament\Tables\Columns\TextColumn::make('batch.product.name')
                    ->label('Ürün Adı')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('scans_count')
                    ->label('Okunma Sayısı')
                    ->badge()
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
