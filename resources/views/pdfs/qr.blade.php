<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'QR Kod' }}</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            padding: 40px;
        }
        .qr-container {
            margin: 40px auto;
            max-width: 400px;
        }
        .qr-container svg {
            max-width: 100%;
            height: auto;
        }
        .uuid {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
            word-break: break-all;
        }
        .product-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .url {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>QR Kod</h1>

    @isset ($product)
        <div class="product-name">{{ $product->getName() }}</div>
    @endisset

    <div class="qr-container">
        {!! $qrSvg !!}
    </div>

    @isset ($item)
        <div class="uuid">{{ $item->uuid }}</div>
    @endisset

    @isset ($product)
        <div class="uuid">{{ $product->general_qr_code }}</div>
    @endisset

    <div class="url">{{ route('product.check', $item?->uuid ?? $product?->general_qr_code) }}</div>
</body>
</html>
