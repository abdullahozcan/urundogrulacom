# ÜrünDoğrula — Proje Özeti

**Domain:** Çok kiracılı (multi-tenant) ürün doğrulama platformu. Şirketler ürünlerine QR kod üretir, tüketiciler kodu okutarak orijinallik sorgusu yapar.

---

## Kullanılan Teknolojiler

| Teknoloji | Versiyon / Detay |
|---|---|
| PHP | ^8.3 |
| Framework | Laravel ^13.0 |
| Admin Panel | Filament 3.x (Shield ile yetkilendirme) |
| Frontend | Livewire 3, Alpine.js, Tailwind CSS 4, Vite 8 |
| Multi-Tenancy | Hiyerarşik (parent→child), 1 katman, `company_id` izolasyonu + `parent_id` |
| Yetkilendirme | Spatie Laravel Permission + Filament Shield |
| QR | simplesoftwareio/simple-qrcode |
| PDF Üretimi | barryvdh/laravel-dompdf |
| Cihaz Tespiti | hisorange/browser-detect |
| Dil Seçici (Filament) | prunacatalin/filament-locale-switcher |
| Harita | OpenStreetMap (Leaflet) |
| Çoklu Dil | tr (varsayılan) / en, JSON translatable |
| Test | PHPUnit 12.x |
| DB | MySQL (Laravel Migration) |

---

## Dizin Yapısı

```
app/
├── Console/Commands/GrantSuperAdmin.php    # CLI: kullanıcıyı super admin yap
├── Events/
│   └── ScanRecorded.php                     # Scan oluşunca fırlatılır (aksiyon motoru girişi)
├── Filament/
│   ├── Resources/                           # 11 resource (her biri → Form/Table/Pages)
│   │   ├── Batches/
│   │   ├── Categories/
│   │   ├── Companies/
│   │   │   ├── RelationManagers/
│   │   │   │   └── ChildrenRelationManager.php   # Alt firmaları yönet
│   │   ├── Items/
│   │   ├── Pages/
│   │   ├── Partners/
│   │   ├── Posts/
│   │   ├── Products/
│   │   │   └── RelationManagers/
│   │   │       ├── BatchesRelationManager.php    # Ürünün partileri
│   │   │       └── ReviewsRelationManager.php    # Değerlendirme yönetimi (onay/red)
│   │   ├── Scans/
│   │   ├── Settings/
│   │   └── Users/
│   └── Widgets/                             # Dashboard widgetları:
│       ├── AccountWidget.php
│       ├── CompanyInfoWidget.php
│       ├── ItemScansChart.php               # Tarama grafiği
│       ├── MapWidget.php                    # Tarama haritası (Leaflet, dark mode)
│       ├── ScanHistoryWidget.php
│       ├── ScanLocationWidget.php
│       ├── ScansMapWidget.php
│       └── TopScannedItemsWidget.php
├── Helpers/SettingHelper.php                # Site ayarları cache helper
├── Http/
│   ├── Controllers/
│   │   ├── ContactController.php            # İletişim sayfası + form (throttle: 5 dk/10)
│   │   └── QrDownloadController.php         # Item/ürün QR indirme (PNG/PDF/SVG)
│   └── Middleware/SetLocale.php             # Dil middleware
├── Listeners/
│   └── EvaluateActionRules.php              # ScanRecorded → aksiyon motoru (fail-safe, feature flag'li)
├── Livewire/
│   ├── CheckProduct.php                     # Public QR sorgulama + değerlendirme + redirect
│   └── CompanySwitcher.php                  # Sidebar'da alt firma seçici
├── Models/                                  # Company, Product, ProductDocument,
│                                            # ProductReview, Batch, Item, Scan,
│                                            # ActionRule, ActionLog, Category, Post,
│                                            # Page, Partner, Setting, User
├── Policies/RolePolicy.php                  # Role bazlı policy
├── Providers/
│   ├── AppServiceProvider.php
│   └── Filament/AdminPanelProvider.php
└── Traits/HasTranslations.php               # Çoklu dil trait'i

config/
├── filament-locale-switcher.php             # Dil seçici widget ayarları
├── languages.php                             # tr/en dil yapılandırması
└── permission.php                            # Spatie Permission ayarları

database/
├── migrations/                               # 27 migration
├── factories/                               # Company, Product, Category, User factory
└── seeders/DatabaseSeeder.php               # Demo veri + roller

routes/web.php                               # Public route'lar

tests/
├── Feature/
│   ├── CategoryTenantIsolationTest.php
│   ├── CrossTenantAssignmentTest.php
│   ├── NavigationVisibilityTest.php
│   ├── ProductQrModeTest.php
│   └── ExampleTest.php
└── Unit/ExampleTest.php
```

---

## Veri Modeli

