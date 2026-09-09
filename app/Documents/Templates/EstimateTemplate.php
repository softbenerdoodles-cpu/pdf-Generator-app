<?php

namespace App\Documents\Templates;

class EstimateTemplate extends BillingDocumentTemplate
{
    public function key(): string
    {
        return 'estimate';
    }

    public function name(): string
    {
        return 'Estimate';
    }

    public function description(): string
    {
        return 'Sketch out likely costs when the final price can still move.';
    }

    public function icon(): string
    {
        return 'clipboard-list';
    }

    public function accent(): string
    {
        return 'orange';
    }

    public function keywords(): array
    {
        return ['estimation', 'ballpark', 'budget'];
    }

    protected function numberPlaceholder(): string
    {
        return 'EST-2026-001';
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
        return 'Assumptions';
    }

    protected function notesPlaceholder(): string
    {
        return 'What this estimate assumes, and what could change the final figure.';
    }

    public function sample(): array
    {
        return array_merge($this->sampleBase(), [
            'number' => 'EST-2026-001',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'items' => [
                ['description' => 'Discovery workshop', 'quantity' => 2, 'unit_price' => 450],
                ['description' => 'Design — estimated days', 'quantity' => 6, 'unit_price' => 520],
                ['description' => 'Build — estimated days', 'quantity' => 10, 'unit_price' => 620],
            ],
            'notes' => 'Figures assume two rounds of revisions and content supplied by the client. Final costs are confirmed before work begins.',
        ]);
    }
}
