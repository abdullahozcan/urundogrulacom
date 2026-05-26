# Faz 1 Backlog - Multi Tenant QR Platform

Bu dokuman, mevcut Laravel + Filament yapisina gore Faz 1 icin teknik backlog'u tanimlar.
Odak: veri modeli temeli + tenant guvenligi + kategori + QR modlari + temel yonetim ekranlari.

## 1. Faz 1 Hedefi

1. Kuruluslar arasi veri izolasyonunu merkezi hale getirmek.
2. Urunleri kategori ve alt kategori yapisina almak.
3. Urunde iki QR takip modunu desteklemek:
   - `unique` (tekil takip)
   - `general` (urun bazli toplu takip)
4. "Yeni QR ile urun" ve "Var olan QR ile urun" akislarinin altyapisini hazirlamak.
5. Super admin ve kurulus kullanicisi panel davranislarini netlestirmek. => superadmin tüm kullanıcıların verilerine ulaşabilecek görebilecek, kuruluşun admin dışında yetki yapısı kurulacak. kuruluşların gördüğü menüler ve işlemlerle ilgili yeni kullanıcı tip ve rolü eklenebilir.yetkiler atanabilir olacak. kuruluşa özel olacak yine.

## 2. Kapsam

### Dahil

1. Migrationlar ve temel modeller.
2. Filament Resource guncellemeleri (minimum gerekli CRUD).
3. Tenant scope ve role bazli gorunurluk kontrolu.
4. Ilk seviye test senaryolari (feature + authorization).

### Dahil Degil (Faz 2+)

1. Gelismis aksiyon motoru (tarih/okutma kurallari).
2. Anket/puanlama/yorum.
3. Gelismis davranis raporlari ve export.
4. Cok sekmeli detay UX'in tumu.

## 3. Veri Modeli Backlog'u

## 3.1 `categories` tablosu

Durum: YAPILDI

1. Alanlar:
   - `id`
   - `company_id` (FK companies, index)
   - `parent_id` (nullable self FK, index)
   - `name`
   - `slug`
   - `description` (nullable)
   - `is_active` (bool, default true)
   - timestamps
2. Kurallar:
   - Her kategori bir kurulus'a ait olacak.
   - Alt kategori sadece ayni kurulusun ust kategorisine baglanabilecek.
3. Index:
   - `(company_id, parent_id)`
   - `(company_id, slug)` unique

## 3.2 `products` tablosu genisletmesi

Durum: YAPILDI

1. Eklenecek alanlar:
   - `category_id` (nullable FK categories)
   - `qr_mode` enum: `unique|general` (default `unique`)
   - `general_qr_code` (nullable, indexed) - mevcut QR baglama icin
2. Kurallar:
   - `category_id` secildiyse ayni `company_id` altinda olmali.
   - `general_qr_code` sadece `qr_mode=general` iken dolu olmali.

## 3.3 Opsiyonel ayrik tablo: `product_qr_codes` (onerilen)

Not: Eger tek urunde birden fazla genel QR desteklenecekse ayrik tabloya gecilecek.

1. Alanlar:
   - `id`
   - `company_id`
   - `product_id`
   - `code_value` (indexed)
   - `code_type` enum: `unique|general`
   - `is_active`
   - timestamps
2. Faz 1 karari:
   - Tek genel QR yeterliyse `products.general_qr_code` ile basla.
   - Coklu QR ihtiyaci kesin ise dogrudan bu tabloyu ac.

## 4. Model ve Iliski Gorevleri

1. `Category` modeli olustur: YAPILDI
   - `belongsTo Company`
   - `belongsTo Parent(Category)`
   - `hasMany Children(Category)`
   - `hasMany Products`
2. `Company` modeline: YAPILDI
   - `hasMany categories`
3. `Product` modeline: YAPILDI
   - `belongsTo category`
   - `qr_mode` cast/dogrulama
4. Servis seviyesi validator: KISMEN YAPILDI
   - cross-tenant category bagini engelle.

## 5. Filament Backlog'u

## 5.1 CategoryResource

Durum: YAPILDI

1. Liste:
   - `name`, `parent`, `is_active`, `created_at`
2. Form:
   - `name`, `slug`, `parent_id`, `description`, `is_active`
3. Tenant:
   - super admin tumunu gorur
   - company kullanicisi sadece kendi `company_id`

## 5.2 ProductResource guncelleme

Durum: YAPILDI

1. Forma ekle:
   - `category_id` (tenant filtreli)
   - `qr_mode`
   - `general_qr_code` (sadece `qr_mode=general` ise gorunur/zorunlu)
2. Liste:
   - kategori kolonu
   - qr_mode badge
3. Validation:
   - `general_qr_code` formati (min/max + benzersizlik kurali)

## 5.3 Navigasyon/Paneller

1. Super admin:
   - Kurulus yonetimi
   - Kullanici yonetimi
   - Tum tenant KPI
2. Kurulus kullanicisi:
   - Dashboard
   - Kategoriler
   - Urunler
   - Raporlar
3. Sistem sekmesi:
   - Sadece super admin

## 6. QR Akis Backlog'u (Faz 1 seviyesinde)

1. Yeni QR ile urun: YAPILDI
   - `qr_mode=unique` secildiginde mevcut item/batch akisiyla uyumlu kalsin.
2. Var olan QR ile urun: KISMEN YAPILDI
   - `qr_mode=general`
   - kullanici QR degerini girsin (`general_qr_code`)
   - tekil item uretmeden urun kaydi acilsin.
3. Public check endpoint hazirligi: HENUZ YAPILMADI
   - mevcut `/check/{uuid}` yanina genel QR cozumleme stratejisi backlog'a eklenir.
   - Faz 1'de endpoint degisikligi opsiyonel; asgari seviyede model/yonetim hazirlanir.

## 7. Guvenlik ve Tenant Kabul Kriterleri

1. Kurulus kullanicisi:
   - baska kurulus kategorisini goremez/secemez.
   - baska kurulus urununu goremez/duzenleyemez.
2. Super admin:
   - tum kurulus verilerini gorebilir.
3. URL ile dogrudan erisim:
   - yetkisiz kayitlara 403/404.

## 8. Test Backlog'u

1. `CategoryTenantIsolationTest`
   - create/list/edit izolasyon.
2. `ProductQrModeTest`
   - `unique` ve `general` form dogrulamasi.
3. `CrossTenantAssignmentTest`
   - farkli kurulus category_id atama denemesi reddedilir.
4. `NavigationVisibilityTest`
   - super admin vs company user menuler.

## 9. Teknik Gorev Listesi (Uygulama Sirasi)

1. Migration: `create_categories_table` - YAPILDI
2. Migration: `alter_products_add_category_and_qr_mode` - YAPILDI
3. Model: `Category` - YAPILDI
4. Model update: `Product`, `Company` - YAPILDI
5. Filament Resource: `CategoryResource` - YAPILDI
6. Product form/table update - YAPILDI
7. Authorization policy/scope sertlestirme - KISMEN YAPILDI
8. Seeder guncelleme (ornek kategori + rol) - KISMEN YAPILDI
9. Feature testler - HENUZ YAPILMADI
10. Smoke test + manuel panel kontrol - HENUZ YAPILMADI

## 10. Definition of Done (Faz 1)

1. Migrationlar calisiyor.
2. Kategori CRUD tenant izole.
3. Urunler kategoriye baglanabiliyor.
4. `qr_mode` secimi ve `general_qr_code` dogrulamasi aktif.
5. Super admin / company user gorunurluk farklari dogru.
6. Kritik tenant testleri yesil.
