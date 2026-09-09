@extends('pdf.layout', ['title' => $template->title($document), 'pageMargin' => '22mm 20mm'])

@section('content')
    <div style="text-align: right;">
        <div class="party-name">{{ $document['sender']['name'] ?? '' }}</div>
        @if (filled($document['sender']['title'] ?? null))
            <div class="muted small">{{ $document['sender']['title'] }}</div>
        @endif
        @if (filled($document['sender']['organisation'] ?? null))
            <div class="small">{{ $document['sender']['organisation'] }}</div>
        @endif
        @if (filled($document['sender']['address'] ?? null))
            <div class="muted small">{{ \App\Documents\Format::lines($document['sender']['address']) }}</div>
        @endif
        @if (filled($document['sender']['email'] ?? null))
            <div class="muted small">{{ $document['sender']['email'] }}</div>
        @endif
        @if (filled($document['sender']['phone'] ?? null))
            <div class="muted small">{{ $document['sender']['phone'] }}</div>
        @endif
    </div>

    <div style="margin-top: 26px;">{{ \App\Documents\Format::date($document['written_on'] ?? null) }}</div>

    <div style="margin-top: 22px;">
        <div class="party-name">{{ $document['recipient']['name'] ?? '' }}</div>
        @if (filled($document['recipient']['title'] ?? null))
            <div class="muted small">{{ $document['recipient']['title'] }}</div>
        @endif
        @if (filled($document['recipient']['organisation'] ?? null))
            <div class="small">{{ $document['recipient']['organisation'] }}</div>
        @endif
        @if (filled($document['recipient']['address'] ?? null))
            <div class="muted small">{{ \App\Documents\Format::lines($document['recipient']['address']) }}</div>
        @endif
    </div>

    <div style="margin-top: 22px;"><strong>{{ $document['subject'] ?? '' }}</strong></div>

    <p style="margin-top: 16px;">{{ $document['salutation'] ?? 'Dear' }} {{ $document['recipient']['name'] ?? '' }},</p>

    @foreach ($document['paragraphs'] ?? [] as $paragraph)
        <p>{{ \App\Documents\Format::lines($paragraph) }}</p>
    @endforeach

    <div style="margin-top: 24px;">{{ $document['closing'] ?? 'Sincerely' }},</div>
    <div style="height: 34px;"></div>
    <div class="party-name">{{ $document['sender']['name'] ?? '' }}</div>
    @if (filled($document['sender']['title'] ?? null))
        <div class="muted small">{{ $document['sender']['title'] }}</div>
    @endif

    @if (filled($document['enclosures'] ?? null))
        <div class="muted small" style="margin-top: 20px;">Enc: {{ $document['enclosures'] }}</div>
    @endif
@endsection
