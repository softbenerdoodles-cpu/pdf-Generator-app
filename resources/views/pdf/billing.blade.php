{{-- Invoice, quote and estimate: same bones, different wording. --}}
@extends('pdf.layout', ['title' => $template->title($document)])


@section('content')
    <table>
        <tr>
            <td style="width: 55%;">
                @include('pdf.partials.party', ['label' => 'From', 'party' => $document['from'] ?? []])
            </td>
            <td style="text-align: right;">
                <div class="doc-type">{{ $template->name() }}</div>
                <table class="small" style="margin-top: 10px;">
                    <tr>
                        <td class="muted" style="text-align: right; padding: 1px 8px 1px 0;">Number</td>
                        <td style="text-align: right; width: 90px;"><strong>{{ $document['number'] ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="muted" style="text-align: right; padding: 1px 8px 1px 0;">Issue date</td>
                        <td style="text-align: right;">{{ \App\Documents\Format::date($document['issue_date'] ?? null, 'M j, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="muted" style="text-align: right; padding: 1px 8px 1px 0;">{{ $document['secondary_date_label'] }}</td>
                        <td style="text-align: right;">{{ \App\Documents\Format::date($document['secondary_date'] ?? null, 'M j, Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    @include('pdf.partials.party', [
        'label' => $document['recipient_label'],
        'party' => $document['to'] ?? [],
    ])

    <table class="items" style="margin-top: 22px;">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num" style="width: 60px;">Qty</th>
                <th class="num" style="width: 95px;">Unit price</th>
                <th class="num" style="width: 100px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document['items'] ?? [] as $item)
                <tr>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td class="num">{{ \App\Documents\Format::quantity($item['quantity'] ?? 0) }}</td>
                    <td class="num">{!! $item['unit_price_formatted'] !!}</td>
                    <td class="num">{!! $item['line_total'] !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="k">Subtotal</td>
            <td class="v">{!! $document['subtotal'] !!}</td>
        </tr>
        @if ($document['has_discount'])
            <tr>
                <td class="k">Discount</td>
                <td class="v">&minus;{!! $document['discount_formatted'] !!}</td>
            </tr>
        @endif
        @if ($document['has_tax'])
            <tr>
                <td class="k">Tax ({{ \App\Documents\Format::quantity($document['tax_rate']) }}%)</td>
                <td class="v">{!! $document['tax'] !!}</td>
            </tr>
        @endif
        <tr class="grand">
            <td class="k">Total</td>
            <td class="v accent">{!! $document['total'] !!}</td>
        </tr>
    </table>

    @if (filled($document['notes'] ?? null))
        <div class="note">
            <div class="label" style="padding-bottom: 4px;">{{ $template->key() === 'invoice' ? 'Notes' : ($template->key() === 'quote' ? 'Terms' : 'Assumptions') }}</div>
            {{ \App\Documents\Format::lines($document['notes']) }}
        </div>
    @endif
@endsection

@section('footer')
    {{ $document['number'] ?? '' }} &middot; {{ $document['from']['name'] ?? '' }}
@endsection
