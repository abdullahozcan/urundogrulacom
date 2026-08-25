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
| Harita | OpenStreetMap (Leaflet) |
| Çoklu Dil | tr (varsayılan) / en, JSON translatable |
| Test | PHPUnit 12.x |
| DB | MySQL (Laravel Migration) |

---

## Dizin Yapısı

```
app/
├── Console/Commands/GrantSuperAdmin.php    # CLI: kullanıcıyı super admin yap
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
│   │   ├── Scans/
│   │   ├── Settings/
│   │   └── Users/
│   └── Widgets/                             # Dashboard widgetları (harita, grafik, vs.)
├── Helpers/SettingHelper.php                # Site ayarları cache helper
├── Http/Middleware/SetLocale.php            # Dil middleware
├── Livewire/
│   ├── CheckProduct.php                     # Public QR sorgulama
│   └── CompanySwitcher.php                  # Sidebar'da alt firma seçici
├── Models/                                  # Company, Product, Batch, Item, Scan, Category, Post, Page, Partner, Setting, User
├── Policies/RolePolicy.php                  # Role bazlı policy
├── Providers/
│   ├── AppServiceProvider.php
│   └── Filament/AdminPanelProvider.php
└── Traits/HasTranslations.php               # Çoklu dil trait'i

config/
├── languages.php                            # tr/en dil yapılandırması
└── permission.php                           # Spatie Permission ayarları

database/
├── migrations/                              # 20 migration
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
- `company_id` (FK), `category_id` (FK), `name`, `description`, `image`
- `type`: physical | digital
- `digital_content_type`: pdf | youtube | file
- `digital_file_path`, `expiration_date`, `serial_number`
- `qr_mode`: unique | general
- `general_qr_code`: nullable, sadece `qr_mode=general` iken dolu
- `redirect_url`: nullable, sadece `qr_mode=unique` iken dolu — QR okutulduğunda tarama kaydı düşüldükten sonra kullanıcı bu URL'e (en fazla 2 sn içinde) yönlendirilir
- Sosyal medya: `whatsapp`, `instagram`, `facebook`, `twitter`, `youtube`, `website`, `email`
- Validasyon: category_id aynı company'e ait olmalı

### `batches` — Partiler (Item'ları toplu üretir)
- `product_id`, `batch_number`, `quantity`
- `created` event → `quantity` adet Item (UUID) otomatik üretir (1000'er chunk)

### `items` — Tekil ürün örnekleri
- `batch_id`, `uuid`, `is_scanned`

### `scans` — QR okutma kayıtları
- `item_id`, `ip_address`, `lat`, `lng`, `browser`, `device_type`, `scanned_at`

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
6. Ürün detayları + dijital içerik (PDF/video) + sosyal medya bağlantıları görüntülenir
   - `qr_mode=unique` ve `redirect_url` dolu ürünlerde: tarama kaydı sonrası kullanıcı (en fazla 2 sn bekleterek) `redirect_url`'e yönlendirilir

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
| GET | `/check/{uuid}` | QR sorgulama (Livewire) |
| GET | `/blog/{slug}` | Blog detay |
| GET | `/p/{slug}` | Statik sayfa |
| GET | `/iletisim` | İletişim |
| GET | `/sitemap.xml` | XML site haritası |
| GET | `/locale/{lang}` | Dil değiştirme |

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
