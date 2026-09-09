<?php

namespace App\Documents\Templates;

use App\Documents\DocumentTemplate;
use App\Documents\Field;
use App\Documents\Money;

/**
 * Shared shape for the priced document types — invoice, quote, and estimate.
 *
 * They all name two parties, list quantity-by-unit-price rows, and carry an
 * optional tax rate, so only their wording and their second date differ.
 */
abstract class BillingDocumentTemplate extends DocumentTemplate
{
    abstract protected function numberPlaceholder(): string;

    /** Field name of the date that follows the issue date. */
    abstract protected function secondaryDateName(): string;

    abstract protected function secondaryDateLabel(): string;

    abstract protected function recipientLabel(): string;

    protected function numberLabel(): string
    {
        return $this->name().' number';
    }

    protected function notesLabel(): string
    {
        return 'Notes';
    }

    protected function notesPlaceholder(): string
    {
        return 'Payment terms, bank details, thank-you note.';
    }

    /** Invoice, quote and estimate all render through one view. */
    public function view(): string
    {
        return 'pdf.billing';
    }

    public function fields(): array
    {
        return [
            Field::text('number', $this->numberLabel())->required()->placeholder($this->numberPlaceholder()),
            Field::date('issue_date', 'Issue date')->required(),
            Field::date($this->secondaryDateName(), $this->secondaryDateLabel())->required(),
            Field::select('currency', 'Currency', Money::currencies())->required()->default('USD'),
            Field::group('from', 'From', [
                Field::text('name', 'Business name')->required(),
                Field::email('email', 'Email'),
                Field::text('phone', 'Phone'),
                Field::textarea('address', 'Address'),
            ]),
            Field::group('to', $this->recipientLabel(), [
                Field::text('name', 'Name')->required(),
                Field::email('email', 'Email'),
                Field::textarea('address', 'Address'),
            ]),
            Field::repeater('items', 'Line items', [
                Field::text('description', 'Description')->required(),
                Field::number('quantity', 'Qty')->required()->default(1),
                Field::money('unit_price', 'Unit price')->required(),
            ])->required()->rows(1, 100),
            Field::number('tax_rate', 'Tax rate (%)')->help('Leave blank for no tax.'),
            Field::money('discount', 'Discount')->help('Flat amount taken off the subtotal.'),
            Field::textarea('notes', $this->notesLabel())->placeholder($this->notesPlaceholder()),
        ];
    }

    public function title(array $data): string
    {
        return trim($this->name().' '.($data['number'] ?? ''));
    }

    public function prepare(array $data): array
    {
        $currency = $data['currency'] ?? 'USD';
        $subtotal = 0;
        $items = [];

        foreach ($data['items'] ?? [] as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitCents = Money::toCents($item['unit_price'] ?? 0);
            $lineCents = (int) round($unitCents * $quantity);
            $subtotal += $lineCents;

            $items[] = $item + [
                'unit_price_formatted' => Money::format($unitCents, $currency),
                'line_total' => Money::format($lineCents, $currency),
            ];
        }

        $discountCents = min(Money::toCents($data['discount'] ?? 0), $subtotal);
        $taxable = $subtotal - $discountCents;
        $taxRate = (float) ($data['tax_rate'] ?? 0);
        $taxCents = (int) round($taxable * $taxRate / 100);

        return array_merge($data, [
            'items' => $items,
            'subtotal' => Money::format($subtotal, $currency),
            'has_discount' => $discountCents > 0,
            'discount_formatted' => Money::format($discountCents, $currency),
            'tax_rate' => $taxRate,
            'has_tax' => $taxRate > 0,
            'tax' => Money::format($taxCents, $currency),
            'total' => Money::format($taxable + $taxCents, $currency),
            'secondary_date_label' => $this->secondaryDateLabel(),
            'secondary_date' => $data[$this->secondaryDateName()] ?? null,
            'recipient_label' => $this->recipientLabel(),
        ]);
    }

    /**
     * Sample parties and rows reused by every billing type.
     *
     * @return array<string, mixed>
     */
    protected function sampleBase(): array
    {
        return [
            'currency' => 'USD',
            'from' => [
                'name' => 'Aneta Studio',
                'email' => 'billing@anetastudio.example',
                'phone' => '+1 (312) 555-0143',
                'address' => "456 Market St\nChicago, IL 60601\nUnited States",
            ],
            'to' => [
                'name' => 'Bright Ideas Co.',
                'email' => 'accounts@brightideas.example',
                'address' => "123 Business Ave\nNew York, NY 10001\nUnited States",
            ],
            'items' => [
                ['description' => 'Website design', 'quantity' => 1, 'unit_price' => 1200],
                ['description' => 'SEO setup', 'quantity' => 1, 'unit_price' => 300],
            ],
            'tax_rate' => '',
            'discount' => '',
        ];
    }
}
