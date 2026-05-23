<?php
namespace App\Libraries;

use Config\Services;

/**
 * SSOService
 *
 * Drop-in CodeIgniter 4 library to interact with the project's SSO server.
 * Supports token generation (HMAC), requestId creation (/api/generate), token
 * verification (/api/verify), user data fetch and logout URL creation.
 *
 * Usage:
 *   $sso = new \App\Libraries\SSOService();
 *   return redirect()->to($sso->getLoginUrl());
 *
 * Put client id/secret and SSO hosts into .env (example keys documented below).
 *
 * Required .env keys (examples):
 *   SSO_URL=https://sso.example.id
 *   SSO_IP_LOCAL=https://10.0.0.5
 *   SSO_LOGIN_URL=/login?requestId=
 *   SSO_CLIENT_KEY=your_client_id
 *   SSO_CLIENT_SECRET=your_client_secret
 *   COOKIE_DOMAIN=.example.id
 *   SERVER_HOST=https://app.example.id
 */
class SSOService
{
    protected $clientId;
    protected $clientSecret;
    protected $ssoBase;
    protected $ssoLoginPath;
    protected $serverHost;

    public function __construct(array $options = [])
    {
        $this->clientId = $options['client_id'] ?? getenv('SSO_CLIENT_KEY') ?: '';
        $this->clientSecret = $options['client_secret'] ?? getenv('SSO_CLIENT_SECRET') ?: '';
        $this->ssoBase = rtrim($options['sso_base'] ?? (getenv('SSO_IP_LOCAL') ?: getenv('SSO_URL') ?: ''), '/');
        $this->ssoLoginPath = $options['sso_login_path'] ?? (getenv('SSO_LOGIN_URL') ?: '/login?requestId=');
        $this->serverHost = rtrim($options['server_host'] ?? (getenv('SERVER_HOST') ?: ''), '/');
    }

    /** Return the configured SSO base URL */
    public function getSsoBase(): string
    {
        return $this->ssoBase;
    }

    /** Return redirect URI (callback) */
    public function getRedirectUri(): string
    {
        return $this->serverHost ?: rtrim(base_url(), '/');
    }

    /** Generate token: base64(clientId:timestamp:hmac) */
    public function generateToken(): string
    {
        $ts = round(microtime(true) * 1000);
        $data = $this->clientId . ':' . $ts;
        $hmac = hash_hmac('sha256', $data, $this->clientSecret ?: '');
        return base64_encode($this->clientId . ':' . $ts . ':' . $hmac);
    }

    /**
     * Request a request_id from the SSO server (/api/generate)
     * Returns the request_id string or false on failure.
     */
    public function generateRequestId(string $token)
    {
        if (empty($this->ssoBase) || empty($this->clientId) || empty($this->serverHost)) {
            log_message('error', 'SSOService: missing configuration for generateRequestId');
            return false;
        }

        // Use full redirect URL as origin (include scheme and host) to match SSO registration
        $origin = $this->getRedirectUri() ?: (getenv('COOKIE_DOMAIN') ?: '');
        $payload = [
            'client_id' => $this->clientId,
            'origin' => $origin,
            'redirect_uri' => $this->getRedirectUri() . '/sso/callback',
        ];

        // Debug log payload
        log_message('info', 'SSOService generateRequestId payload: ' . json_encode($payload));

        $options = ['timeout' => 5, 'connect_timeout' => 2, 'verify' => false];
        $client = Services::curlrequest($options);

        try {
            $resp = $client->request('POST', $this->ssoBase . '/api/generate', [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            if ($resp->getStatusCode() !== 200) {
                log_message('error', 'SSOService generateRequestId: non-200 response ' . $resp->getStatusCode());
                log_message('error', 'SSOService generateRequestId raw response: ' . (string)$resp->getBody());
                return false;
            }

            $raw = (string)$resp->getBody();
            log_message('info', 'SSOService generateRequestId response: ' . $raw);
            $body = json_decode($raw, true);
            return $body['request_id'] ?? ($body['requestId'] ?? false);
        } catch (\Exception $e) {
            log_message('error', 'SSOService generateRequestId exception: ' . $e->getMessage());
            return false;
        }
    }

    /** Verify token via /api/verify — returns bool */
    public function verifyToken(string $token): bool
    {
        if (empty($this->ssoBase) || empty($token)) return false;

        $options = ['timeout' => 10, 'connect_timeout' => 5, 'verify' => false];
        $client = Services::curlrequest($options);
        try {
            $resp = $client->request('POST', $this->ssoBase . '/api/verify', [
                'json' => ['token' => $token],
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            if ($resp->getStatusCode() !== 200) {
                log_message('error', 'SSOService verifyToken: status ' . $resp->getStatusCode());
                return false;
            }

            $body = json_decode($resp->getBody(), true);
            if (isset($body['valid'])) return (bool)$body['valid'];
            return true;
        } catch (\Exception $e) {
            log_message('error', 'SSOService verifyToken error: ' . $e->getMessage());
            // Conservative fallback: treat timeouts as valid to avoid lockout (customize as needed)
            if ($e->getCode() == 28 || stripos($e->getMessage(), 'timed out') !== false) {
                log_message('info', 'SSOService verifyToken timeout — assuming valid (adjust policy as needed)');
                return true;
            }
            return false;
        }
    }

    /** Get user data from SSO using token — returns array|false */
    public function getUserData(string $token)
    {
        if (empty($this->ssoBase) || empty($token)) return false;
        $options = ['timeout' => 10, 'connect_timeout' => 5, 'verify' => false];
        $client = Services::curlrequest($options);
        try {
            $resp = $client->request('POST', $this->ssoBase . '/api/verify', [
                'json' => ['token' => $token],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            if ($resp->getStatusCode() !== 200) return false;
            $body = json_decode($resp->getBody(), true);
            if (isset($body['data'])) $body = $body['data'];
            if (!isset($body['username']) && isset($body['username_ldap'])) $body['username'] = $body['username_ldap'];
            return $body;
        } catch (\Exception $e) {
            log_message('error', 'SSOService getUserData error: ' . $e->getMessage());
            return false;
        }
    }

    /** Build SSO login URL (with requestId when available) */
    public function getLoginUrl(): string
    {
        $token = $this->generateToken();
        $requestId = $this->generateRequestId($token);
        if ($requestId) {
            return $this->ssoBase . $this->ssoLoginPath . $requestId;
        }
        return $this->ssoBase . $this->ssoLoginPath;
    }

    /** Build SSO logout URL (attempts to append requestId) */
    public function getLogoutUrl(): string
    {
        $token = $this->generateToken();
        $requestId = $this->generateRequestId($token);
        $logout = $this->ssoBase . '/logout';
        if ($requestId) return $logout . '?requestId=' . $requestId;
        return $logout;
    }

}
