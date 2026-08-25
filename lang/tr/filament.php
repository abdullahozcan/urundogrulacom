<?php

return [
    'admin' => [
        'language_switcher' => 'Dil',
    ],
    'fields' => [
        'name' => 'Ürün Adı',
        'description' => 'Açıklama',
        'category' => 'Kategori',
        'redirect_url' => 'Yönlendirme Linki (URL)',
    ],
    'product' => [
        'label' => 'Ürün',
        'plural_label' => 'Ürünler',
        'navigation_group' => 'Ürün Yönetimi',

        'sections' => [
            'general' => 'Genel Bilgiler',
            'general_desc' => 'Ürün adı ve açıklaması',
            'kunya' => 'Künye Bilgileri',
            'kunya_desc' => 'Kategori, tarih, görsel ve ürün tipi',
            'documents' => 'Belgeler',
            'documents_desc' => 'PDF, PNG, JPEG, GIF dosyaları',
            'social' => 'Sosyal Medya',
            'social_desc' => 'Ürünün sosyal medya hesapları (isteğe bağlı)',
            'qr' => 'QR Kod Ayarları',
            'qr_desc' => 'QR kod türü ve genel QR kod değeri',
            'digital' => 'Dijital İçerik',
            'digital_desc' => 'Dijital ürünler için dosya ve link ayarları',
        ],

        'table' => [
            'name' => 'Ürün Adı',
            'category' => 'Kategori',
            'qr_mode' => 'QR Modu',
            'qr_code' => 'Genel QR',
            'serial' => 'Seri No',
            'expiry' => 'Son Kullanma',
            'company' => 'Firma',
        ],

        'batches_table' => [
            'title' => 'Partiler',
            'batch_no' => 'Parti No',
            'quantity' => 'Adet',
            'created_items' => 'Oluşturulan Adet',
            'created_at' => 'Oluşturulma',
        ],

        'reviews_table' => [
            'title' => 'Değerlendirmeler',
            'rating' => 'Puan',
            'comment' => 'Yorum',
            'approval' => 'Onay',
            'date' => 'Tarih',
        ],
    ],

    'batch' => [
        'label' => 'Parti',
        'plural_label' => 'Partiler',
        'navigation_group' => 'Ürün Yönetimi',
    ],

    'item' => [
        'label' => 'Öğe (Item)',
        'plural_label' => 'Öğeler',
        'navigation_group' => 'Ürün Yönetimi',
    ],

    'actions' => [
        'add_batch' => 'Parti Ekle',
        'new_batch' => 'Yeni Parti Oluştur',
        'edit_batch' => 'Partiyi Düzenle',
        'view_items' => 'Öğeleri Gör',
        'approve' => 'Onayla',
        'unapprove' => 'Onayı Kaldır',
        'approved' => 'Yorum onaylandı.',
        'unapproved' => 'Yorum onayı kaldırıldı.',
    ],
];