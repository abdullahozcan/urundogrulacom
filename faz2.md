# Faz 2 Backlog - Multi Tenant QR Platform

Bu dokuman Faz 1 sonrası (aksiyon motoru, raporlar, detay UX, anket, moderasyon,
genel QR sertleştirme) teknik backlog'unu tanımlar.

Faz 1 durumuna bakış: `faz1.md` (tamamlandı). Proje özeti: `AGENT.md`.

---

## 1. Faz 2 Hedefi

1. **Aksiyon motoru:** Tarih ve okutma kurallarına göre otomatik aksiyon yürütme —
   **mevcut tarama/doğrulama akışını bozmadan**.
2. Gelişmiş tarama raporları ve CSV/Excel export.
3. Ürün detay ekranını çok sekmeli UX'e taşıma.
4. Anket (survey) modülü altyapısı.
5. Değerlendirme moderasyonu için ayrı yönetim ekranı.
6. Genel QR çözümleme akışını sertleştirme.

## 2. Kapsam

### Dahil

1. `action_rules` + `action_logs` migrationları ve modeller.
2. `ScanRecorded` event + fail-safe listener mimarisi.
3. Filament `ProductActionResource` (tenant izolasyonlu).
4. Scan filtreleri + CSV export action.
5. Ürün detay ViewPage sekmeleri (Özet / Taramalar / Partiler / Belgeler / Değerlendirmeler).
6. Review moderasyon ekranı (ayrı resource, bulk onay/red).
7. Feature + unit testler.

### Dahil Değil (Faz 3+)

1. Gelişmiş anket analizleri ve doğal dil cevapları.
2. Üçüncü parti BI entegrasyonları (Metabase, Power BI).
3. Otomatik kampanya şablonları / A/B testi.
4. Mobil uygulama / push bildirim.

---

## 3. Aksiyon Motoru (Çekirdek) — Sistem Bozulmayacak Tasarım

### 3.1 Tasarım İlkeleri (Non-Breaking Prensipler)

1. **Tamamen ekleyici:** Sadece yeni tablolar, yeni model/service/listener. Mevcut
   `products`, `scans`, `items` tablolarına hiçbir kolon eklenmez/değişmez.
2. **Feature flag:** `Settings` tablosunda `action_engine_enabled` (default `false`).
   Kapalıyken motor tamamen no-op — hiçbir ek sorgu, hiçbir davranış değişikliği yok.
3. **Fail-safe:** Aksiyon değerlendirme zinciri tek noktadan `try/catch (Throwable)` ile
   sarılır. Motor ne patlatırsa patlasın: scan kaydı düşmüş, kullanıcı doğrulama
   sonucunu görmüş olur. Hata sadece `action_logs.result = failed` + `error_message`
   olarak kaydedilir.
4. **Event-driven bağlantı:** `CheckProduct` doğrudan motoru çağırMAZ. Scan kaydı
   düştükten sonra `ScanRecorded` event'i fırlatılır (`Scan` modelinde `created`
   event). Listener dosyada en sonda çalışır; doğrulama render'ını bloklamaz
   (ağır aksiyonlar kuyruğa atılır).
