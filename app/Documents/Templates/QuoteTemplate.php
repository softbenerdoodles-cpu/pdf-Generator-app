<?php

namespace App\Documents\Templates;

class QuoteTemplate extends BillingDocumentTemplate
{
    public function key(): string
    {
        return 'quote';
    }

    public function name(): string
    {
        return 'Quote';
    }

    public function description(): string
    {
        return 'Give a customer a firm price for work before it starts.';
    }

    public function icon(): string
    {
        return 'file-check';
    }

    public function accent(): string
    {
        return 'green';
    }

    public function keywords(): array
    {
        return ['quotation', 'pricing'];
    }

    protected function numberPlaceholder(): string
    {
        return 'QUO-2026-001';
    }

    protected function secondaryDateName(): string
    {
        return 'valid_until';
    }

    protected function secondaryDateLabel(): string
    {
        return 'Valid until';
    }

    protected function recipientLabel(): string
    {
        return 'Prepared for';
    }

    protected function notesLabel(): string
    {
        return 'Terms';
    }

    protected function notesPlaceholder(): string
    {
        return 'How long the price holds, what it includes, what happens next.';
    }

    public function sample(): array
    {
        return array_merge($this->sampleBase(), [
            'number' => 'QUO-2026-001',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'notes' => 'This quote is valid for 30 days. Thank you!',
        ]);
    }
}
