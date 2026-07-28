<?php

namespace App\Services\Tsepass;

use App\Contracts\GiftCardBalance as GiftCardBalanceContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GiftCardBalance implements GiftCardBalanceContract
{
    public function remainingBalance(string $cardNumber): ?float
    {
        $apiKey = config('services.tsepass.api_key');
        $baseUrl = config('services.tsepass.api_url');

        if (! filled($apiKey) || ! filled($baseUrl)) {
            return null;
        }

        $response = Http::withHeaders([
            'x-api-key' => (string) $apiKey,
            'Accept' => 'application/json',
        ])
            ->timeout(10)
            ->connectTimeout(5)
            ->get($this->url('/queries/gift-card-simple'), [
                'cardNumber' => $cardNumber,
                'legal_entity' => config('services.tsepass.legal_entity'),
            ]);

        if ($response->failed()) {
            Log::warning('Tsepass gift card balance lookup failed', [
                'card_number' => $cardNumber,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            Log::warning('Tsepass gift card balance lookup returned unsuccessful response', [
                'card_number' => $cardNumber,
                'body' => $payload,
            ]);

            return null;
        }

        $netCardValue = data_get($payload, 'data.0.netCardValue');

        if (! is_numeric($netCardValue)) {
            return null;
        }

        return (float) $netCardValue;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.tsepass.api_url'), '/').$path;
    }
}
