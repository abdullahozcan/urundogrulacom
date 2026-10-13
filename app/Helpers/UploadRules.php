<?php

namespace App\Helpers;

/**
 * Tek noktadan yönetilen dosya yükleme kuralları.
 *
 * Gerekçe: her FileUpload alanı kendi kuralını yazdığında kolayca tutarsız
 * kalır ve `->image()` gibi kısayollar `image/*` kabul ettiği için SVG de
 * (kalıcı XSS vektörü) içeri geçer.
 */
final class UploadRules
{
    /**
     * `image/*` kullanılmaz: SVG, tarayıcıda <img> içinde çalışmasa da
     * `/storage/....svg` adresine doğrudan gidildiğinde uygulamanın kendi
     * origin'inde script çalıştırır. Yalnızca yeniden kodlanabilir formatlar.
     */
    public const IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /** Ürün/partner/blog görselleri için üst sınır (KB). */
    public const IMAGE_MAX_KB = 4096;

    /** Ürün belgeleri (sertifika, PDF) için üst sınır (KB). */
    public const DOCUMENT_MAX_KB = 20480;

    /** Ürün belgeleri: yalnızca belge formatları, görsel değil. */
    public const DOCUMENT_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /** Dijital teslim içeriği (ücretli PDF / video) için üst sınır (KB). */
    public const DIGITAL_MAX_KB = 51200;

    /**
     * Yüklenen görselleri 1600px genişliğe indirger ve yeniden kodlar.
     *
     * Hem performans (12 MP telefon fotoğrafları her sayfada yükleniyor) hem
     * gizlilik kazancı var: yeniden kodlama EXIF bloğunu düşürür, yani
     * yükleyen çalışanın GPS konumu sızmaz.
     */
    public static function hardenImage($component, int $maxKb = self::IMAGE_MAX_KB)
    {
        // Order matters: `image()` resets acceptedFileTypes to the permissive
        // `image/*`, so the strict allowlist must be applied afterwards. Both
        // `mimetypes` rules stay registered, and the strict list is also what
        // the browser accept attribute renders.
        return $component
            ->image()
            ->acceptedFileTypes(self::IMAGE_MIMES)
            ->maxSize($maxKb)
            ->imageResizeMode('cover')
            ->imageResizeTargetWidth(1600)
            ->imageResizeTargetHeight(1600)
            ->orientImagesFromExif();
    }

    /** Ürün belgesi: boyut sınırlı, MIME beyaz listeli, disk seçilebilir. */
    public static function hardenDocument($component, ?string $disk = 'public')
    {
        return $component
            ->acceptedFileTypes(self::DOCUMENT_MIMES)
            ->maxSize(self::DOCUMENT_MAX_KB)
            ->disk($disk);
    }
}