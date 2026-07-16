<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrDownloadController extends Controller
{
    protected function userCanAccessProduct(Product $product): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->isSuperAdmin()) return true;
        return in_array($product->company_id, $user->getAccessibleCompanyIds());
    }

    protected function userCanAccessItem(Item $item): bool
    {
        $product = $item->batch?->product;
        if (!$product) return false;
        return $this->userCanAccessProduct($product);
    }

    public function downloadItemPng(Item $item): Response
    {
        abort_unless($this->userCanAccessItem($item), 403);

        $qrCode = QrCode::format('png')
            ->size(400)
            ->generate(route('product.check', $item->uuid));

        return response($qrCode, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-' . $item->uuid . '.png"',
        ]);
    }

    public function downloadItemPdf(Item $item): Response
    {
        abort_unless($this->userCanAccessItem($item), 403);

        $qrPng = QrCode::format('png')
            ->size(400)
            ->generate(route('product.check', $item->uuid));
        $qrBase64 = base64_encode($qrPng);

        $pdf = Pdf::loadView('pdfs.qr', [
            'item' => $item,
            'qrBase64' => $qrBase64,
            'title' => 'QR Kod - ' . $item->uuid,
        ]);

        return $pdf->download('qr-' . $item->uuid . '.pdf');
    }

    public function downloadProductPng(Product $product): Response
    {
        abort_unless($this->userCanAccessProduct($product), 403);

        $uuid = $product->general_qr_code ?? $product->batches()->first()?->items()->first()?->uuid;
        abort_if(!$uuid, 404, 'Bu ürün için QR kodu bulunamadı.');

        $qrCode = QrCode::format('png')
            ->size(400)
            ->generate(route('product.check', $uuid));

        return response($qrCode, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-' . $uuid . '.png"',
        ]);
    }

    public function downloadProductPdf(Product $product): Response
    {
        abort_unless($this->userCanAccessProduct($product), 403);

        $item = $product->batches()->first()?->items()->first();
        $uuid = $product->general_qr_code ?? $item?->uuid;
        abort_if(!$uuid, 404, 'Bu ürün için QR kodu bulunamadı.');

        $qrPng = QrCode::format('png')
            ->size(400)
            ->generate(route('product.check', $uuid));
        $qrBase64 = base64_encode($qrPng);

        $pdf = Pdf::loadView('pdfs.qr', [
            'item' => $item,
            'product' => $product,
            'qrBase64' => $qrBase64,
            'title' => 'QR Kod - ' . $product->getName(),
        ]);

        $filename = 'qr-' . str($product->getName())->slug() . '.pdf';
        return $pdf->download($filename);
    }
}
