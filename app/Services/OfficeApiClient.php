<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use Config\OfficeApi;

class OfficeApiClient
{
    private CURLRequest $http;
    private OfficeApi $config;
    /** @var array<string,string> */
    private array $fixedQuery = [];

    public function __construct()
    {
        $this->config = config(OfficeApi::class);
        $this->config->enabled = filter_var(env('OFFICE_API_ENABLED', $this->config->enabled), FILTER_VALIDATE_BOOL);
        $this->config->baseUrl = (string) env('OFFICE_API_BASE_URL', $this->config->baseUrl);
        $this->config->endpoint = (string) env('OFFICE_API_ENDPOINT', $this->config->endpoint);
        $this->config->token = (string) env('OFFICE_API_TOKEN', $this->config->token);
        $this->config->timeout = (int) env('OFFICE_API_TIMEOUT', $this->config->timeout);
        $this->config->verifySsl = filter_var(env('OFFICE_API_VERIFY_SSL', $this->config->verifySsl), FILTER_VALIDATE_BOOL);

        $this->normalizeBaseUrlAndEndpoint();

        $options = [
            'baseURI' => rtrim($this->config->baseUrl, '/') . '/',
            'timeout' => $this->config->timeout,
            'connect_timeout' => min(5, max(1, (int) floor($this->config->timeout / 2))),
            'verify'  => $this->config->verifySsl,
        ];

        /** @var CURLRequest $req */
        $req = service('curlrequest', $options);
        $this->http = $req;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->config->enabled && $this->config->baseUrl !== '' && $this->config->endpoint !== '';
    }

    /**
     * Normalize common misconfig:
     * - OFFICE_API_BASE_URL accidentally set to full URL: http://host/api.php?tgl_uji=...
     *   -> baseUrl = http://host/ , endpoint = api.php , fixedQuery contains parsed query
     */
    private function normalizeBaseUrlAndEndpoint(): void
    {
        $rawBase = trim($this->config->baseUrl);
        if ($rawBase === '') {
            return;
        }

        $parts = @parse_url($rawBase);
        if (!is_array($parts) || !isset($parts['host'])) {
            return;
        }

        $path = (string) ($parts['path'] ?? '');
        $query = (string) ($parts['query'] ?? '');
        $looksLikeFull = $path !== '' && ($path !== '/' || $query !== '');

        // If baseUrl is mistakenly set to a full URL (with path/query),
        // split it into host-only baseUrl + endpoint path.
        if ($looksLikeFull) {
            $scheme = (string) ($parts['scheme'] ?? 'http');
            $host = (string) $parts['host'];
            $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
            $this->config->baseUrl = $scheme . '://' . $host . $port . '/';

            if ($this->config->endpoint === '' && $path !== '' && $path !== '/') {
                $this->config->endpoint = ltrim($path, '/');
            }
        }

        if ($query !== '') {
            parse_str($query, $parsed);
            foreach ($parsed as $k => $v) {
                if (!is_string($k)) continue;
                if (is_scalar($v) && (string) $v !== '') {
                    $this->fixedQuery[$k] = (string) $v;
                }
            }
        }
    }

