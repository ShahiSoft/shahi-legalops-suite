# TASK 2.5: Cookie Scanner

**Phase:** 2 (Consent Management - CORE)  
**Effort:** 8-10 hours  
**Prerequisites:** TASK 2.4 complete (Banner exists)  
**Next Task:** [task-2.6-consent-preferences-ui.md](task-2.6-consent-preferences-ui.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 2.5 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Create an automated cookie scanner that detects all cookies set by the website, categorizes them
by purpose, and helps administrators manage which cookies require consent. This integrates with
the consent banner to block/allow cookies based on user preferences.

This is essential for GDPR/CCPA compliance as it provides transparency about cookie usage.

INPUT STATE (verify these exist):
✅ Consent banner working (Task 2.4)
✅ Consent Service and REST API
✅ Database table slos_consent

YOUR TASK:

1. **Create Cookie_Scanner Service**

Location: `includes/Services/Cookie_Scanner.php`

```php
<?php
/**
 * Cookie Scanner Service
 * Detects and categorizes cookies on the website.
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\Services;

class Cookie_Scanner extends Base_Service {

    /**
     * Known cookie patterns
     */
    private $cookie_patterns = [
        'analytics' => [
            '_ga', '_gid', '_gat', '__utm', '_hjid', '_pk_id',
        ],
        'marketing' => [
            '_fbp', 'fr', 'IDE', 'DSID', 'test_cookie', 'NID',
        ],
        'advertising' => [
            'IDE', 'ANID', '__gads', '_gcl_au',
        ],
        'functional' => [
            'PHPSESSID', 'wp-settings', 'wordpress_logged_in',
        ],
    ];

    /**
     * Scan cookies on site
     *
     * @return array Detected cookies
     */
    public function scan_site() {
        $cookies = [];

        // Scan current page cookies
        $cookies = array_merge( $cookies, $this->scan_browser_cookies() );

        // Scan JavaScript-set cookies
        $cookies = array_merge( $cookies, $this->scan_js_cookies() );

        // Scan third-party scripts
        $cookies = array_merge( $cookies, $this->scan_third_party_scripts() );

        // Categorize cookies
        $categorized = $this->categorize_cookies( $cookies );

        // Save to database
        $this->save_scan_results( $categorized );

        return $categorized;
    }

    /**
     * Scan browser cookies
     *
     * @return array Cookies
     */
    private function scan_browser_cookies() {
        $cookies = [];

        if ( isset( $_COOKIE ) && is_array( $_COOKIE ) ) {
            foreach ( $_COOKIE as $name => $value ) {
                $cookies[] = [
                    'name' => $name,
                    'value' => $value,
                    'domain' => $this->get_cookie_domain(),
                    'path' => '/',
                    'source' => 'server',
                ];
            }
        }

        return $cookies;
    }

    /**
     * Scan JavaScript-set cookies
     *
     * @return array Cookies (detected from scripts)
     */
    private function scan_js_cookies() {
        $cookies = [];

        // Get all enqueued scripts
        global $wp_scripts;
        
        if ( ! $wp_scripts instanceof \WP_Scripts ) {
            return $cookies;
        }

        foreach ( $wp_scripts->queue as $handle ) {
            $script_src = $wp_scripts->registered[ $handle ]->src ?? '';
            
            // Check if script sets cookies
            if ( $this->script_sets_cookies( $script_src ) ) {
                $detected = $this->detect_cookies_from_script( $script_src );
                $cookies = array_merge( $cookies, $detected );
            }
        }

        return $cookies;
    }

    /**
     * Scan third-party scripts
     *
     * @return array Cookies
     */
    private function scan_third_party_scripts() {
        $cookies = [];

        // Common third-party services
        $third_party_services = [
            'google-analytics' => [
                'pattern' => 'google-analytics.com|googletagmanager.com',
                'cookies' => [ '_ga', '_gid', '_gat', '__utma', '__utmb', '__utmc', '__utmz' ],
                'category' => 'analytics',
            ],
            'facebook-pixel' => [
                'pattern' => 'facebook.com/tr|connect.facebook.net',
                'cookies' => [ '_fbp', 'fr' ],
                'category' => 'marketing',
            ],
            'google-ads' => [
                'pattern' => 'googleadservices.com|doubleclick.net',
                'cookies' => [ 'IDE', 'DSID', 'test_cookie', '__gads' ],
                'category' => 'advertising',
            ],
        ];

        // Check if scripts are enqueued
        global $wp_scripts;
        
        foreach ( $wp_scripts->queue as $handle ) {
            $script_src = $wp_scripts->registered[ $handle ]->src ?? '';
            
            foreach ( $third_party_services as $service => $config ) {
                if ( preg_match( '/' . $config['pattern'] . '/i', $script_src ) ) {
                    foreach ( $config['cookies'] as $cookie_name ) {
                        $cookies[] = [
                            'name' => $cookie_name,
                            'value' => '',
                            'domain' => $this->extract_domain( $script_src ),
                            'category' => $config['category'],
                            'source' => $service,
                        ];
                    }
                }
            }
        }

        return $cookies;
    }

    /**
     * Categorize cookies
     *
     * @param array $cookies Cookies to categorize.
     * @return array Categorized cookies
     */
    private function categorize_cookies( $cookies ) {
        $categorized = [
            'functional' => [],
            'analytics' => [],
            'marketing' => [],
            'advertising' => [],
            'uncategorized' => [],
        ];

        foreach ( $cookies as $cookie ) {
            $category = $this->detect_category( $cookie['name'] );
            $cookie['category'] = $category;
            $categorized[ $category ][] = $cookie;
        }

        return $categorized;
    }

    /**
     * Detect cookie category
     *
     * @param string $cookie_name Cookie name.
     * @return string Category
     */
    private function detect_category( $cookie_name ) {
        foreach ( $this->cookie_patterns as $category => $patterns ) {
            foreach ( $patterns as $pattern ) {
                if ( strpos( $cookie_name, $pattern ) !== false ) {
                    return $category;
                }
            }
        }

        return 'uncategorized';
    }

    /**
     * Save scan results
     *
     * @param array $cookies Categorized cookies.
     */
    private function save_scan_results( $cookies ) {
        update_option( 'slos_cookie_scan_results', [
            'cookies' => $cookies,
            'scanned_at' => current_time( 'mysql' ),
            'total_count' => $this->count_total_cookies( $cookies ),
        ] );

        // Save individual cookie records
        $this->save_cookie_definitions( $cookies );
    }

    /**
     * Save cookie definitions
     *
     * @param array $cookies Categorized cookies.
     */
    private function save_cookie_definitions( $cookies ) {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_cookie_definitions';

        // Create table if not exists
        $this->create_cookie_definitions_table();

        foreach ( $cookies as $category => $cookie_list ) {
            foreach ( $cookie_list as $cookie ) {
                $wpdb->replace( $table, [
                    'cookie_name' => $cookie['name'],
                    'category' => $cookie['category'] ?? $category,
                    'domain' => $cookie['domain'] ?? '',
                    'source' => $cookie['source'] ?? 'unknown',
                    'description' => $this->get_cookie_description( $cookie['name'] ),
                    'duration' => $this->get_cookie_duration( $cookie['name'] ),
                    'is_blocked' => $category !== 'functional' ? 1 : 0,
                ] );
            }
        }
    }

    /**
     * Create cookie definitions table
     */
    private function create_cookie_definitions_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_cookie_definitions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            cookie_name varchar(255) NOT NULL,
            category varchar(50) NOT NULL,
            domain varchar(255) DEFAULT '',
            source varchar(100) DEFAULT 'unknown',
            description text DEFAULT NULL,
            duration varchar(100) DEFAULT NULL,
            is_blocked tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY cookie_name (cookie_name)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Get cookie description
     *
     * @param string $cookie_name Cookie name.
     * @return string Description
     */
    private function get_cookie_description( $cookie_name ) {
        $descriptions = [
            '_ga' => 'Google Analytics cookie used to distinguish users',
            '_gid' => 'Google Analytics cookie used to distinguish users',
            '_gat' => 'Google Analytics cookie used to throttle request rate',
            '_fbp' => 'Facebook Pixel cookie for ad targeting',
            'PHPSESSID' => 'PHP session cookie',
            'wordpress_logged_in_*' => 'WordPress authentication cookie',
        ];

        return $descriptions[ $cookie_name ] ?? 'No description available';
    }

    /**
     * Get cookie duration
     *
     * @param string $cookie_name Cookie name.
     * @return string Duration
     */
    private function get_cookie_duration( $cookie_name ) {
        $durations = [
            '_ga' => '2 years',
            '_gid' => '24 hours',
            '_gat' => '1 minute',
            '_fbp' => '3 months',
            'PHPSESSID' => 'Session',
        ];

        return $durations[ $cookie_name ] ?? 'Unknown';
    }

    /**
     * Get cookie domain
     *
     * @return string Domain
     */
    private function get_cookie_domain() {
        return parse_url( home_url(), PHP_URL_HOST );
    }

    /**
     * Extract domain from URL
     *
     * @param string $url URL.
     * @return string Domain
     */
    private function extract_domain( $url ) {
        $parsed = parse_url( $url );
        return $parsed['host'] ?? '';
    }

    /**
     * Check if script sets cookies
     *
     * @param string $script_src Script source URL.
     * @return bool
     */
    private function script_sets_cookies( $script_src ) {
        // Known cookie-setting scripts
        $cookie_scripts = [
            'google-analytics.com',
            'googletagmanager.com',
            'facebook.com',
            'doubleclick.net',
        ];

        foreach ( $cookie_scripts as $pattern ) {
            if ( strpos( $script_src, $pattern ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect cookies from script
     *
     * @param string $script_src Script source.
     * @return array Cookies
     */
    private function detect_cookies_from_script( $script_src ) {
        // This is a simplified version
        // In production, you'd analyze script content
        return [];
    }

    /**
     * Count total cookies
     *
     * @param array $cookies Categorized cookies.
     * @return int Count
     */
    private function count_total_cookies( $cookies ) {
        $count = 0;
        foreach ( $cookies as $category_cookies ) {
            $count += count( $category_cookies );
        }
        return $count;
    }

    /**
     * Get scan results
     *
     * @return array|false Scan results
     */
    public function get_scan_results() {
        return get_option( 'slos_cookie_scan_results', false );
    }

    /**
     * Schedule automatic scans
     */
    public function schedule_scans() {
        if ( ! wp_next_scheduled( 'slos_cookie_scan' ) ) {
            wp_schedule_event( time(), 'daily', 'slos_cookie_scan' );
        }
    }

    /**
     * Run scheduled scan
     */
    public function run_scheduled_scan() {
        $this->scan_site();
    }
}
```

2. **Create REST API endpoint for scanner**

Location: `includes/API/Cookie_Scanner_Controller.php`

```php
<?php
/**
 * Cookie Scanner REST Controller
 *
 * @package Shahi\LegalOps
 * @since 3.0.0
 */

namespace Shahi\LegalOps\API;

use Shahi\LegalOps\Services\Cookie_Scanner;
use WP_REST_Request;
use WP_REST_Response;

class Cookie_Scanner_Controller extends Base_REST_Controller {

    protected $namespace = 'slos/v1';
    protected $rest_base = 'cookies';

    /**
     * Register routes
     */
    public function register_routes() {
        // Scan cookies
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/scan', [
            [
                'methods' => 'POST',
                'callback' => [ $this, 'scan_cookies' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ] );

        // Get scan results
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/scan-results', [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get_scan_results' ],
                'permission_callback' => [ $this, 'admin_permissions_check' ],
            ],
        ] );

        // Get cookie definitions
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/definitions', [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get_definitions' ],
                'permission_callback' => '__return_true',
            ],
        ] );
    }

    /**
     * Scan cookies
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function scan_cookies( $request ) {
        $scanner = new Cookie_Scanner();
        $results = $scanner->scan_site();

        return new WP_REST_Response( [
            'success' => true,
            'data' => $results,
        ], 200 );
    }

    /**
     * Get scan results
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_scan_results( $request ) {
        $scanner = new Cookie_Scanner();
        $results = $scanner->get_scan_results();

        if ( ! $results ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'No scan results found',
            ], 404 );
        }

        return new WP_REST_Response( [
            'success' => true,
            'data' => $results,
        ], 200 );
    }

    /**
     * Get cookie definitions
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_definitions( $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_cookie_definitions';

        $cookies = $wpdb->get_results( "SELECT * FROM $table ORDER BY category, cookie_name", ARRAY_A );

        return new WP_REST_Response( [
            'success' => true,
            'data' => [
                'cookies' => $cookies,
                'count' => count( $cookies ),
            ],
        ], 200 );
    }
}
```

3. **Add cron job for scheduled scans**

In `includes/Core/Cron_Handler.php`, add:

```php
// Schedule cookie scans
add_action( 'slos_cookie_scan', [ $this, 'run_cookie_scan' ] );

public function run_cookie_scan() {
    $scanner = new \Shahi\LegalOps\Services\Cookie_Scanner();
    $scanner->run_scheduled_scan();
}
```

4. **Test cookie scanner**

```bash
# Trigger manual scan via WP-CLI
wp eval "
    \$scanner = new \Shahi\LegalOps\Services\Cookie_Scanner();
    \$results = \$scanner->scan_site();
    print_r(\$results);
"

# Via REST API
curl -X POST http://localhost/wp-json/slos/v1/cookies/scan \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get scan results
curl http://localhost/wp-json/slos/v1/cookies/scan-results \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get cookie definitions
curl http://localhost/wp-json/slos/v1/cookies/definitions
```

5. **Verify database**

```sql
SELECT * FROM wp_slos_cookie_definitions;
```

OUTPUT STATE:
✅ Cookie_Scanner service (auto-detects cookies)
✅ REST API endpoints (/scan, /scan-results, /definitions)
✅ Cookie categorization (functional, analytics, marketing, advertising)
✅ Cookie definitions table
✅ Scheduled daily scans
✅ Third-party script detection

VERIFICATION:

1. **Scan works:**
```bash
# Should return categorized cookies
wp eval "\$scanner = new \Shahi\LegalOps\Services\Cookie_Scanner(); print_r(\$scanner->scan_site());"
```

2. **Database populated:**
```sql
SELECT category, COUNT(*) as count FROM wp_slos_cookie_definitions GROUP BY category;
```

3. **REST API works:**
```bash
curl http://localhost/wp-json/slos/v1/cookies/definitions | jq
```

SUCCESS CRITERIA:
✅ Cookie scanner detects cookies
✅ Cookies categorized correctly
✅ Database table created and populated
✅ REST API endpoints working
✅ Scheduled scans configured
✅ Third-party cookies detected

ROLLBACK:
```bash
rm includes/Services/Cookie_Scanner.php
rm includes/API/Cookie_Scanner_Controller.php
wp db query "DROP TABLE wp_slos_cookie_definitions"
```

TROUBLESHOOTING:

**Problem 1: No cookies detected**
- Check if site has cookies: document.cookie in browser console
- Verify $_COOKIE populated: print_r($_COOKIE)

**Problem 2: Table not created**
- Run manually: $scanner->create_cookie_definitions_table()
- Check database permissions

**Problem 3: Third-party cookies not detected**
- Verify scripts enqueued: global $wp_scripts; print_r($wp_scripts->queue)

COMMIT MESSAGE:
```
feat(consent): Add cookie scanner

- Create Cookie_Scanner service
- Auto-detect browser and JS cookies
- Categorize cookies (functional, analytics, marketing)
- Create cookie_definitions table
- Add REST API endpoints (/scan, /scan-results, /definitions)
- Schedule daily automatic scans
- Detect third-party scripts (GA, FB, Google Ads)
- Store cookie descriptions and durations

Cookie transparency ready.

Task: 2.5 (8-10 hours)
Next: Task 2.6 - Consent Preferences UI
```

WHAT TO REPORT BACK:
"✅ TASK 2.5 COMPLETE

Created:
- Cookie_Scanner service
- Cookie_Scanner_Controller (REST API)
- slos_cookie_definitions table

Implemented:
- ✅ Automatic cookie detection
- ✅ Cookie categorization (4 categories)
- ✅ Third-party script detection (GA, FB, Ads)
- ✅ REST API endpoints (scan, results, definitions)
- ✅ Scheduled daily scans
- ✅ Cookie descriptions and durations
- ✅ Database storage

Verification passed:
- ✅ Scanner detects cookies
- ✅ Categorization correct
- ✅ API endpoints working
- ✅ Database populated
- ✅ Cron scheduled

📍 Ready for TASK 2.6: [task-2.6-consent-preferences-ui.md](task-2.6-consent-preferences-ui.md)"
```

---

## ✅ COMPLETION CHECKLIST

- [ ] Cookie_Scanner.php created
- [ ] Cookie_Scanner_Controller.php created
- [ ] slos_cookie_definitions table created
- [ ] REST API endpoints working
- [ ] Cron job scheduled
- [ ] Scanner detects cookies
- [ ] Committed to git
- [ ] Ready for Task 2.6

---

**Status:** ✅ Ready to execute  
**Time:** 8-10 hours  
**Next:** [task-2.6-consent-preferences-ui.md](task-2.6-consent-preferences-ui.md)
