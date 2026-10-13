<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public, signed asset delivery for consumer-facing files.
 *
 * Product documents and digital (licensed) product files used to live on the
 * `public` disk and were served from permanent, unauthenticated
 * `/storage/...` URLs. They now live on the private disk and are only
 * reachable through short-lived signed URLs, so a leaked URL stops working
 * and nothing is guessable or enumerable.
 */
class PublicAssetController extends Controller
{
    /** How long a generated asset URL stays valid. */
    private const URL_TTL_MINUTES = 60;

    /**
     * Signed URL for a product document (certificate / official document).
     */
    public function document(Request $request, ProductDocument $document): StreamedResponse
    {
        $this->assertSignature($request);
        $this->assertNotExpired($document->product);

        $path = $document->file_path;
        $disk = $this->diskFor($path);

        abort_unless(Storage::disk($disk)->exists($path), 404);

        return $this->stream($disk, $path, $document->original_name ?: basename($path), 'inline');
    }

    /**
     * Signed URL for digital product content (licensed PDF / video).
     */
    public function digitalContent(Request $request, Product $product): StreamedResponse
    {
        $this->assertSignature($request);

        $this->assertNotExpired($product);

        abort_unless(
            $product->type === 'digital' && filled($product->digital_file_path),
            404
        );

        $path = $product->digital_file_path;
        $disk = $this->diskFor($path);

        abort_unless(Storage::disk($disk)->exists($path), 404);

        return $this->stream(
            $disk,
            $path,
            $product->getName() . '.' . pathinfo($path, PATHINFO_EXTENSION),
            'inline'
        );
    }

    /**
     * Build a fresh signed URL for a stored path.
     */
    public static function signedUrl(string $routeName, array $parameters): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes(self::URL_TTL_MINUTES),
            $parameters
        );
    }

    private function assertSignature(Request $request): void
    {
        abort_unless($request->hasValidSignature(), 403, 'Geçersiz veya süresi dolmuş bağlantı.');
    }

    /**
     * An expired product must not hand out its digital payload. Previously
     * `expiration_date` was only displayed on the check page and never
     * enforced, so a lapsed licence stayed downloadable forever.
     */
    private function assertNotExpired(?Product $product): void
    {
        if (!$product) {
            abort(404);
        }

        if ($product->expiration_date && $product->expiration_date->isPast()) {
            abort(410, 'Bu ürünün kullanım/servis süresi dolmuştur.');
        }
    }

    /**
     * Records written before the private-disk migration still point at the
     * public disk; fall back to it rather than 404 on legacy rows.
     */
    private function diskFor(string $path): string
    {
        return Storage::disk('public')->exists($path) ? 'public' : 'local';
    }

    private function stream(string $disk, string $path, string $downloadName, string $disposition): StreamedResponse
    {
        return Storage::disk($disk)->download($path, $downloadName, [
            'Cache-Control' => 'private, max-age=300',
            'Content-Disposition' => $disposition . '; filename="' . addslashes($downloadName) . '"',
        ]);
    }
}