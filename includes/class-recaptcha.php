<?php
/**
 * reCAPTCHA v3 validation handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_Recaptcha {

    private string $secret_key;
    private float $threshold;

    public function __construct() {
        $settings = get_option('irp_settings', []);
        $this->secret_key = $settings['recaptcha_secret_key'] ?? '';
        $this->threshold = (float) ($settings['recaptcha_threshold'] ?? 0.5);
    }

    /**
     * Check if reCAPTCHA is configured
     */
    public function is_configured(): bool {
        $settings = get_option('irp_settings', []);
        return !empty($settings['recaptcha_site_key']) && !empty($settings['recaptcha_secret_key']);
    }

    /**
     * Get the site key for frontend
     */
    public function get_site_key(): string {
        $settings = get_option('irp_settings', []);
        return $settings['recaptcha_site_key'] ?? '';
    }

    /**
     * Verify a reCAPTCHA token
     *
     * @param string $token The reCAPTCHA token from frontend
     * @param string $expected_action The expected action name
     * @return array{success: bool, score: float, error: string|null}
     */
    public function verify(string $token, string $expected_action = 'submit'): array {
        // If not configured, skip verification
        if (!$this->is_configured()) {
            return [
                'success' => true,
                'score' => 1.0,
                'error' => null,
                'skipped' => true,
            ];
        }

        if (empty($token)) {
            return [
                'success' => false,
                'score' => 0,
                'error' => __('reCAPTCHA-Token fehlt.', 'immobilien-rechner-pro'),
            ];
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'timeout' => 10,
            'body' => [
                'secret' => $this->secret_key,
                'response' => $token,
                'remoteip' => $this->get_client_ip(),
            ],
        ]);

        if (is_wp_error($response)) {
            // On network error, allow through but log
            error_log('IRP reCAPTCHA verification failed: ' . $response->get_error_message());
            return [
                'success' => true,
                'score' => 0.5,
                'error' => null,
                'network_error' => true,
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!$body || !isset($body['success'])) {
            return [
                'success' => false,
                'score' => 0,
                'error' => __('Ungültige reCAPTCHA-Antwort.', 'immobilien-rechner-pro'),
            ];
        }

        if (!$body['success']) {
            $error_codes = $body['error-codes'] ?? [];
            return [
                'success' => false,
                'score' => 0,
                'error' => $this->get_error_message($error_codes),
            ];
        }

        $score = (float) ($body['score'] ?? 0);
        $action = $body['action'] ?? '';

        // Verify action matches (if provided)
        if ($expected_action && $action !== $expected_action) {
            return [
                'success' => false,
                'score' => $score,
                'error' => __('reCAPTCHA-Aktion stimmt nicht überein.', 'immobilien-rechner-pro'),
            ];
        }

        // Check score threshold
        if ($score < $this->threshold) {
            return [
                'success' => false,
                'score' => $score,
                'error' => __('reCAPTCHA-Score zu niedrig. Bitte versuchen Sie es erneut.', 'immobilien-rechner-pro'),
            ];
        }

        return [
            'success' => true,
            'score' => $score,
            'error' => null,
        ];
    }

    /**
     * Get client IP address
     */
    private function get_client_ip(): string {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '';
    }

    /**
     * Get human-readable error message
     */
    private function get_error_message(array $error_codes): string {
        $messages = [
            'missing-input-secret' => __('Server-Konfigurationsfehler.', 'immobilien-rechner-pro'),
            'invalid-input-secret' => __('Server-Konfigurationsfehler.', 'immobilien-rechner-pro'),
            'missing-input-response' => __('reCAPTCHA-Token fehlt.', 'immobilien-rechner-pro'),
            'invalid-input-response' => __('Ungültiges reCAPTCHA-Token.', 'immobilien-rechner-pro'),
            'bad-request' => __('Ungültige Anfrage.', 'immobilien-rechner-pro'),
            'timeout-or-duplicate' => __('reCAPTCHA abgelaufen. Bitte erneut versuchen.', 'immobilien-rechner-pro'),
        ];

        foreach ($error_codes as $code) {
            if (isset($messages[$code])) {
                return $messages[$code];
            }
        }

        return __('reCAPTCHA-Validierung fehlgeschlagen.', 'immobilien-rechner-pro');
    }
}
