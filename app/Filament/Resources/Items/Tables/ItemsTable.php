<?php

namespace App\Filament\Resources\Items\Tables;

use App\Models\Item;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch.id')
                    ->searchable(),
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                \Filament\Tables\Columns\ViewColumn::make('qr')
                    ->label('QR Kod')
                    ->view('filament.tables.columns.qr-code'),
                TextColumn::make('scans_count')
                    ->label('Okunma')
                    ->counts('scans')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('batch_id')
                    ->label('Parti')
                    ->options(function () {
                        $user = auth()->user();
                        $query = \App\Models\Batch::query();

                        if ($user && !$user->isSuperAdmin()) {
                            $query->whereHas('product', function (\Illuminate\Database\Eloquent\Builder $q) use ($user) {
                                $q->whereIn('company_id', $user->getAccessibleCompanyIds());
                            });
                        }

                        return $query->pluck('batch_number', 'id');
                    }),
                \Filament\Tables\Filters\SelectFilter::make('product_id')
                    ->label('Ürün')
                    ->options(function () {
                        $user = auth()->user();
                        $query = \App\Models\Product::query();

                        if ($user && !$user->isSuperAdmin()) {
                            $query->where('company_id', $user->company_id);
                        }

                        return $query->get()->mapWithKeys(fn ($product) => [$product->id => $product->getName()]);
                    })
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('batch', function (\Illuminate\Database\Eloquent\Builder $query) use ($data) {
                                $query->where('product_id', $data['value']);
                            });
                        }
                    })
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('qr_png')
                    ->label('QR İndir (PNG)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Item $record) => route('admin.items.qr-png', $record)),
                Action::make('qr_pdf')
                    ->label('QR İndir (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Item $record) => route('admin.items.qr-pdf', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    \Filament\Actions\BulkAction::make('export_csv')
                        ->label('CSV Olarak İndir (Excel)')
                        ->icon('heroicon-o-document-text')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $filename = 'ogeler-' . now()->format('Y-m-d-H-i-s') . '.csv';
                            $headers = [
                                "Content-type"        => "text/csv",
                                "Content-Disposition" => "attachment; filename=$filename",
                                "Pragma"              => "no-cache",
                                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                                "Expires"             => "0"
                            ];

                            $callback = function() use ($records) {
                                $file = fopen('php://output', 'w');
                                // Excel'in Türkçe karakterleri düzgün okuması için UTF-8 BOM ekliyoruz
                                echo "\xEF\xBB\xBF";
                                fputcsv($file, ['UUID', 'QR Kod Linki', 'Olusturulma Tarihi']);

                                foreach ($records as $record) {
                                    fputcsv($file, [
                                        $record->uuid,
                                        route('product.check', $record->uuid),
                                        $record->created_at->format('Y-m-d H:i:s')
                                    ]);
                                }
                                fclose($file);
                            };

                            return response()->stream($callback, 200, $headers);
                        }),
                        
                    \Filament\Actions\BulkAction::make('export_txt')
                        ->label('TXT Olarak İndir')
                        ->icon('heroicon-o-document')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $filename = 'qr-kodlar-' . now()->format('Y-m-d-H-i-s') . '.txt';
                            $headers = [
                                "Content-type"        => "text/plain",
                                "Content-Disposition" => "attachment; filename=$filename",
                            ];

                            $callback = function() use ($records) {
                                $file = fopen('php://output', 'w');
                                foreach ($records as $record) {
                                    fwrite($file, route('product.check', $record->uuid) . PHP_EOL);
                                }
                                fclose($file);
                            };

                            return response()->stream($callback, 200, $headers);
                        }),
                        
                    \Filament\Actions\BulkAction::make('export_json')
                        ->label('JSON Olarak İndir')
                        ->icon('heroicon-o-code-bracket')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $filename = 'ogeler-' . now()->format('Y-m-d-H-i-s') . '.json';
                            $headers = [
                                "Content-type"        => "application/json",
                                "Content-Disposition" => "attachment; filename=$filename",
                            ];
                            
                            $data = $records->map(function($record) {
                                return [
                                    'uuid' => $record->uuid,
                                    'qr_link' => route('product.check', $record->uuid),
                                    'created_at' => $record->created_at->toIso8601String(),
                                ];
                            })->toArray();

                            $callback = function() use ($data) {
                                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            };

                            return response()->stream($callback, 200, $headers);
                        }),
                ]),
            ]);
    }
}
