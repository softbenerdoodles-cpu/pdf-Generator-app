<?php

namespace App\Documents\Templates;

use App\Documents\DocumentTemplate;
use App\Documents\Field;

class CertificateTemplate extends DocumentTemplate
{
    public function key(): string
    {
        return 'certificate';
    }

    public function name(): string
    {
        return 'Certificate';
    }

    public function description(): string
    {
        return 'Landscape award certificate with a bordered frame and signature.';
    }

    public function icon(): string
    {
        return 'award';
    }

    public function accent(): string
    {
        return 'amber';
    }

    public function keywords(): array
    {
        return ['award', 'diploma', 'completion', 'achievement'];
    }

    public function paperOrientation(): string
    {
        return 'landscape';
    }

    public function fields(): array
    {
        return [
            Field::text('heading', 'Heading')->required()->default('Certificate of Completion'),
            Field::text('recipient', 'Recipient')->required(),
            Field::textarea('citation', 'Citation')->required()->maxLength(1000)
                ->placeholder('for successfully completing the Advanced Laravel programme'),
            Field::date('awarded_on', 'Date awarded')->required(),
            Field::text('issuer', 'Issued by')->required(),
            Field::text('signatory_name', 'Signatory name')->required(),
            Field::text('signatory_role', 'Signatory role'),
            Field::text('reference', 'Reference')->placeholder('CERT-2026-0001'),
        ];
    }

    public function title(array $data): string
    {
        return trim(($data['heading'] ?? 'Certificate').' — '.($data['recipient'] ?? ''), ' —');
    }

    public function sample(): array
    {
        return [
            'heading' => 'Certificate of Completion',
            'recipient' => 'John Smith',
            'citation' => 'for successfully completing the Advanced Laravel programme, comprising forty hours of instruction and a final assessed project.',
            'awarded_on' => now()->toDateString(),
            'issuer' => 'Aneta Studio',
            'signatory_name' => 'Sarah Johnson',
            'signatory_role' => 'Studio Director',
            'reference' => 'CERT-2026-0001',
        ];
    }
}
