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

        h2.products-heading {
            margin: 36px 0 14px;
            font-size: 18px;
            font-weight: 700;
        }

        table.products {
            width: 100%;
            max-width: 720px;
            border-collapse: collapse;
        }

        table.products th,
        table.products td {
            border: 1px solid #000;
            padding: 12px 14px;
            vertical-align: middle;
            text-align: right;
        }

        table.products th {
            font-weight: 700;
            background: #f2f2f2;
        }

        table.products td.count,
        table.products td.id {
            text-align: left;
            font-variant-numeric: tabular-nums;
            direction: ltr;
            unicode-bidi: isolate;
        }

        table.products .product-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: flex-start;
            flex-direction: row-reverse;
        }

        table.products img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border: 1px solid #000;
            background: #f7f7f7;
            flex-shrink: 0;
        }

        table.products .no-image {
            width: 64px;
            height: 64px;
            border: 1px dashed #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #666;
            flex-shrink: 0;
            background: #fafafa;
        }

        h2.assignments-heading {
            margin: 36px 0 14px;
            font-size: 18px;
            font-weight: 700;
        }

        table.assignments {
            width: 100%;
            max-width: 960px;
            border-collapse: collapse;
        }

        table.assignments th,
        table.assignments td {
            border: 1px solid #000;
            padding: 10px 12px;
            vertical-align: top;
            text-align: right;
        }

        table.assignments th {
            font-weight: 700;
            background: #f2f2f2;
        }

        table.assignments td.ltr {
            text-align: left;
            font-variant-numeric: tabular-nums;
            direction: ltr;
            unicode-bidi: isolate;
        }

        table.assignments .empty {
            text-align: center;
            color: #555;
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

            table.report th,
            table.products th,
            table.assignments th {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.products img {
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

    @if ($report['products']->isNotEmpty())
        <h2 class="products-heading">القسائم المستخدمة حسب المنتج</h2>
        <table class="products">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>رقم المنتج</th>
                    <th>المستخدمة</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['products'] as $product)
                    <tr>
                        <td>
                            <div class="product-cell">
                                @if ($product['image_url'])
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}">
                                @else
                                    <div class="no-image">بدون صورة</div>
                                @endif
                                <span>{{ $product['name'] }}</span>
                            </div>
                        </td>
                        <td class="id">{{ $product['product_id'] ?? '—' }}</td>
                        <td class="count">{{ number_format($product['used_count']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2 class="assignments-heading">اختيارات الضيوف والقسائم</h2>
    <table class="assignments">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الجوال</th>
                <th>المنتج المختار</th>
                <th>رقم القسيمة</th>
                <th>القيمة</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['assignments'] as $assignment)
                <tr>
                    <td>{{ $assignment['contact_name'] !== '' ? $assignment['contact_name'] : '—' }}</td>
                    <td class="ltr">{{ $assignment['contact_phone'] !== '' ? $assignment['contact_phone'] : '—' }}</td>
                    <td>{{ $assignment['product_name'] }}</td>
                    <td class="ltr">{{ $assignment['voucher_id'] }}</td>
                    <td class="ltr">{{ number_format($assignment['value'], 2) }} SAR</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">لا توجد قسائم مخصصة بعد</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Delawa Events &middot; التقرير الحالي للفعالية &middot; ملخص جاهز للطباعة
    </div>
</body>
</html>
