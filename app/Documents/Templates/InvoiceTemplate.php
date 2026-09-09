<?php

namespace App\Documents\Templates;

class InvoiceTemplate extends BillingDocumentTemplate
{
    public function key(): string
    {
        return 'invoice';
    }

    public function name(): string
    {
        return 'Invoice';
    }

    public function description(): string
    {
        return 'Bill a customer for work delivered, with tax and a payable total.';
    }

    public function icon(): string
    {
        return 'file-text';
    }

    public function accent(): string
    {
        return 'blue';
    }

    public function keywords(): array
    {
        return ['bill', 'billing', 'invoicing'];
    }

    protected function numberPlaceholder(): string
    {
        return 'INV-1001';
    }

    protected function secondaryDateName(): string
    {
        return 'due_date';
    }

    protected function secondaryDateLabel(): string
    {
        return 'Due date';
    }

    protected function recipientLabel(): string
    {
        return 'Bill to';
    }

    public function sample(): array
    {
        return array_merge($this->sampleBase(), [
            'number' => 'INV-1001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(21)->toDateString(),
            'notes' => 'Payment due within 21 days by bank transfer. Thank you for your business!',
        ]);
    }
}
