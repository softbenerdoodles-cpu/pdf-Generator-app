<?php

namespace App\Documents\Templates;

use App\Documents\DocumentTemplate;
use App\Documents\Field;
use App\Documents\Money;

class ProposalTemplate extends DocumentTemplate
{
    public function key(): string
    {
        return 'proposal';
    }

    public function name(): string
    {
        return 'Proposal';
    }

    public function description(): string
    {
        return 'Pitch a piece of work: the problem, your approach, and the price.';
    }

    public function icon(): string
    {
        return 'file-signature';
    }

    public function accent(): string
    {
        return 'pink';
    }

    public function keywords(): array
    {
        return ['pitch', 'scope of work', 'sow'];
    }

    public function fields(): array
    {
        return [
            Field::text('title', 'Proposal title')->required()->placeholder('Website redesign'),
            Field::text('number', 'Reference')->placeholder('PRO-2026-001'),
            Field::date('issue_date', 'Date')->required(),
            Field::date('valid_until', 'Valid until'),
            Field::select('currency', 'Currency', Money::currencies())->required()->default('USD'),
            Field::group('from', 'Prepared by', [
                Field::text('name', 'Business name')->required(),
                Field::text('contact', 'Contact name'),
                Field::email('email', 'Email'),
                Field::text('phone', 'Phone'),
            ]),
            Field::group('to', 'Prepared for', [
                Field::text('name', 'Client name')->required(),
                Field::text('contact', 'Contact name'),
                Field::email('email', 'Email'),
            ]),
            Field::textarea('overview', 'Overview')->required()->maxLength(5000)
                ->help('The problem you are solving, in a short paragraph.'),
            Field::repeater('sections', 'Sections', [
                Field::text('heading', 'Heading')->required(),
                Field::textarea('body', 'Body')->required()->maxLength(20000),
            ])->required()->rows(1, 40),
            Field::repeater('deliverables', 'Pricing', [
                Field::text('description', 'Deliverable')->required(),
                Field::money('amount', 'Amount')->required(),
            ])->rows(0, 60),
            Field::textarea('terms', 'Terms')->placeholder('Payment schedule, timeline, what is out of scope.'),
        ];
    }

    public function title(array $data): string
    {
        return $data['title'] ?? 'Proposal';
    }

    public function prepare(array $data): array
    {
        $currency = $data['currency'] ?? 'USD';
        $total = 0;
        $deliverables = [];

        foreach ($data['deliverables'] ?? [] as $row) {
            if (($row['description'] ?? '') === '') {
                continue;
            }

            $cents = Money::toCents($row['amount'] ?? 0);
            $total += $cents;
            $deliverables[] = $row + ['amount_formatted' => Money::format($cents, $currency)];
        }

        return array_merge($data, [
            'deliverables' => $deliverables,
            'has_pricing' => $deliverables !== [],
            'total' => Money::format($total, $currency),
        ]);
    }

    public function sample(): array
    {
        return [
            'title' => 'Website redesign',
            'number' => 'PRO-2026-001',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'currency' => 'USD',
            'from' => [
                'name' => 'Aneta Studio',
                'contact' => 'Sarah Johnson',
                'email' => 'hello@anetastudio.example',
                'phone' => '+1 (312) 555-0143',
            ],
            'to' => [
                'name' => 'Bright Ideas Co.',
                'contact' => 'John Smith',
                'email' => 'john@brightideas.example',
            ],
            'overview' => 'Your current site was built in 2019 and now takes over four seconds to load on mobile, where two thirds of your traffic arrives. This proposal covers a rebuild on a modern stack, a refreshed visual identity, and a content structure that lets your team publish without developer help.',
            'sections' => [
                [
                    'heading' => 'Approach',
                    'body' => "We start with a two-day discovery workshop to agree the site map and the three journeys that matter most to your customers.\n\nDesign runs in two-week cycles with a review at the end of each, so nothing is built before you have seen it.",
                ],
                [
                    'heading' => 'Timeline',
                    'body' => 'Discovery in week one, design through weeks two to five, build through weeks six to eleven, and launch in week twelve. The date holds provided content is supplied by the end of week eight.',
                ],
            ],
            'deliverables' => [
                ['description' => 'Discovery and information architecture', 'amount' => 2400],
                ['description' => 'Visual design — 12 templates', 'amount' => 7800],
                ['description' => 'Front-end build and CMS integration', 'amount' => 11500],
                ['description' => 'Launch, testing and handover', 'amount' => 2300],
            ],
            'terms' => '40% on signature, 30% at design sign-off, 30% on launch. Prices exclude sales tax and third-party licences.',
        ];
    }
}
