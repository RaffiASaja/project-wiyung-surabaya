<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class OfficeApi extends BaseConfig
{
    /**
     * Enable pulling data from Office API and syncing into local DB.
     */
    public bool $enabled = false;

    /**
     * Base URL, e.g. https://api.kantor.local/
     */
    public string $baseUrl = '';

    /**
     * Endpoint path, e.g. /antrian or /api/v1/antrian
     */
    public string $endpoint = '';

    /**
     * Optional bearer token.
     */
    public string $token = '';

    /**
     * Timeout seconds.
     */
    public int $timeout = 10;

    /**
     * If true, SSL verification will be disabled (use only for internal dev).
     */
    public bool $verifySsl = true;
}

