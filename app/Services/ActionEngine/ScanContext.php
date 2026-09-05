<?php

namespace App\Services\ActionEngine;

use App\Models\Item;
use App\Models\Product;
use App\Models\Scan;

class ScanContext
{
    public Scan $scan;

    public ?Item $item = null;

    public ?Product $product = null;

    public static function fromScan(Scan $scan): self
    {
        $context = new self();
        $scan->loadMissing('item.batch.product');
        $context->scan = $scan;
        $context->item = $scan->item;
        $context->product = $scan->item?->batch?->product;

        return $context;
    }

    public function scanCountFor(string $scope): int
    {
        if (! $this->product) {
            return 0;
        }

        return match ($scope) {
            'product' => Scan::query()
                ->where('id', '<=', $this->scan->id)
                ->whereHas('item.batch', fn ($q) => $q->where('product_id', $this->product->id))
                ->count(),
            'company' => Scan::query()
                ->where('id', '<=', $this->scan->id)
                ->whereHas('item.batch.product', fn ($q) => $q->where('company_id', $this->product->company_id))
                ->count(),
            default => Scan::query()
                ->where('id', '<=', $this->scan->id)
                ->where('item_id', $this->scan->item_id)
                ->count(),
        };
    }

    public function previousScanForItem(): ?Scan
    {
        return Scan::query()
            ->where('item_id', $this->scan->item_id)
            ->where('id', '<', $this->scan->id)
            ->orderByDesc('id')
            ->first();
    }

    public function summary(): array
    {
        return [
            'scan_id' => $this->scan->id,
            'item_uuid' => $this->item?->uuid,
            'product_id' => $this->product?->id,
            'product_name' => $this->product?->name,
            'ip_address' => $this->scan->ip_address,
            'device_type' => $this->scan->device_type,
            'browser' => $this->scan->browser,
            'lat' => $this->scan->lat !== null ? (float) $this->scan->lat : null,
            'lng' => $this->scan->lng !== null ? (float) $this->scan->lng : null,
            'scanned_at' => $this->scan->scanned_at?->toIso8601String(),
        ];
    }
}
