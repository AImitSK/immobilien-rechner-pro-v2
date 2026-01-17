<?php
/**
 * GitHub Updater Class
 * Handles plugin updates from GitHub releases
 */

if (!defined('ABSPATH')) {
    exit;
}

class IRP_GitHub_Updater {

    private string $plugin_slug;
    private string $plugin_basename;
    private string $github_repo;
    private ?object $github_response = null;

    public function __construct() {
        $this->plugin_slug = 'immobilien-rechner-pro';
        $this->plugin_basename = IRP_PLUGIN_BASENAME;
        $this->github_repo = IRP_GITHUB_REPO;

        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);

        // AJAX handlers for manual update check
        add_action('wp_ajax_irp_check_updates', [$this, 'ajax_check_updates']);
        add_action('wp_ajax_irp_get_update_status', [$this, 'ajax_get_update_status']);
    }

    /**
     * Get GitHub release info
     */
    private function get_github_release(): ?object {
        if ($this->github_response !== null) {
            return $this->github_response;
        }

        // Check transient first
        $transient = get_transient('irp_github_release');
        if ($transient !== false) {
            $this->github_response = $transient;
            return $this->github_response;
        }

        // Fetch from GitHub API
        $response = wp_remote_get(
            "https://api.github.com/repos/{$this->github_repo}/releases/latest",
            [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                ],
                'timeout' => 10,
            ]
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $release = json_decode($body);

        if (!$release || !isset($release->tag_name)) {
            return null;
        }

        $this->github_response = $release;

        // Cache for 6 hours
        set_transient('irp_github_release', $release, 6 * HOUR_IN_SECONDS);

        return $this->github_response;
    }

    /**
     * Get remote version from GitHub
     */
    private function get_remote_version(): ?string {
        $release = $this->get_github_release();
        if (!$release) {
            return null;
        }

        // Remove 'v' prefix if present
        return ltrim($release->tag_name, 'v');
    }

    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_version = $this->get_remote_version();
        if (!$remote_version) {
            return $transient;
        }

        if (version_compare(IRP_VERSION, $remote_version, '<')) {
            $release = $this->get_github_release();

            // Find zip asset
            $download_url = $this->get_download_url($release);

            if ($download_url) {
                $transient->response[$this->plugin_basename] = (object) [
                    'slug' => $this->plugin_slug,
                    'plugin' => $this->plugin_basename,
                    'new_version' => $remote_version,
                    'url' => "https://github.com/{$this->github_repo}",
                    'package' => $download_url,
                    'icons' => [],
                    'banners' => [],
                    'requires' => '6.0',
                    'tested' => get_bloginfo('version'),
                    'requires_php' => '7.4',
                ];
            }
        }

        return $transient;
    }

    /**
     * Get download URL from release
     */
    private function get_download_url(object $release): ?string {
        // Check for zip asset in release assets
        if (!empty($release->assets)) {
            foreach ($release->assets as $asset) {
                if (substr($asset->name, -4) === '.zip') {
                    return $asset->browser_download_url;
                }
            }
        }

        // Fallback to source zip
        return $release->zipball_url ?? null;
    }

    /**
     * Provide plugin info for WordPress
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $release = $this->get_github_release();
        if (!$release) {
            return $result;
        }

        $remote_version = $this->get_remote_version();

        return (object) [
            'name' => 'Immobilien Rechner Pro',
            'slug' => $this->plugin_slug,
            'version' => $remote_version,
            'author' => '<a href="https://sk-online-marketing.de">Stefan Kühne</a>',
            'author_profile' => 'https://sk-online-marketing.de',
            'homepage' => "https://github.com/{$this->github_repo}",
            'requires' => '6.0',
            'tested' => get_bloginfo('version'),
            'requires_php' => '7.4',
            'downloaded' => 0,
            'last_updated' => $release->published_at ?? '',
            'sections' => [
                'description' => 'Professionelles WordPress-Plugin für Mietwertberechnung und Verkaufen-vs-Vermieten-Vergleich. White-Label-Lösung für Immobilienmakler.',
                'changelog' => $this->format_changelog($release->body ?? ''),
            ],
            'download_link' => $this->get_download_url($release),
        ];
    }

    /**
     * Format changelog from GitHub release notes
     */
    private function format_changelog(string $body): string {
        if (empty($body)) {
            return '<p>Keine Änderungsdetails verfügbar.</p>';
        }

        // Convert markdown to basic HTML
        $html = nl2br(esc_html($body));
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/((?:<li>.*<\/li>\s*)+)/', '<ul>$1</ul>', $html);

        return $html;
    }

    /**
     * After install, rename folder if needed
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_basename) {
            return $result;
        }

        $plugin_folder = WP_PLUGIN_DIR . '/' . $this->plugin_slug;

        // Move to correct folder if needed
        if ($result['destination'] !== $plugin_folder) {
            $wp_filesystem->move($result['destination'], $plugin_folder);
            $result['destination'] = $plugin_folder;
        }

        // Reactivate plugin
        activate_plugin($this->plugin_basename);

        return $result;
    }

    /**
     * AJAX: Check for updates manually
     */
    public function ajax_check_updates(): void {
        check_ajax_referer('irp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Keine Berechtigung.', 'immobilien-rechner-pro')]);
        }

        // Clear cached release info
        delete_transient('irp_github_release');
        $this->github_response = null;

        $remote_version = $this->get_remote_version();
        $current_version = IRP_VERSION;

        if (!$remote_version) {
            wp_send_json_error([
                'message' => __('Verbindung zu GitHub fehlgeschlagen.', 'immobilien-rechner-pro'),
            ]);
        }

        $update_available = version_compare($current_version, $remote_version, '<');
        $release = $this->get_github_release();

        wp_send_json_success([
            'current_version' => $current_version,
            'remote_version' => $remote_version,
            'update_available' => $update_available,
            'changelog' => $release->body ?? '',
            'download_url' => $update_available ? admin_url('update-core.php') : null,
            'message' => $update_available
                ? sprintf(__('Update verfügbar: Version %s', 'immobilien-rechner-pro'), $remote_version)
                : __('Sie verwenden die neueste Version.', 'immobilien-rechner-pro'),
        ]);
    }

    /**
     * AJAX: Get current update status
     */
    public function ajax_get_update_status(): void {
        check_ajax_referer('irp_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Keine Berechtigung.', 'immobilien-rechner-pro')]);
        }

        $remote_version = $this->get_remote_version();
        $current_version = IRP_VERSION;

        wp_send_json_success([
            'current_version' => $current_version,
            'remote_version' => $remote_version,
            'update_available' => $remote_version ? version_compare($current_version, $remote_version, '<') : false,
        ]);
    }
}
