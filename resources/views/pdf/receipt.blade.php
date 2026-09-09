@extends('pdf.layout', ['title' => $template->title($document)])


@section('content')
    <table>
        <tr>
            <td style="width: 55%;">
                @include('pdf.partials.party', ['label' => 'Received by', 'party' => $document['from'] ?? []])
            </td>
            <td style="text-align: right;">
                <div class="doc-type">Receipt</div>
                <table class="small" style="margin-top: 10px;">
                    <tr>
                        <td class="muted" style="text-align: right; padding: 1px 8px 1px 0;">Number</td>
                        <td style="text-align: right; width: 90px;"><strong>{{ $document['number'] ?? '' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="muted" style="text-align: right; padding: 1px 8px 1px 0;">Paid on</td>
                        <td style="text-align: right;">{{ \App\Documents\Format::date($document['paid_on'] ?? null, 'M j, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="muted" style="text-align: right; padding: 1px 8px 1px 0;">Method</td>
                        <td style="text-align: right;">{{ $document['method_label'] }}</td>
                    </tr>
                    @if (filled($document['reference'] ?? null))
                        <tr>
                            <td class="muted" style="text-align: right; padding: 1px 8px 1px 0;">Reference</td>
                            <td style="text-align: right;">{{ $document['reference'] }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    <div class="label">Received from</div>
    <div class="party-name">{{ $document['payer'] ?? '' }}</div>

    <table class="items" style="margin-top: 22px;">
        <thead>
            <tr>
                <th>Description</th>
                <th class="num" style="width: 120px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document['items'] ?? [] as $item)
                <tr>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td class="num">{!! $item['amount_formatted'] !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="grand">
            <td class="k">Paid in full</td>
            <td class="v accent">{!! $document['total'] !!}</td>
        </tr>
    </table>

    @if (filled($document['notes'] ?? null))
        <div class="note">{{ \App\Documents\Format::lines($document['notes']) }}</div>
    @endif
@endsection

@section('footer')
    {{ $document['number'] ?? '' }} &middot; {{ $document['from']['name'] ?? '' }}
@endsection
