@extends('pdf.layout', ['title' => $template->title($document)])

@section('content')
    <div class="doc-type">Proposal</div>
    <h1 style="font-size: 21px; margin-top: 6px;">{{ $document['title'] ?? '' }}</h1>
    <div class="muted small" style="margin-top: 4px;">
        {{ \App\Documents\Format::date($document['issue_date'] ?? null, 'M j, Y') }}
        @if (filled($document['number'] ?? null)) &middot; {{ $document['number'] }} @endif
        @if (filled($document['valid_until'] ?? null))
            &middot; valid until {{ \App\Documents\Format::date($document['valid_until'], 'M j, Y') }}
        @endif
    </div>

    <div class="rule"></div>

    <table>
        <tr>
            <td style="width: 50%; padding-right: 14px;">
                @include('pdf.partials.party', ['label' => 'Prepared for', 'party' => $document['to'] ?? []])
            </td>
            <td style="width: 50%;">
                @include('pdf.partials.party', ['label' => 'Prepared by', 'party' => $document['from'] ?? []])
            </td>
        </tr>
    </table>

    <div style="margin-top: 22px;">
        <div class="label">Overview</div>
        <p>{{ \App\Documents\Format::lines($document['overview'] ?? '') }}</p>
    </div>

    @foreach ($document['sections'] ?? [] as $section)
        <div style="margin-top: 16px;">
            <h2 class="accent" style="font-size: 13px;">{{ $section['heading'] ?? '' }}</h2>
            <p style="margin-top: 5px;">{{ \App\Documents\Format::lines($section['body'] ?? '') }}</p>
        </div>
    @endforeach

    @if ($document['has_pricing'])
        <h2 class="accent" style="font-size: 13px; margin-top: 22px;">Pricing</h2>
        <table class="items">
            <thead>
                <tr>
                    <th>Deliverable</th>
                    <th class="num" style="width: 120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document['deliverables'] as $row)
                    <tr>
                        <td>{{ $row['description'] }}</td>
                        <td class="num">{!! $row['amount_formatted'] !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="totals">
            <tr class="grand">
                <td class="k">Total</td>
                <td class="v accent">{!! $document['total'] !!}</td>
            </tr>
        </table>
    @endif

    @if (filled($document['terms'] ?? null))
        <div class="note">
            <div class="label" style="padding-bottom: 4px;">Terms</div>
            {{ \App\Documents\Format::lines($document['terms']) }}
        </div>
    @endif
@endsection

@section('footer')
    {{ $document['title'] ?? '' }} &middot; {{ $document['from']['name'] ?? '' }}
@endsection
