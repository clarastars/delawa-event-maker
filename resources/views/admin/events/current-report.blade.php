<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>التقرير الحالي — {{ $event->name }}</title>
    <style>
        :root {
            color-scheme: only light;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 32px;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            color: #000;
            background: #fff;
            font-size: 14px;
            line-height: 1.6;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
            direction: ltr;
        }

        .toolbar a,
        .toolbar button {
            appearance: none;
            border: 1px solid #000;
            background: #fff;
            color: #000;
            padding: 8px 14px;
            font: inherit;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .toolbar button {
            background: #000;
            color: #fff;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 28px;
            font-weight: 700;
        }

        .meta {
            margin: 0 0 28px;
            color: #222;
            font-size: 13px;
        }

        .meta .ltr {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
        }

        .badge {
            display: inline-block;
            margin-bottom: 10px;
            border: 1px solid #000;
            padding: 3px 10px;
            font-size: 12px;
        }

        table.report {
            width: 100%;
            max-width: 720px;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            border: 1px solid #000;
            padding: 12px 14px;
            vertical-align: top;
        }

        table.report th {
            width: 55%;
            font-weight: 700;
            background: #f2f2f2;
            text-align: right;
        }

        table.report td {
            text-align: left;
            font-variant-numeric: tabular-nums;
            direction: ltr;
            unicode-bidi: isolate;
        }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #000;
            font-size: 12px;
        }

        @media print {
            body { padding: 0; }

            .toolbar { display: none; }

            table.report th {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            a { color: #000; text-decoration: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <a href="{{ route('admin.events.current-report.statement', $event) }}">Download statement</a>
        <a href="{{ route('admin.events.index') }}">Back to events</a>
    </div>

    <div class="badge">التقرير الحالي · فعالية جارية</div>
    <h1>{{ $event->name }}</h1>
    <p class="meta">
        تاريخ الإنشاء
        <span class="ltr">{{ $generatedAt->format('Y-m-d H:i') }}</span>
        &middot;
        رابط الدعوة
        <span class="ltr">/e/{{ $event->slug }}</span>
        &middot;
        الحالة: مفتوحة
    </p>

    <table class="report">
        <tbody>
            <tr>
                <th>إجمالي القسائم</th>
                <td>{{ number_format($report['total_coupons']) }}</td>
            </tr>
            <tr>
                <th>إجمالي القيمة</th>
                <td>{{ number_format($report['total_value'], 2) }} SAR</td>
            </tr>
            <tr>
                <th>إجمالي القيمة المخصصة</th>
                <td>{{ number_format($report['assigned_value'], 2) }} SAR</td>
            </tr>
            <tr>
                <th>إجمالي القيمة المستخدمة</th>
                <td>{{ number_format($report['used_value'], 2) }} SAR</td>
            </tr>
            <tr>
                <th>القسائم المتبقية (غير مخصصة)</th>
                <td>{{ number_format($report['leftover_coupons']) }}</td>
            </tr>
            <tr>
                <th>القيمة المتبقية المتاحة</th>
                <td>{{ number_format($report['leftover_value'], 2) }} SAR</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Delawa Events &middot; التقرير الحالي للفعالية &middot; ملخص جاهز للطباعة
    </div>
</body>
</html>
