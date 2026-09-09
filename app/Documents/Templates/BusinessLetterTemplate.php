<?php

namespace App\Documents\Templates;

use App\Documents\DocumentTemplate;
use App\Documents\Field;

class BusinessLetterTemplate extends DocumentTemplate
{
    public function key(): string
    {
        return 'business-letter';
    }

    public function name(): string
    {
        return 'Business Letter';
    }

    public function description(): string
    {
        return 'Formal letter with sender block, subject line, and sign-off.';
    }

    public function icon(): string
    {
        return 'mail';
    }

    public function accent(): string
    {
        return 'indigo';
    }

    public function keywords(): array
    {
        return ['letter', 'cover letter', 'formal letter'];
    }

    public function fields(): array
    {
        return [
            Field::date('written_on', 'Date')->required(),
            Field::group('sender', 'From', [
                Field::text('name', 'Name')->required(),
                Field::text('title', 'Job title'),
                Field::text('organisation', 'Organisation'),
                Field::email('email', 'Email'),
                Field::text('phone', 'Phone'),
                Field::textarea('address', 'Address'),
            ]),
            Field::group('recipient', 'To', [
                Field::text('name', 'Name')->required(),
                Field::text('title', 'Job title'),
                Field::text('organisation', 'Organisation'),
                Field::textarea('address', 'Address'),
            ]),
            Field::text('subject', 'Subject')->required()->placeholder('Re: Proposal for website redesign'),
            Field::text('salutation', 'Salutation')->required()->default('Dear'),
            Field::textarea('body', 'Body')->required()->maxLength(20000)
                ->help('Leave a blank line between paragraphs.'),
            Field::text('closing', 'Closing')->required()->default('Sincerely'),
            Field::text('enclosures', 'Enclosures')->placeholder('Proposal PRO-2026-001'),
        ];
    }

    public function title(array $data): string
    {
        return $data['subject'] ?? 'Business letter';
    }

    public function prepare(array $data): array
    {
        $body = (string) ($data['body'] ?? '');
        $paragraphs = preg_split('/\R{2,}/', trim($body)) ?: [];

        return array_merge($data, [
            'paragraphs' => array_values(array_filter($paragraphs, fn ($p) => trim($p) !== '')),
        ]);
    }

    public function sample(): array
    {
        return [
            'written_on' => now()->toDateString(),
            'sender' => [
                'name' => 'Sarah Johnson',
                'title' => 'Studio Director',
                'organisation' => 'Aneta Studio',
                'email' => 'sarah@anetastudio.example',
                'phone' => '+1 (312) 555-0143',
                'address' => "456 Market St\nChicago, IL 60601",
            ],
            'recipient' => [
                'name' => 'John Smith',
                'title' => 'Operations Manager',
                'organisation' => 'Bright Ideas Co.',
                'address' => "123 Business Ave\nNew York, NY 10001",
            ],
            'subject' => 'Re: Proposal for website redesign',
            'salutation' => 'Dear',
            'body' => "Thank you for meeting with us last Tuesday to talk through the state of your website and what you need it to do next year.\n\nI have enclosed our proposal. It covers the rebuild we discussed, the content structure that lets your team publish without our help, and a timeline that puts launch in the first week of December. The pricing is broken down by stage so you can see exactly what each part costs.\n\nThe quote holds for thirty days. I am happy to walk through any part of it on a call, and can usually make time at short notice.\n\nWe would very much like to work with you on this.",
            'closing' => 'Sincerely',
            'enclosures' => 'Proposal PRO-2026-001',
        ];
    }
}
