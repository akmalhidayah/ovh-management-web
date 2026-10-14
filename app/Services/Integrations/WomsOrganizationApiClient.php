<?php

namespace App\Services\Integrations;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JsonException;

class WomsOrganizationApiClient
{
    private const SNAPSHOT_PATH = '/api/v1/organization/snapshot';

    /**
     * @return array<string, mixed>
     */
    public function fetchSnapshot(): array
    {
        $baseUrl = rtrim((string) config('services.woms_organization.base_url'), '/');
        $token = trim((string) config('services.woms_organization.token'));
        $timeout = min(30, max(1, (int) config('services.woms_organization.timeout_seconds', 15)));

        $this->assertConfigurationIsSafe($baseUrl, $token);

        $response = $this->requestWithLimitedRetry($baseUrl.self::SNAPSHOT_PATH, $token, $timeout);

        if ($response->status() === 401 || $response->status() === 403) {
            throw new WomsOrganizationException(
                'authentication',
                'Kredensial API WOMS ditolak. Periksa token integrasi pada konfigurasi server.'
            );
        }

        if ($response->status() !== 200) {
            throw new WomsOrganizationException(
                'remote_http',
                "API WOMS merespons dengan HTTP {$response->status()}. Data lokal tidak diubah."
            );
        }

        try {
            $objectPayload = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
            $payload = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new WomsOrganizationException(
                'invalid_json',
                'API WOMS tidak mengembalikan JSON yang valid. Data lokal tidak diubah.'
            );
        }

        if (! is_object($objectPayload) || ! is_array($payload)) {
            throw new WomsOrganizationException(
                'invalid_json',
                'Root respons API WOMS harus berupa JSON object.'
            );
        }

        return $payload;
    }

    private function assertConfigurationIsSafe(string $baseUrl, string $token): void
    {
        if ($baseUrl === '' || $token === '') {
            throw new WomsOrganizationException(
                'configuration',
                'URL atau token API WOMS belum dikonfigurasi.'
            );
        }

        $parts = parse_url($baseUrl);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');
        $hasUnexpectedParts = isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment']);

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || $hasUnexpectedParts) {
            throw new WomsOrganizationException(
                'configuration',
                'Konfigurasi URL API WOMS tidak valid.'
            );
        }

        if (app()->environment('production') && $scheme !== 'https') {
            throw new WomsOrganizationException(
                'configuration',
                'API WOMS wajib menggunakan HTTPS pada environment production.'
            );
        }
    }

    private function requestWithLimitedRetry(string $url, string $token, int $timeout): Response
    {
        $retryableStatuses = [408, 425, 429, 500, 502, 503, 504];
        $lastConnectionFailure = false;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = Http::acceptJson()
                    ->withToken($token)
                    ->connectTimeout(min($timeout, 10))
                    ->timeout($timeout)
                    ->withOptions([
                        'allow_redirects' => false,
                        'verify' => true,
                    ])
                    ->get($url);

                if (! in_array($response->status(), $retryableStatuses, true) || $attempt === 3) {
                    return $response;
                }
            } catch (ConnectionException) {
                $lastConnectionFailure = true;

                if ($attempt === 3) {
                    break;
                }
            }

            usleep(250_000 * $attempt);
        }

        if ($lastConnectionFailure) {
            throw new WomsOrganizationException(
                'connection',
                'API WOMS tidak dapat dihubungi dalam batas waktu. Data lokal tidak diubah.'
            );
        }

        throw new WomsOrganizationException(
            'remote_http',
            'API WOMS sedang tidak tersedia. Data lokal tidak diubah.'
        );
    }
}
