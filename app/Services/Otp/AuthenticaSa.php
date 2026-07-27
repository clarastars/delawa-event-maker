<?php

namespace App\Services\Otp;

use App\Contracts\Otp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AuthenticaSa implements Otp
{
    public function send(string $phone): void
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->connectTimeout(5)
            ->post($this->url('/api/v2/send-otp'), [
                'method' => 'sms',
                'phone' => $phone,
            ]);

        if ($response->failed()) {
            Log::warning('Authentica send OTP failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new RuntimeException('Unable to send verification code.');
        }
    }

    public function verify(string $phone, string $otp): bool
    {
        $otp = preg_replace('/\D+/', '', $otp) ?? '';

        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->connectTimeout(5)
            ->post($this->url('/api/v2/verify-otp'), [
                'phone' => $phone,
                'otp' => $otp,
            ]);

        if ($response->failed()) {
            Log::warning('Authentica verify OTP failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return false;
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            Log::warning('Authentica verify OTP returned non-json response', [
                'phone' => $phone,
            ]);

            return false;
        }

        return $this->isVerified($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function isVerified(array $payload): bool
    {
        foreach ([
            data_get($payload, 'verified'),
            data_get($payload, 'success'),
            data_get($payload, 'status'),
            data_get($payload, 'data.verified'),
            data_get($payload, 'data.success'),
            data_get($payload, 'data.status'),
        ] as $value) {
            if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Authorization' => (string) config('services.authentica.api_key'),
        ];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.authentica.base_url'), '/').$path;
    }
}