### `companies` — Kiracı şirketler (hiyerarşik)
- `id`, `parent_id` (self FK, nullable), `name`, `is_active`, `max_sub_companies` (null=sınırsız)
- `parent_id` → bir üst firmaya bağlanır (1 katman, parent→child)
- `children()` → alt firmalar (hasMany)
- `accessibleCompanyIds()` → kendi + alt company ID'leri
- `canCreateSubCompany()` → `max_sub_companies` limit kontrolü

### `users` — Kullanıcılar (Company'e bağlı, Spatie rolleri)
- `company_id` (FK), name, email, password
- `isSuperAdmin()`, `isCompanyScoped()` yardımcıları

### `categories` — Ürün kategorileri (hiyerarşik)
- `company_id` (FK), `parent_id` (self FK), `name`, `slug`, `description`, `is_active`

### `products` — Ürünler (çekirdek varlık)
- `company_id` (FK), `category_id` (FK), `image`
- `name`, `description`: **JSON translatable** `{"tr": "...", "en": "..."}` (HasTranslations trait)
- `type`: physical | digital
- `digital_content_type`: pdf | youtube | file
- `digital_file_path`, `expiration_date`, `serial_number`
- `qr_mode`: unique | general
- `general_qr_code`: nullable, sadece `qr_mode=general` iken dolu
- `redirect_url`: nullable, her iki modda da kullanılabilir — QR okutulduğunda tarama kaydı düşüldükten sonra kullanıcı bu URL'e (en fazla 2 sn içinde) yönlendirilir; genel QR ürünlerinde anında yönlendirilir (item/scan kaydı yok)
- Sosyal medya: `whatsapp`, `instagram`, `facebook`, `twitter`, `youtube`, `website`, `email`
- Validasyon: category_id aynı company'e ait olmalı

### `batches` — Partiler (Item'ları toplu üretir)
- `product_id`, `batch_number`, `quantity`
- `created` event → `quantity` adet Item (UUID) otomatik üretir (1000'er chunk)

### `items` — Tekil ürün örnekleri
- `batch_id`, `uuid`, `is_scanned`

### `scans` — QR okutma kayıtları
- `item_id`, `ip_address`, `lat`, `lng`, `browser`, `device_type`, `scanned_at`

### `product_documents` — Ürün belgeleri
- `product_id` (FK, cascade), `file_path`, `original_name`, `mime_type`, `sort_order`
- Check sayfasında ve admin'de indirilebilir belgeler

### `product_reviews` — Değerlendirmeler
- `product_id` (FK, cascade), `rating` (tinyint), `comment` (nullable)
- `cookie_token`: tek cihaz tek oy (unique: `product_id + cookie_token`)
- `is_approved`: admin onayı gerekir (default false)

### `action_rules` / `action_logs` — Aksiyon motoru (Faz 2, flag ile açılır)
- Kural: `company_id`, `product_id` (nullable=firma geneli), `trigger_type` (scan_count | first_scan | date_range | item_revisit), `trigger_config` (JSON), `action_type` (show_content | redirect | webhook | notify_admin), `action_payload` (JSON), `priority`, `max_executions`, zaman penceresi
- Log: `action_rule_id` + `scan_id` unique (dedup), `result`: success | failed | skipped
- Mimar: `Scan::created` → `ScanRecorded` event → `EvaluateActionRules` listener (try/catch'li, `action_engine_enabled` settings flag'i kapalıyken no-op). Motor: `app/Services/ActionEngine/ActionEngine.php`
- **Sistem etkisi sıfır:** motor hatası scan kaydını ve check akışını etkilemez

### `posts` / `pages` / `settings` — Dinamik içerik (translatable)
- JSON translatable alanlar: `title`, `slug`, `content` / `value`

### `partners` — Anasayfa partner logoları
- `name`, `logo`, `is_active`, `sort_order`

---

## Temel İş Akışı

1. **Admin** → Ürün oluşturur (kategori + QR modu + dijital içerik + sosyal medya)
2. **Admin** → Parti oluşturur (adet girer) → otomatik UUID'li Item'lar üretilir
3. **Item UUID** → QR kod basılır, ürüne iliştirilir
4. **Tüketici** → Kodu okutur → `/check/{uuid}` Livewire sayfası
5. **Scan** kaydı düşülür (konum, tarayıcı, IP, cihaz tipi)
6. Ürün detayları + dijital içerik (PDF/video) + ürün belgeleri (product_documents) + sosyal medya bağlantıları görüntülenir
   - `redirect_url` dolu ürünlerde: tarama kaydı sonrası kullanıcı (en fazla 2 sn bekleterek) `redirect_url`'e yönlendirilir; genel QR ürünlerinde `general_qr_code` ile eşleşen ürün anında yönlendirilir
7. **Tüketici değerlendirme:** Check sayfasından puan (1-5) + yorum gönderilir (cookie_token ile tek oy); admin, Products → ReviewsRelationManager'dan onaylar/red eder

---

## Multi-Tenant Mimarisi (Hiyerarşik)

- **Super Admin:** Tüm şirket ve alt şirket verilerini görür. Sistem menüsü (Users, Companies, Pages, Posts, Partners, Settings) ona açıktır.
- **Parent Company Admin:** Kendi şirketinin + tüm alt şirketlerinin verilerini (Category, Product, Batch, Item, User) görür. Sidebar'daki **CompanySwitcher** ile hangi alt firmanın verilerini göreceğini seçebilir.
- **Child Company Admin:** Sadece kendi şirketinin verilerini görür.
- **Tenant Scoping:** Tüm resource'larda `$user->company_id` yerine `$user->getAccessibleCompanyIds()` kullanılır (kendi ID + alt company ID'leri).
- **CompanySwitcher:** Filament sidebar'ında `panels::sidebar.start` renderHook ile gösterilen Livewire component. Session'a `active_company_id` yazar.
- **Filament Resources + Widgets:** `getEloquentQuery()` override ile `accessible_company_ids` scope'u uygulanır.
- **Yetkisiz erişim:** 403/404 döner.
- **Alt Firma Limiti:** Super admin, Company edit sayfasında `max_sub_companies` alanından belirler. `canCreateSubCompany()` metodu kontrol eder.

