<?php

namespace App\Documents\Templates;

use App\Documents\DocumentTemplate;
use App\Documents\Field;

class ContractTemplate extends DocumentTemplate
{
    public function key(): string
    {
        return 'contract';
    }

    public function name(): string
    {
        return 'Contract';
    }

    public function description(): string
    {
        return 'Numbered clauses between two parties, with signature blocks.';
    }

    public function icon(): string
    {
        return 'pen-line';
    }

    public function accent(): string
    {
        return 'sky';
    }

    public function keywords(): array
    {
        return ['agreement', 'terms', 'nda'];
    }

    public function fields(): array
    {
        return [
            Field::text('title', 'Contract title')->required()->placeholder('Services Agreement'),
            Field::text('reference', 'Reference')->placeholder('AGR-2026-001'),
            Field::date('effective_date', 'Effective date')->required(),
            Field::group('party_one', 'First party', [
                Field::text('name', 'Name')->required(),
                Field::text('role', 'Referred to as')->default('the Provider'),
                Field::textarea('address', 'Address'),
            ]),
            Field::group('party_two', 'Second party', [
                Field::text('name', 'Name')->required(),
                Field::text('role', 'Referred to as')->default('the Client'),
                Field::textarea('address', 'Address'),
            ]),
            Field::textarea('preamble', 'Preamble')->maxLength(4000)
                ->help('Optional opening paragraph shown before the numbered clauses.'),
            Field::repeater('clauses', 'Clauses', [
                Field::text('heading', 'Heading')->required(),
                Field::textarea('body', 'Body')->required()->maxLength(20000),
            ])->required()->rows(1, 80),
            Field::text('governing_law', 'Governing law')->placeholder('the State of Illinois'),
        ];
    }

    public function title(array $data): string
    {
        return $data['title'] ?? 'Contract';
    }

    public function sample(): array
    {
        return [
            'title' => 'Services Agreement',
            'reference' => 'AGR-2026-001',
            'effective_date' => now()->toDateString(),
            'party_one' => [
                'name' => 'Aneta Studio LLC',
                'role' => 'the Provider',
                'address' => "456 Market St\nChicago, IL 60601",
            ],
            'party_two' => [
                'name' => 'Bright Ideas Co.',
                'role' => 'the Client',
                'address' => "123 Business Ave\nNew York, NY 10001",
            ],
            'preamble' => 'This agreement sets out the terms on which the Provider will supply design and development services to the Client.',
            'clauses' => [
                [
                    'heading' => 'Services',
                    'body' => 'The Provider will supply the services described in the accompanying proposal dated the same day as this agreement. Any change to that scope takes effect only when both parties have agreed it in writing.',
                ],
                [
                    'heading' => 'Fees and payment',
                    'body' => "The Client will pay the fees set out in the proposal. Invoices are due within 21 days of issue.\n\nAmounts unpaid after that date carry interest at 2% per month.",
                ],
                [
                    'heading' => 'Intellectual property',
                    'body' => 'On payment in full, the Provider assigns to the Client all rights in the deliverables, except for third-party components and the Provider\'s pre-existing tools, which are licensed to the Client on a perpetual, non-exclusive basis.',
                ],
                [
                    'heading' => 'Confidentiality',
                    'body' => 'Each party will keep the other\'s confidential information private, and will use it only to perform this agreement. This obligation survives termination by three years.',
                ],
                [
                    'heading' => 'Termination',
                    'body' => 'Either party may end this agreement on 30 days\' written notice. The Client will pay for work completed up to the termination date.',
                ],
            ],
            'governing_law' => 'the State of Illinois',
        ];
    }
}
