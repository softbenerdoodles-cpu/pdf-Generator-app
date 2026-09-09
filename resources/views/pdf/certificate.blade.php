{{-- Landscape award. The frame is two nested bordered blocks rather than a
     background image, so it stays crisp at any print size. --}}
@extends('pdf.layout', ['title' => $template->title($document), 'pageMargin' => '12mm'])

@section('content')
    <div style="border: 3px solid {{ $template->accentHex() }}; padding: 5px;">
        <div style="border: 1px solid {{ $template->accentHex() }}; padding: 30px 44px; text-align: center;">
            <div class="doc-type" style="font-size: 12px; letter-spacing: 3px;">
                {{ $document['issuer'] ?? '' }}
            </div>

            <h1 style="font-size: 34px; margin-top: 18px; letter-spacing: 0.5px;">
                {{ $document['heading'] ?? '' }}
            </h1>

            <div class="muted" style="margin-top: 22px; letter-spacing: 1.5px; font-size: 9px; text-transform: uppercase;">
                This is presented to
            </div>

            <div class="accent" style="font-size: 30px; margin-top: 8px;">
                {{ $document['recipient'] ?? '' }}
            </div>

            <div style="width: 130px; border-bottom: 1px solid {{ $template->accentHex() }}; margin: 14px auto 0;"></div>

            <p style="margin-top: 16px; font-size: 12px; line-height: 1.7;">
                {{ \App\Documents\Format::lines($document['citation'] ?? '') }}
            </p>

            <table style="margin-top: 44px;">
                <tr>
                    <td style="width: 40%; text-align: center;">
                        <div style="border-bottom: 1px solid #94a3b8;"></div>
                        <div style="margin-top: 5px;"><strong>{{ $document['signatory_name'] ?? '' }}</strong></div>
                        @if (filled($document['signatory_role'] ?? null))
                            <div class="muted small">{{ $document['signatory_role'] }}</div>
                        @endif
                    </td>
                    <td style="width: 20%;"></td>
                    <td style="width: 40%; text-align: center;">
                        <div style="border-bottom: 1px solid #94a3b8;"></div>
                        <div style="margin-top: 5px;">
                            <strong>{{ \App\Documents\Format::date($document['awarded_on'] ?? null) }}</strong>
                        </div>
                        <div class="muted small">Date awarded</div>
                    </td>
                </tr>
            </table>

            @if (filled($document['reference'] ?? null))
                <div class="muted small" style="margin-top: 22px;">{{ $document['reference'] }}</div>
            @endif
        </div>
    </div>
@endsection