---

## Public Route'lar (routes/web.php)

| HTTP | Path | İşlev |
|---|---|---|
| GET | `/` | Anasayfa (hero, blog, partner) |
| GET | `/check/{uuid}` | QR sorgulama (Livewire) + değerlendirme + redirect |
| GET | `/blog/{slug}` | Blog detay (slug tüm destekli dillerde eşlenir) |
| GET | `/p/{slug}` | Statik sayfa (slug tüm destekli dillerde eşlenir) |
| GET | `/iletisim` | İletişim (ContactController) |
| POST | `/iletisim` | İletişim formu gönderimi (throttle: 10 dk içinde 5) |
| GET | `/sitemap.xml` | XML site haritası |
| GET | `/locale/{lang}` | Dil değiştirme |

### Admin QR İndirme Route'ları (`auth` + `admin` prefix)

| HTTP | Path | İşlev |
|---|---|---|
| GET | `/admin/items/{item}/qr-png` | Item QR PNG indir |
| GET | `/admin/items/{item}/qr-pdf` | Item QR PDF indir (Dompdf) |
| GET | `/admin/items/{item}/qr-svg` | Item QR SVG indir |
| GET | `/admin/products/{product}/qr-png` | Ürün (genel) QR PNG indir |
| GET | `/admin/products/{product}/qr-pdf` | Ürün (genel) QR PDF indir |
| GET | `/admin/products/{product}/qr-svg` | Ürün (genel) QR SVG indir |

---

## Önemli Proje Kuralları

### Tasarım (DESIGN.md)
- **"Architectural Truth"** felsefesi: sınırlar çizgiyle değil, renk ton geçişleriyle belirlenir
- 1px solid border yasak; kenarlar background color shift ile ayrılır
- Glassmorphism (80% opacity + backdrop-blur: 20px)
- Inter font family, agresif tipografi skalası
- QR sorgulamada "Trust Scan" animasyonu (tertiary gradient sweep)

### Kod
- Filament Resource'ları → `Schemas/` (Form) + `Tables/` (Table) + `Pages/` ayrımı
- Çoklu dil: JSON kolonlarda `{"tr": "...", "en": "..."}` formatı
- Tenant izolasyonu: tüm sorgularda `->where('company_id', auth()->user()->company_id)`
- Item üretimi: chunk (1000), transaction içinde
- Cache: Setting modeli `site_settings` cache key kullanır

---

## Test

```bash
composer test
# veya
php artisan config:clear --ansi && php artisan test
```

### Test Dosyaları
- `CategoryTenantIsolationTest` — kategori CRUD tenant izolasyonu
- `ProductQrModeTest` — unique/general QR modu validasyonu, `redirect_url` davranışı
- `CrossTenantAssignmentTest` — farklı şirket kategorisi atama reddi
- `NavigationVisibilityTest` — super admin vs company admin menü (Company+User artık company admin'e de açık)
- `SubCompanyIsolationTest` — parent'ın child verilerini görebildiği, child'ın parent'ı göremediği, sibling'lerin birbirini görmediği doğrulanır

---

## Komutlar

```bash
# Ortam kurulumu
composer setup

# Geliştirme sunucusu
composer dev

# Test
composer test

# Kullanıcıyı super admin yap
php artisan user:super-admin {email} [--create] [--name=] [--password=]
```

---

## 🔄 Güncelleme Notu

**Bu dosya, projede yapılan her değişiklikten sonra güncellenmelidir.** Özellikle:
- Yeni migration / model / resource eklendiğinde
- Route yapısı değiştiğinde
- Bağımlılık (composer.json / package.json) değiştiğinde
- Test dosyası eklendiğinde/silindiğinde
- Tenant / hiyerarşi mantığı değiştiğinde
- Sidebar / navigasyon yapısı değiştiğinde
- Tasarım sistemi (DESIGN.md) güncellendiğinde
