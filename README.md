# Aneta Prime — PDF Generator

Create professional PDFs in seconds. Describe what you need in a sentence, or
pick a document type, edit it beside a live preview, and download.

Built on **Laravel 13**, **Inertia 3** and **React 19**, with **Dompdf** doing
the rendering.

## The idea

Making a document requires no account and stores nothing. A guest's PDF is
built in memory during the request and streamed straight back — no row is
written, no file touches disk. Signing in adds convenience on top: somewhere to
keep finished documents, your business details filled in for you, and a list of
customers you bill often.

|                     | Signed out | Signed in |
| ------------------- | :--------: | :-------: |
| Create and download |     ✓      |     ✓     |
| All 8 templates     |     ✓      |     ✓     |
| Smart generation    |     ✓      |     ✓     |
| No limits           |     ✓      |     ✓     |
| Saved documents     |            |     ✓     |
| Business details    |            |     ✓     |
| Saved customers     |            |     ✓     |

## Document types

Invoice, Quote, Receipt, Estimate, Proposal, Contract, Business Letter and
Certificate. Invoices, quotes and estimates share a priced-lines shape with
currency, an optional tax rate and a flat discount; totals are summed in whole
cents so repeated addition cannot drift.

## The smart prompt

> Create an invoice for John Smith for $850 for logo design, due September 30

`RuleBasedParser` reads that with regular expressions — no language model and no
API call, so it answers instantly and the text never leaves the app. It picks
out the document type, who it is for, amounts and currency, quantities priced
per unit, and dates ("due September 30", "due in 14 days", "valid until
October 15"). A bare date that has already passed is read as next year. Whatever
it cannot work out is left blank for you, and the editor shows exactly what it
understood.

`DocumentParser` is an interface, bound in `AppServiceProvider`. Swapping in a
model-backed parser later means changing one binding.

## How it fits together

```
Prompt ──▶ RuleBasedParser ──▶ draft ──▶ Editor ──▶ DocumentValidator
                                              │              │
                                              ▼              ▼
                                        Live preview ◀── PdfGenerator ──▶ Download
```

Every document type is one class extending `DocumentTemplate`. It declares its
fields, its sample data, and any derived figures:

```php
Field::text('number', 'Invoice number')->required()->placeholder('INV-1001'),
Field::repeater('items', 'Line items', [
    Field::text('description', 'Description')->required(),
    Field::number('quantity', 'Qty')->required()->default(1),
    Field::money('unit_price', 'Unit price')->required(),
])->required()->rows(1, 100),
```

That single schema drives three things, so they cannot drift apart:

- the React form (`DocumentEditor` renders any field type, including nested
  groups and repeating rows),
- the Laravel validation rules, compiled by `Field::rules()`,
- the labels in validation messages, so an error reads "The unit price field is
  required" rather than naming `data.items.0.unit_price`.

Adding a document type means writing one template class, one Blade view, and
listing it in `TemplateManager`.

### Layout

```
app/
├── Documents/              Field schema, templates, money and formatting
│   └── Templates/          One class per document type
├── Services/
│   ├── Document/           Parser, validator, generator
│   ├── Pdf/                PdfGenerator — the only class that talks to Dompdf
│   └── Templates/          TemplateManager — the catalogue
└── Http/
    ├── Controllers/        Generator, Pdf, Document, Account, Auth
    └── Requests/           ParseDocumentRequest, GeneratePdfRequest

resources/
├── js/
│   ├── Pages/              Home, Generator/*, Documents/*, Auth/*, Account
│   ├── Components/         Generator/, Documents/, Pdf/, UI/
│   └── Layouts/AppLayout.jsx
└── views/pdf/              Blade templates rendered to PDF
```

## Running it

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

npm run build          # or: npm run dev
php artisan serve
```

Then open http://127.0.0.1:8000.

SQLite is the default and needs no setup; point `DB_CONNECTION` at MySQL or
Postgres if you would rather. Saved PDFs are written to the `local` disk under
`storage/app/private/documents`, out of the web root — they are only reachable
through the download route, and only by the account that saved them.

## Tests

```bash
./vendor/bin/phpunit     # 100 tests
./vendor/bin/pint        # code style
```

Coverage worth knowing about: every template's sample data is validated against
its own rules and rendered to a real PDF; the parser is checked against the
prompts on the home page; guest generation is asserted to write no database row;
and one account can never reach another's documents.

## Notes on a couple of decisions

**No build-time font fetch.** The UI uses a system font stack rather than a
web font, so `npm run build` needs no network and pages have no render-blocking
font request. Dompdf uses its bundled DejaVu Sans.

**PDF routes always answer failures with JSON.** They are called with `fetch`,
and a redirect would be followed and rendered as though it were the document —
which is exactly the bug that produces a blank preview. `bootstrap/app.php`
pins `pdf/*` to JSON responses.

**Inertia pages live in `resources/js/Pages`.** Inertia 3 looks in lowercase
`js/pages` by default; `config/inertia.php` points it at the capitalised
directory so `assertInertia()` still catches a controller rendering a component
nobody wrote.
