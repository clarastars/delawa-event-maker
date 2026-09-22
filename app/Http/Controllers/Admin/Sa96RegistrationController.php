<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sa96Registration;
use App\Support\PhoneNumber;
use App\Support\Sa96Privacy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Sa96RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();

        if (! in_array($status, ['active', 'withdrawn', 'all'], true)) {
            $status = 'active';
        }

        $registrations = $this->filteredQuery($search, $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.sa96.index', [
            'registrations' => $registrations,
            'search' => $search,
            'status' => $status,
            'activeCount' => Sa96Registration::query()->active()->count(),
            'withdrawnCount' => Sa96Registration::query()->withdrawn()->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();

        if (! in_array($status, ['active', 'withdrawn', 'all'], true)) {
            $status = 'active';
        }

        $filename = 'sa96-registrations-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($search, $status): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'name',
                'phone',
                'birth_year',
                'locale',
                'consent_marketing',
                'consent_notice_version',
                'consent_method',
                'consented_at',
                'withdrawn_at',
                'created_at',
            ]);

            $this->filteredQuery($search, $status)
                ->latest()
                ->cursor()
                ->each(function (Sa96Registration $registration) use ($handle): void {
                    fputcsv($handle, [
                        $registration->name ?? '',
                        $registration->phone ?? '',
                        $registration->birth_year ?? '',
                        $registration->locale,
                        $registration->consent_marketing ? 'yes' : 'no',
                        $registration->consent_notice_version,
                        $registration->consent_method,
                        $registration->consented_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '',
                        $registration->withdrawn_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '',
                        $registration->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '',
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function filteredQuery(string $search, string $status): Builder
    {
        $query = Sa96Registration::query();

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'withdrawn') {
            $query->withdrawn();
        }

        if ($search === '') {
            return $query;
        }

        $e164 = PhoneNumber::toE164($search);

        if ($e164 !== null) {
            return $query->where('phone_hash', Sa96Privacy::phoneHash($e164));
        }

        if (preg_match('/^\d{4}$/', $search) === 1) {
            return $query->where('birth_year', (int) $search);
        }

        return $query->whereRaw('0 = 1');
    }
}