    /**
     * Fetch queue list from office API.
     *
     * Expected office fields include:
     * - no_antrian, id_hasil_uji, id_daftar, id_kendaraan, no_kendaraan, no_uji, nama_pemilik, posisi, jdatang, jselesai, ...
     *
     * This method returns a normalized list of associative arrays.
     *
     * @return array{items: array<int,array<string,mixed>>, raw: mixed, meta: array<string,mixed>}
     */
    public function fetchAntrian(array $query = [], bool $debug = false): array
    {
        $headers = [
            // Some endpoints return HTML/encrypted payload unless you look like an XHR client.
            'Accept' => 'application/json, text/plain, */*',
            'User-Agent' => 'CI4-Antrian/1.0',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => rtrim($this->config->baseUrl, '/') . '/',
        ];
        if ($this->config->token !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->config->token;
        }

        $path = ltrim($this->config->endpoint, '/');

        $mergedQuery = array_merge($this->fixedQuery, $query);
        $attempts = 0;
        $resp = null;
        $lastErr = null;
        while ($attempts < 2) {
            $attempts++;
            try {
                $resp = $this->http->get($path, [
                    'headers' => $headers,
                    'query'   => $mergedQuery,
                    // Important: do not follow redirects silently.
                    // If office API is behind captive portal/WAF, we want to see 3xx + Location.
                    'allow_redirects' => false,
                ]);
                $lastErr = null;
                break;
            } catch (\Throwable $e) {
                $lastErr = $e;
                // simple retry on first failure (network/timeout)
                if ($attempts >= 2) break;
                usleep(200_000);
            }
        }

        if ($resp === null) {
            $msg = $lastErr ? $lastErr->getMessage() : 'unknown error';
            throw new \RuntimeException('Office API request failed: ' . $msg);
        }

        $status = $resp->getStatusCode();
        $body = $resp->getBody();
        $location = $resp->getHeaderLine('Location');

        if ($status >= 300 && $status < 400) {
            $msg = "Office API HTTP $status (redirect).";
            if ($location !== '') {
                $msg .= " Location: " . $location;
            }
            throw new \RuntimeException($msg);
        }

        if ($status < 200 || $status >= 300) {
            $preview = substr((string) $body, 0, 300);
            throw new \RuntimeException("Office API HTTP $status. Body: " . $preview);
        }

        if (trim((string) $body) === '') {
            throw new \RuntimeException('Office API returned empty response body');
        }

        $bodyTrim = ltrim((string) $body);
        if ($bodyTrim !== '' && ($bodyTrim[0] === '<' || str_contains(strtolower($bodyTrim), '<html'))) {
            $preview = substr((string) $bodyTrim, 0, 300);
            throw new \RuntimeException('Office API returned HTML (not JSON). Body: ' . $preview);
        }

        $raw = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $preview = substr((string) $body, 0, 300);
            // Common pattern: API returns encrypted/obfuscated HTML (includes aes.js + decrypt helpers)
            if (str_contains((string) $body, '/aes.js') || str_contains((string) $body, 'toNumbers(')) {
                throw new \RuntimeException(
                    'Office API returned encrypted HTML, not JSON. Ask office for server-to-server JSON endpoint/token or the decryption contract. Body: ' . $preview
                );
            }

            throw new \RuntimeException('Office API invalid JSON: ' . json_last_error_msg() . '. Body: ' . $preview);
        }

        $items = $this->extractItems($raw);

        $meta = [
            'attempts' => $attempts,
            'query' => $mergedQuery,
            'status' => $status,
        ];

        if ($debug) {
            $meta['raw_preview'] = substr((string) $body, 0, 1500);
            $meta['location'] = $location !== '' ? $location : null;
        }

        return [
            'items' => is_array($items) ? $items : [],
            'raw'   => $raw,
            'meta'  => $meta,
        ];
    }

    /**
     * Heuristic extraction of list node from various API response shapes.
     *
     * @return array<int,array<string,mixed>>
     */
    private function extractItems(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $expectedKeys = ['no_uji', 'nouji', 'nama_pemilik', 'no_antrian', 'id_daftar', 'id_hasil_uji'];

        $looksLikeRowList = static function (array $list) use ($expectedKeys): bool {
            if ($list === [] || !array_is_list($list)) return false;
            $first = $list[0] ?? null;
            if (!is_array($first)) return false;
            $firstLower = array_change_key_case($first, CASE_LOWER);
            foreach ($expectedKeys as $k) {
                if (array_key_exists($k, $firstLower)) {
                    return true;
                }
            }
            // if it is a list of arrays, accept as last resort
            return true;
        };

        // 1) direct list
        if (array_is_list($raw) && $looksLikeRowList($raw)) {
            return $raw;
        }

        // 2) common wrappers
        $candidates = [];
        foreach (['data', 'result', 'items', 'rows', 'records'] as $k) {
            if (isset($raw[$k]) && is_array($raw[$k])) {
                $candidates[] = $raw[$k];
            }
        }

        // 3) nested common wrappers
        foreach (['data', 'result'] as $k) {
            if (isset($raw[$k]) && is_array($raw[$k])) {
                foreach (['data', 'items', 'rows', 'records', 'result'] as $kk) {
                    if (isset($raw[$k][$kk]) && is_array($raw[$k][$kk])) {
                        $candidates[] = $raw[$k][$kk];
                    }
                }
            }
        }

        foreach ($candidates as $cand) {
            if (is_array($cand) && array_is_list($cand) && $looksLikeRowList($cand)) {
                return $cand;
            }
        }

        // 4) deep scan (depth-limited)
        $queue = [[$raw, 0]];
        while ($queue !== []) {
            [$node, $depth] = array_shift($queue);
            if (!is_array($node) || $depth > 4) continue;

            if (array_is_list($node) && $looksLikeRowList($node)) {
                return $node;
            }

            foreach ($node as $v) {
                if (is_array($v)) {
                    $queue[] = [$v, $depth + 1];
                }
            }
        }

        return [];
    }
}
