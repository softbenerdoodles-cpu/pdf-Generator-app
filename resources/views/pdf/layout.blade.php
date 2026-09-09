{{-- Base page for every generated PDF. Dompdf has no flexbox or grid, so the
     layouts below are built from tables and plain block flow. --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? $template->name() }}</title>
    <style>
        @page { margin: {{ $pageMargin ?? '18mm 16mm' }}; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10.5px;
            line-height: 1.55;
            color: #0f172a;
            margin: 0;
        }

        h1, h2, h3 { margin: 0; font-weight: bold; }

        p { margin: 0 0 9px; }

        table { width: 100%; border-collapse: collapse; }

        td, th { vertical-align: top; }

        .accent { color: {{ $template->accentHex() }}; }

        .doc-type {
            font-size: 25px;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: {{ $template->accentHex() }};
        }

        .muted { color: #64748b; }

        .small { font-size: 9.5px; }

        .label {
            font-size: 8.5px;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            padding-bottom: 3px;
        }

        .rule { border-top: 1px solid #e2e8f0; height: 1px; font-size: 0; margin: 14px 0; }

        .party-name { font-weight: bold; font-size: 11.5px; }

        /* Line-item table */
        .items { margin-top: 4px; }

        .items th {
            font-size: 8.5px;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #475569;
            text-align: left;
            padding: 7px 8px;
            border-bottom: 1.5px solid {{ $template->accentHex() }};
        }

        .items td { padding: 8px; border-bottom: 1px solid #e2e8f0; }

        .items .num { text-align: right; white-space: nowrap; }

        .totals { margin-top: 12px; }

        .totals td { padding: 4px 8px; }

        .totals .k { text-align: right; color: #475569; }

        .totals .v { text-align: right; white-space: nowrap; width: 110px; }

        .totals .grand td {
            border-top: 1.5px solid {{ $template->accentHex() }};
            padding-top: 8px;
            font-size: 13px;
            font-weight: bold;
        }

        .note {
            margin-top: 20px;
            padding: 11px 13px;
            background: #f8fafc;
            border-left: 3px solid {{ $template->accentHex() }};
        }

        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    @yield('content')

    @hasSection('footer')
        <div class="footer">@yield('footer')</div>
    @endif
</body>
</html>
