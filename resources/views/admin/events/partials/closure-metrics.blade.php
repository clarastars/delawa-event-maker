@php
    $formatMoney = fn (float $amount): string => number_format($amount, 2);
    $formatRate = fn (?float $rate): string => $rate === null ? '—' : number_format($rate, 1).'%';
@endphp

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total budget</p>
    <p class="mt-2 text-2xl font-black text-slate-950">{{ $formatMoney($metrics['values']['total_budget']) }} SAR</p>
    <p class="mt-1 text-xs text-slate-500">{{ $metrics['counts']['total_vouchers'] }} gift cards</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Distributed</p>
    <p class="mt-2 text-2xl font-black text-[#4E2E36]">{{ $formatMoney($metrics['values']['distributed_value']) }} SAR</p>
    <p class="mt-1 text-xs text-slate-500">Assignment rate: {{ $formatRate($metrics['rates']['assignment_rate']) }}</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Activated</p>
    <p class="mt-2 text-2xl font-black text-sky-700">{{ $formatMoney($metrics['values']['activated_value']) }} SAR</p>
    <p class="mt-1 text-xs text-slate-500">Activation rate: {{ $formatRate($metrics['rates']['activation_rate']) }}</p>
  </div>
  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Consumed vs remaining</p>
    <p class="mt-2 text-2xl font-black text-emerald-700">{{ $formatMoney($metrics['values']['consumed_value']) }} <span class="text-base font-bold text-slate-400">/</span> {{ $formatMoney($metrics['values']['remaining_value']) }}</p>
    <p class="mt-1 text-xs text-slate-500">Utilization: {{ $formatRate($metrics['rates']['utilization_rate']) }}</p>
  </div>
</div>

<div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
  <table class="w-full text-left text-sm">
    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
      <tr>
        <th class="px-4 py-3">Gift card outcome</th>
        <th class="px-4 py-3 text-right">Count</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-200">
      <tr>
        <td class="px-4 py-3 text-slate-700">Unassigned</td>
        <td class="px-4 py-3 text-right font-semibold">{{ $metrics['counts']['unassigned'] }}</td>
      </tr>
      <tr>
        <td class="px-4 py-3 text-slate-700">Assigned, not activated</td>
        <td class="px-4 py-3 text-right font-semibold">{{ $metrics['counts']['assigned_pending_activation'] }}</td>
      </tr>
      <tr>
        <td class="px-4 py-3 text-slate-700">Activated, balance intact</td>
        <td class="px-4 py-3 text-right font-semibold">{{ $metrics['counts']['activated_unused'] }}</td>
      </tr>
      <tr>
        <td class="px-4 py-3 text-slate-700">Activated, balance not synced</td>
        <td class="px-4 py-3 text-right font-semibold">{{ $metrics['counts']['activated_untracked'] }}</td>
      </tr>
      <tr>
        <td class="px-4 py-3 text-slate-700">Partially spent</td>
        <td class="px-4 py-3 text-right font-semibold">{{ $metrics['counts']['partially_used'] }}</td>
      </tr>
      <tr>
        <td class="px-4 py-3 text-slate-700">Fully spent</td>
        <td class="px-4 py-3 text-right font-semibold">{{ $metrics['counts']['fully_used'] }}</td>
      </tr>
      <tr>
        <td class="px-4 py-3 text-slate-700">Expired / inactive</td>
        <td class="px-4 py-3 text-right font-semibold">{{ $metrics['counts']['expired'] + $metrics['counts']['inactive'] }}</td>
      </tr>
    </tbody>
  </table>
</div>
