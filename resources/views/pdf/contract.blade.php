@extends('pdf.layout', ['title' => $template->title($document)])

@section('content')
    <div style="text-align: center;">
        <h1 style="font-size: 19px; letter-spacing: 0.5px;">{{ $document['title'] ?? '' }}</h1>
        <div class="muted small" style="margin-top: 5px;">
            Effective {{ \App\Documents\Format::date($document['effective_date'] ?? null) }}
            @if (filled($document['reference'] ?? null)) &middot; {{ $document['reference'] }} @endif
        </div>
    </div>

    <div class="rule"></div>

    <p>
        This agreement is made between
        <strong>{{ $document['party_one']['name'] ?? '' }}</strong>
        @if (filled($document['party_one']['role'] ?? null)) ({{ $document['party_one']['role'] }}) @endif
        and
        <strong>{{ $document['party_two']['name'] ?? '' }}</strong>
        @if (filled($document['party_two']['role'] ?? null)) ({{ $document['party_two']['role'] }}) @endif,
        with effect from {{ \App\Documents\Format::date($document['effective_date'] ?? null) }}.
    </p>

    @if (filled($document['preamble'] ?? null))
        <p>{{ \App\Documents\Format::lines($document['preamble']) }}</p>
    @endif

    @foreach ($document['clauses'] ?? [] as $index => $clause)
        <div style="margin-top: 14px;">
            <h2 style="font-size: 12px;">
                <span class="accent">{{ $index + 1 }}.</span> {{ $clause['heading'] ?? '' }}
            </h2>
            <p style="margin-top: 4px;">{{ \App\Documents\Format::lines($clause['body'] ?? '') }}</p>
        </div>
    @endforeach

    @if (filled($document['governing_law'] ?? null))
        <p style="margin-top: 14px;">
            This agreement is governed by the laws of {{ $document['governing_law'] }}.
        </p>
    @endif

    <table style="margin-top: 40px;">
        <tr>
            @foreach (['party_one', 'party_two'] as $key)
                <td style="width: 50%; padding-right: 20px;">
                    <div style="border-bottom: 1px solid #94a3b8; height: 34px;"></div>
                    <div style="margin-top: 5px;"><strong>{{ $document[$key]['name'] ?? '' }}</strong></div>
                    @if (filled($document[$key]['role'] ?? null))
                        <div class="muted small">{{ $document[$key]['role'] }}</div>
                    @endif
                    <div class="muted small" style="margin-top: 8px;">Date: ________________</div>
                </td>
            @endforeach
        </tr>
    </table>
@endsection

@section('footer')
    {{ $document['title'] ?? '' }}
@endsection
