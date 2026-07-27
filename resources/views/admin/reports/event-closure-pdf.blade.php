<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Event Closure — {{ $event->name }}</title>
    <style>
        body { font-family: dejavusans, sans-serif; color: #0f172a; font-size: 11px; line-height: 1.45; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #475569; margin: 0 0 8px; }
        .muted { color: #64748b; }
        .badge { display: inline-block; background: #f5ecee; color: #7d4651; padding: 4px 10px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .grid th, .grid td { border: 1px solid #e2e8f0; padding: 7px 8px; text-align: left; }
        .grid th { background: #f8fafc; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; }
        .metrics { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .metrics td { width: 25%; vertical-align: top; padding: 10px 12px; border: 1px solid #e2e8f0; }
        .metric-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
        .metric-value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .section { margin-top: 18px; page-break-inside: avoid; }
        .notes { border: 1px solid #e2e8f0; background: #f8fafc; padding: 10px 12px; min-height: 54px; white-space: pre-wrap; }
        .notes-ar { direction: rtl; text-align: right; font-family: xbriyaz, dejavusans, sans-serif; }
        .footer { margin-top: 18px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    <span class="badge">Executive closure summary</span>
    <h1>{{ $event->name }}</h1>
    <p class="muted">
        Generated {{ $generatedAt->format('Y-m-d H:i') }}
        @if ($event->closed_at)
            &middot; Closed {{ $event->closed_at->format('Y-m-d H:i') }}
        @endif
        @if ($closedBy)
            &middot; Prepared by {{ $closedBy->name ?: $closedBy->email }}
        @endif
    </p>

    <table class="metrics">
        <tr>
            <td>
                <div class="metric-label">Total budget</div>
                <div class="metric-value">{{ number_format($metrics['values']['total_budget'], 2) }} SAR</div>
                <div class="muted">{{ $metrics['counts']['total_vouchers'] }} gift cards</div>
            </td>
            <td>
                <div class="metric-label">Distributed</div>
                <div class="metric-value">{{ number_format($metrics['values']['distributed_value'], 2) }} SAR</div>
                <div class="muted">Assignment {{ $metrics['rates']['assignment_rate'] !== null ? number_format($metrics['rates']['assignment_rate'], 1).'%' : '—' }}</div>
            </td>
            <td>
                <div class="metric-label">Activated</div>
                <div class="metric-value">{{ number_format($metrics['values']['activated_value'], 2) }} SAR</div>
                <div class="muted">Activation {{ $metrics['rates']['activation_rate'] !== null ? number_format($metrics['rates']['activation_rate'], 1).'%' : '—' }}</div>
            </td>
            <td>
                <div class="metric-label">Consumed / remaining</div>
                <div class="metric-value">{{ number_format($metrics['values']['consumed_value'], 2) }} / {{ number_format($metrics['values']['remaining_value'], 2) }}</div>
                <div class="muted">Utilization {{ $metrics['rates']['utilization_rate'] !== null ? number_format($metrics['rates']['utilization_rate'], 1).'%' : '—' }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <h2>Gift card outcomes</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Outcome</th>
                    <th>Cards</th>
                    <th>Value impact</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Unassigned</td><td>{{ $metrics['counts']['unassigned'] }}</td><td>{{ number_format($metrics['values']['undistributed_value'], 2) }} SAR not distributed</td></tr>
                <tr><td>Assigned, not activated</td><td>{{ $metrics['counts']['assigned_pending_activation'] }}</td><td>Awaiting guest activation</td></tr>
                <tr><td>Activated, balance intact</td><td>{{ $metrics['counts']['activated_unused'] }}</td><td>Budget issued, not yet spent</td></tr>
                <tr><td>Activated, balance not synced</td><td>{{ $metrics['counts']['activated_untracked'] }}</td><td>{{ number_format($metrics['values']['untracked_value'], 2) }} SAR untracked</td></tr>
                <tr><td>Partially spent</td><td>{{ $metrics['counts']['partially_used'] }}</td><td>Partial utilization</td></tr>
                <tr><td>Fully spent</td><td>{{ $metrics['counts']['fully_used'] }}</td><td>Completed utilization</td></tr>
                <tr><td>Expired / inactive</td><td>{{ $metrics['counts']['expired'] + $metrics['counts']['inactive'] }}</td><td>Non-operational cards</td></tr>
            </tbody>
        </table>
    </div>

    @php
        $containsArabic = fn (?string $text): bool => filled($text) && preg_match('/[\x{0600}-\x{06FF}]/u', $text) === 1;
    @endphp

    <div class="section">
        <h2>Observations</h2>
        @if (filled($closureNotes['observations'] ?? null))
            <div @class(['notes', 'notes-ar' => $containsArabic($closureNotes['observations'])]) lang="{{ $containsArabic($closureNotes['observations']) ? 'ar' : 'en' }}" dir="{{ $containsArabic($closureNotes['observations']) ? 'rtl' : 'ltr' }}">{{ $closureNotes['observations'] }}</div>
        @else
            <div class="notes">No observations recorded.</div>
        @endif
    </div>

    <div class="section">
        <h2>Lessons learned</h2>
        @if (filled($closureNotes['lessons_learned'] ?? null))
            <div @class(['notes', 'notes-ar' => $containsArabic($closureNotes['lessons_learned'])]) lang="{{ $containsArabic($closureNotes['lessons_learned']) ? 'ar' : 'en' }}" dir="{{ $containsArabic($closureNotes['lessons_learned']) ? 'rtl' : 'ltr' }}">{{ $closureNotes['lessons_learned'] }}</div>
        @else
            <div class="notes">No lessons recorded.</div>
        @endif
    </div>

    <div class="section">
        <h2>Recommendations for future events</h2>
        @if (filled($closureNotes['recommendations'] ?? null))
            <div @class(['notes', 'notes-ar' => $containsArabic($closureNotes['recommendations'])]) lang="{{ $containsArabic($closureNotes['recommendations']) ? 'ar' : 'en' }}" dir="{{ $containsArabic($closureNotes['recommendations']) ? 'rtl' : 'ltr' }}">{{ $closureNotes['recommendations'] }}</div>
        @else
            <div class="notes">No recommendations recorded.</div>
        @endif
    </div>

    <div class="footer">
        Delawa Events &middot; Event closure report &middot; Invite slug /e/{{ $event->slug }}
    </div>
</body>
</html>
