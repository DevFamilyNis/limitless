# Limitless — Tehnička dokumentacija arhitekture

## Pregled

Ovo je single-company poslovna aplikacija za upravljanje klijentima, fakturama, projektima, leadovima i internim zadacima (issues).

## Važna poslovna pravila

### 1. Official Document Signer (Potpisnik dokumenata)

**Pravilo:** Potpisnik zvaničnih dokumenata (fakture, KPO izveštaji) nije automatski ulogovani korisnik.

Aplikacija ima konfigurabilnog "official signer"-a koji se čuva u `app_settings` tabeli sa ključem `official_signer_user_id`.

**Zašto:** U pravnom kontekstu, na dokumentima mora biti ime odgovornog lica (vlasnik firme), bez obzira ko je ulogovan u sistem.

**Implementacija:** `App\Models\AppSetting::officialSignerUserId()`, podešen kroz `AdminUserSeeder`.

**Gde se koristi:**
- `app/Livewire/Invoices/Form.php` → `getDocumentSignerUserId()`
- `app/Livewire/Invoices/Index.php` → `getDocumentSignerUserId()`
- `app/Livewire/KpoReports/Index.php` → `getDocumentSignerUserId()`

**Greška ako nije podešen:** HTTP 503 sa porukom `messages.errors.official_signer_not_configured`.

---

### 2. KPO Period — `created_at`, ne `issue_date`

**Pravilo:** KPO izveštaj za određeni mesec uključuje fakture koje su **kreirane** u tom mesecu (po `created_at`), ne fakture čiji je period usluge u tom mesecu.

**Zašto:** KPO je knjiga prihoda po datumu knjiženja, ne po datumu usluge.

**Primer:** Faktura za januar (issue_date 01.01-31.01) ali kreirana 05.02 → ulazi u FEBRUARSKI KPO, ne januarski.

**Implementacija:** `app/Domain/Kpo/Actions/GenerateMonthlyKpoReportAction.php` — `whereBetween('created_at', [startOfDay, endOfDay])`.

**Testovi:** `tests/Feature/Domain/Kpo/KpoReportPeriodTest.php`

---

### 3. Dashboard — Company-wide metrike

**Pravilo:** Dashboard prikazuje ukupne metrike cele firme, nije scopeovan po user_id.

**Zašto:** Više zaposlenih koristi istu aplikaciju ali svi vide iste finansijske metrike firme.

**Implementacija:** `app/Domain/Dashboard/Queries/DashboardMetricsQuery.php` — bez filtera po `user_id` za transakcije i fakture.

**Testovi:** `tests/Feature/Domain/Dashboard/CompanyDashboardMetricsTest.php`

---

### 4. Roles i Permissions

**Sistem:** `spatie/laravel-permission`

**Role:**
- `super-admin` — bypasses sve permission provere (via `Gate::before` u `AppServiceProvider`)
- `user` — standardni pristup svim poslovnim funkcijama osim `manage-users`, `manage-roles`, `manage-kpo`

**Super-admin:** Igor Mitrinovic — konfigurisan kroz `AdminUserSeeder`

**Permissions Enum:** `App\Enums\PermissionKey`

**Route zaštita:** Admin rute zahtevaju `manage-users` permission: `/admin/users`

---

### 5. DB::transaction pravila

Sve akcije koje menjaju više tabela moraju biti u transakciji:

| Akcija | Razlog |
|--------|--------|
| `UpsertClientAction` | clients + client_companies/person + contacts + app_links |
| `UpsertInvoiceAction` | invoices + invoice_items |
| `MarkInvoicePaidAction` | invoices (status) + transactions |
| `GenerateMonthlyKpoReportAction` | kpo_reports + kpo_report_rows |
| `AddLeadCommentAction` | lead_comments + leads (tracking fields) |
| `LockKpoReportAction` | kpo_reports (locked_at) |

`UpsertClientAction` koristi `lockForUpdate()` na existing client da spreči konkurentnu izmenu.

---

### 6. Shared Workspace Authorization

Aplikacija je **single-company, shared workspace** model:
- Svi autentifikovani korisnici vide SVE podatke firme
- Brisanje/izmena je kontrolisana permissions sistemom, ne ownership-om po user_id
- `user_id` na modelima označava KO JE KREIRAO zapis, ne KO JE VLASNIK za ACL svrhe

---

## Enum-i

- `App\Enums\AppSettingKey` — ključevi za app_settings tabelu
- `App\Enums\InvoiceStatusKey` — draft, sent, paid, canceled
- `App\Enums\IssueStatusKey` — backlog, todo, doing, done
- `App\Enums\IssuePriorityKey` — low, medium, high, urgent
- `App\Enums\RoleKey` — super-admin, user
- `App\Enums\PermissionKey` — sve permission vrednosti

## Struktura Domain layer-a

```
Domain/{ModuleName}/
├── Actions/    # Poslovna logika (execute metoda, prima DTO, vraća Model)
├── DTO/        # Data Transfer Objects (fromArray factory, readonly properties)
├── Queries/    # Read-only upiti (execute metoda, vraća Collection/Model)
└── Exceptions/ # Domain-specific exceptions
```

## Livewire → Domain flow

```
Livewire::method()
  → $this->validate()          (inline rules() ili Form Request)
  → DTO::fromArray([...])       (castovanje validiranih podataka)
  → Action::execute(DTO)        (poslovna logika + DB::transaction)
  → redirect / session flash
```
