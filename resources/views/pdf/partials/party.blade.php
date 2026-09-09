{{-- One addressed party: name, then any contact lines that were filled in. --}}
@php($party = $party ?? [])
<div class="label">{{ $label }}</div>
<div class="party-name">{{ $party['name'] ?? '' }}</div>
@if (filled($party['contact'] ?? null))
    <div>{{ $party['contact'] }}</div>
@endif
@if (filled($party['address'] ?? null))
    <div class="muted">{{ \App\Documents\Format::lines($party['address']) }}</div>
@endif
@if (filled($party['email'] ?? null))
    <div class="muted small">{{ $party['email'] }}</div>
@endif
@if (filled($party['phone'] ?? null))
    <div class="muted small">{{ $party['phone'] }}</div>
@endif