5. **Zaman bütçesi:** Listener senkron bölümünde sorgular indexed + sınırlı (rule set
   cache'lenir); webhook/mail gibi dışa açılan işler `queue:listen` altyapısına
   (mevcut jobs tablosu) job olarak devreder.

### 3.2 Veri Modeli

#### `action_rules` tablosu

| Kolon | Tip / Not |
|---|---|
| `id` | PK |
| `company_id` | FK companies, index — tenant izolasyonu |
| `product_id` | nullable FK products — null ise firma geneli |
| `name` | string |
| `trigger_type` | enum: `scan_count` \| `first_scan` \| `date_range` \| `item_revisit` |
| `trigger_config` | JSON — tetikleyici parametreleri (aşağıda) |
| `action_type` | enum: `show_content` \| `redirect` \| `webhook` \| `notify_admin` |
| `action_payload` | JSON — aksiyon parametreleri (aşağıda) |
| `is_active` | bool, default true |
| `priority` | int, default 0 — küçük önce çalışır |
| `starts_at` / `ends_at` | nullable — kuralın geçerlilik penceresi |
| `max_executions` | nullable int — toplam çalışma limiti |
| `times_executed` | int, default 0 (denormalize sayaç, `action_logs` ile tutarlı) |
| timestamps | — |

`trigger_config` şemaları:
- `scan_count`: `{ "count": 5, "scope": "item|product|company" }` → N. tarama
- `first_scan`: `{}` → item'ın ilk taraması (`items.is_scanned` geçişi)
- `date_range`: `{}` → `starts_at/ends_at` penceresi her taramada tetiklenir
- `item_revisit`: `{ "after_days": 7 }` → aynı item X günden fazla sonra yeniden okutulursa

`action_payload` şemaları:
- `show_content`: `{ "title": {...tr/en}, "body": {...tr/en}, "style": "info|warning" }`
  → check sayfasında gösterilir (HTML değil; `strip_tags` + mevcut sanitization)
- `redirect`: `{ "url": "https://..." }` → tarama sonrası yönlendirme
  (mevcut `products.redirect_url` davranışı aynen korunur; kural varsa ve ürünün
  kendi redirect'i yoksa kural çalışır, ikisi birden asla tetiklenmez)
- `webhook`: `{ "url": "https://...", "secret": "..." }` → imzalı POST (queue)
- `notify_admin`: `{ "email": "..." }` → firma adminine e-posta (queue)

#### `action_logs` tablosu

| Kolon | Tip / Not |
|---|---|
| `id` | PK |
| `action_rule_id` | FK action_rules, cascade |
| `scan_id` | nullable FK scans — dedup anahtarı |
| `item_id` | nullable FK items |
| `result` | enum: `success` \| `failed` \| `skipped` |
| `error_message` | nullable text |
| timestamps | — |

Dedup kuralı: `(action_rule_id, scan_id)` unique — aynı tarama iki kez değerlendirilemez.

### 3.3 Kod Yapısı

```
app/
├── Events/ScanRecorded.php
├── Listeners/EvaluateActionRules.php     # try/catch sarmalı, feature flag kontrolü
├── Services/ActionEngine/
│   ├── ActionEngine.php                  # evaluate(Scan): kural eşleme + yürütme
│   ├── Triggers/                          # ITrigger contract + 4 trigger sınıfı
│   │   ├── ScanCountTrigger.php
│   │   ├── FirstScanTrigger.php
│   │   ├── DateRangeTrigger.php
│   │   └── ItemRevisitTrigger.php
│   └── Actions/                           # IAction contract + 4 action sınıfı
│       ├── ShowContentAction.php          # CheckProduct'a public alan setler
│       ├── RedirectAction.php
│       ├── WebhookAction.php              # job dispatch
│       └── NotifyAdminAction.php          # job dispatch
├── Jobs/ExecuteWebhookAction.php          # tries=3, backoff, timeout
└── Filament/Resources/ActionRules/        # CRUD + tenant scope
```

### 3.4 Akış (Mevcut Akışa Etki Sıfır Noktaları)

1. Tüketici `/check/{uuid}` açar → mevcut akış aynen çalışır.
2. `Scan` kaydı oluşur → `ScanRecorded` event.
3. Listener: flag kapalıysa → return (0 maliyet). Açıksa → `ActionEngine::evaluate()`.
4. Engine: product'ın aktif kurallarını (priority sıralı, cache'li) değerlendirir,
   eşleşenler için `action_logs` yazar, `max_executions` kontrol eder, aksiyon yürütür.
5. `show_content` / `redirect` sonuçları CheckProduct üzerinde zaten var olan
   `$redirectUrl`/yeni `$actionContent` property'lerine aktarılır — render tarafı
   mevcut layout'a küçük bir koşullu blok eklenerek gösterilir.
6. Herhangi bir adımda hata → log + `failed` → kullanıcı akışı etkilenmez.

### 3.5 Yol Haritası (Uygulama Sırası)

1. Migration: `create_action_rules_table` + `create_action_logs_table` — YAPILDI
2. Model: `ActionRule`, `ActionLog` (+ tenant helper'lar) — YAPILDI
3. `ScanRecorded` event + `EvaluateActionRules` listener (flag kapalı no-op) — YAPILDI
4. `ActionEngine` + Trigger sınıfları + `ActionLogs` dedup — YAPILDI
5. Action sınıfları: `ShowContent` → `NotifyAdmin` → `Redirect` → `Webhook` (risk sırasıyla) — YAPILDI
6. Filament: `ActionRuleResource` (tenant izolasyonlu, öncelik + limit alanları) — YAPILDI (flag toggle dahil)
7. CheckProduct render entegrasyonu (koşullu blok, sanitize) — YAPILDI
8. Testler (aşağıda) + smoke test, flag kapalıyken regresyon — YAPILDI (8 test dosyası, 46/46 yeşil)
9. Pilot: tek demo firmada flag açma — BEKLİYOR (manuel adım: ListActionRules → "Aksiyon Motorunu Aç")

### 3.6 Aksiyon Motoru Kabul Kriterleri

1. Flag kapalıyken: mevcut tüm testler yeşil, `/check/{uuid}` davranışı bit olarak aynı.
2. Motor içinde bilinmeyen hata → HTTP 200 + doğrulama sonucu gösterilmeye devam eder.
3. Aynı scan ID için kural asla iki kez çalışmaz (dedup).
4. `max_executions` aşımında kural atlanır (`skipped`).
5. Webhook job 3 denemeden sonra başarısız olursa akış etkilenmez.
6. Kural CRUD tenant izole (firma A, firmanın B kuralını göremez/düzenleyemez).
7. Aksiyon motoru hataları genel Laravel log'a düşer ama kullanıcıya sızmaz.

---

## 4. Gelişmiş Raporlar ve Export — YAPILDI

1. Scan listesi filtreleri: tarih aralığı, cihaz tipi, ürün, parti, kategori — YAPILDI
2. ListScans header CSV export action (`getFilteredTableQuery` ile filtreleri uygular, chunk 1000, BOM'lu UTF-8) — YAPILDI
3. Dashboard `CategoryScansChart` (son 30 gün kategori kırılımı, bar) — YAPILDI
4. Export kendi firmasının verisiyle sınırlı (resource scope + filtre scope) — YAPILDI

## 5. Çok Sekmeli Ürün Detay UX — YAPILDI

1. `ViewProduct` sayfası sekmeler: Özet (infolist) / Partiler / Taramalar / Belgeler / Değerlendirmeler — YAPILDI
2. Yeni relation manager'lar: `ScansRelationManager` (custom Builder relationship), `DocumentsRelationManager` — YAPILDI
3. `ReviewsRelationManager` salt-okunur listeye çekildi — YAPILDI
4. Sekme state'i URL'de korunur (Filament `?relation=` parametresi) — YAPILDI

## 6. Anket (Survey) Modülü — YAPILDI

1. Tablolar: `surveys`, `survey_questions` (tek seçim/çok seçim/metin), `survey_responses` (question+scan unique) — YAPILDI
2. Gösterim kuralı: aksiyon motoru `show_survey` aksiyon tipiyle (enum genişletildi) — YAPILDI
3. CheckProduct: anket formu render + `submitSurvey` (zorunlu soru validasyonu, strip_tags, 1000 char) — YAPILDI
4. Admin: `SurveyResource` (soru repeater'lı CRUD, tenant izole) — YAPILDI
5. Cevaplar anonim, scan ile ilişkili (PII yok) — YAPILDI

## 7. Değerlendirme Moderasyon Ekranı — YAPILDI

1. `ReviewResource` (bağımsız resource, tenant izole, bekleyen sayacı navigation badge) — YAPILDI
2. Bulk onay/red action + tekil onay toggle — YAPILDI
3. Onaylı değerlendirme e-posta özeti — YAPILMADI (opsiyonel, Faz 3'e bırakıldı)
4. Product detayındaki ReviewsRelationManager salt-okunur — YAPILDI

## 8. Genel QR Sertleştirme — YAPILDI

1. `general_qr_code` DB unique index (multiple NULL serbest) — YAPILDI
2. Redirect'siz genel QR davranışı 404 olarak test ile sabitlendi — YAPILDI
3. Form validasyonu: `unique(ignoreRecord)` + item UUID çakışma kontrolü (mevcut) — YAPILDI
4. Faz 1'de "KISMEN YAPILDI" işaretli madde kapatıldı — YAPILDI

---

## 9. Test Backlog'u (Faz 2)

1. `ActionRuleTriggerTest` — 4 tetikleyici tipinin doğru değerlendirilmesi.
2. `ActionEngineFailSafeTest` — aksiyon hata fırlatsa bile check akışı 200 döner.
3. `ActionEngineDedupTest` — aynı scan için çift yürütme engellenir.
4. `ActionRuleTenantIsolationTest` — kural CRUD izolasyonu.
5. `ScanExportTest` — export sadece kendi firmasının verisini verir.
6. `GeneralQrResolutionTest` — redirect'li/redirect'siz genel QR davranışı.
7. Mevcut 6 feature test dokunulmadan yeşil kalır (regresyon).

## 10. Definition of Done (Faz 2)

1. `action_rules`/`action_logs` migrationları çalışıyor, modeller tenant izole. — ✅
2. Feature flag kapalıyken davranış farkı sıfır (regresyon testi ile kanıt). — ✅
3. Aksiyon motoru 4 tetikleyici + 5 aksiyon tipiyle çalışıyor. — ✅
4. Rapor filtreleri + CSV export tenant izole. — ✅
5. Ürün detay sekmeleri eski fonksiyonaliteyi eksiksiz taşıyor. — ✅
6. Anket modülü motor tetikleyicisiyle gösterilebiliyor. — ✅
7. Moderasyon ekranı bulk onay/red yapıyor. — ✅
8. Genel QR akışı test ile sabitlenmiş. — ✅
9. Kritik Faz 2 testleri yeşil, AGENT.md güncellenmiş. — ✅ (46/46 test)

## 11. Manuel Kontrol Listesi (Uygulama Sonu)

1. Flag kapalı → `/check/{uuid}` eskisi gibi; admin panelde kural ekranı gizli.
2. Flag açık → demo firmada `scan_count` kuralı: 3. taramada mesaj görünüyor.
3. Aynı item tekrar okutuluyor → kural tekrar tetiklenmiyor (dedup log).
4. Geçersiz webhook URL'li kural → kullanıcı yine doğrulamayı görüyor.
5. Firma A kuralı, Firma B panelinde görünmüyor.
6. CSV export sadece kendi firmasının taramalarını içeriyor.
7. Review bulk onay sonrası check sayfasında görünmüyor (is_approved filtresi doğruysa görünmeli — davranış netleştirilir).
