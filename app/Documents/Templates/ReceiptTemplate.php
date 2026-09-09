<?php

namespace App\Documents\Templates;

use App\Documents\DocumentTemplate;
use App\Documents\Field;
use App\Documents\Money;

class ReceiptTemplate extends DocumentTemplate
{
    protected const METHODS = [
        'card' => 'Card',
        'cash' => 'Cash',
        'bank_transfer' => 'Bank transfer',
        'cheque' => 'Cheque',
        'other' => 'Other',
    ];

    public function key(): string
    {
        return 'receipt';
    }

    public function name(): string
    {
        return 'Receipt';
    }

    public function description(): string
    {
        return 'Confirm a payment you have already been given.';
    }

    public function icon(): string
    {
        return 'receipt';
    }

    public function accent(): string
    {
        return 'purple';
    }

    public function keywords(): array
    {
        return ['paid', 'payment', 'proof of payment'];
    }

    public function fields(): array
    {
        return [
            Field::text('number', 'Receipt number')->required()->placeholder('RCP-1001'),
            Field::date('paid_on', 'Payment date')->required(),
            Field::select('currency', 'Currency', Money::currencies())->required()->default('USD'),
            Field::group('from', 'Received by', [
                Field::text('name', 'Business name')->required(),
                Field::email('email', 'Email'),
                Field::text('phone', 'Phone'),
                Field::textarea('address', 'Address'),
            ]),
            Field::text('payer', 'Received from')->required(),
            Field::repeater('items', 'What was paid for', [
                Field::text('description', 'Description')->required(),
                Field::money('amount', 'Amount')->required(),
            ])->required()->rows(1, 100),
            Field::select('method', 'Payment method', self::METHODS)->required()->default('card'),
            Field::text('reference', 'Reference')->placeholder('Transaction or cheque number'),
            Field::textarea('notes', 'Notes'),
        ];
    }

    public function title(array $data): string
    {
        return trim('Receipt '.($data['number'] ?? ''));
    }

    public function prepare(array $data): array
    {
        $currency = $data['currency'] ?? 'USD';
        $total = 0;
        $items = [];

        foreach ($data['items'] ?? [] as $item) {
            $cents = Money::toCents($item['amount'] ?? 0);
            $total += $cents;
            $items[] = $item + ['amount_formatted' => Money::format($cents, $currency)];
        }

        return array_merge($data, [
            'items' => $items,
            'total' => Money::format($total, $currency),
            'method_label' => self::METHODS[$data['method'] ?? ''] ?? 'Other',
        ]);
    }

    public function sample(): array
    {
        return [
            'number' => 'RCP-1001',
            'paid_on' => now()->toDateString(),
            'currency' => 'USD',
            'from' => [
                'name' => 'Aneta Studio',
                'email' => 'billing@anetastudio.example',
                'phone' => '+1 (312) 555-0143',
                'address' => "456 Market St\nChicago, IL 60601\nUnited States",
            ],
            'payer' => 'Bright Ideas Co.',
            'items' => [
                ['description' => 'Invoice INV-1001 — paid in full', 'amount' => 1500],
            ],
            'method' => 'bank_transfer',
            'reference' => 'FT26091234567',
            'notes' => 'Thank you for your payment.',
        ];
    }
}
