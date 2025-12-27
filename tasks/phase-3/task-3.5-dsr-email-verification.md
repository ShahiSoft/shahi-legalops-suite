# TASK 3.5: DSR Email Verification & Tokens

**Phase:** 3 (DSR Portal)  
**Effort:** 4-6 hours  
**Prerequisites:** Task 3.4 form  
**Next Task:** [task-3.6-dsr-admin-workflow.md](task-3.6-dsr-admin-workflow.md)

---

## 🎯 COPY THIS ENTIRE PROMPT TO YOUR AI AGENT

```
You are implementing TASK 3.5 for the Shahi LegalOps Suite WordPress plugin.

CONTEXT:
Implement email verification for DSR submissions to prevent abuse and confirm identity.
Tokens expire in 24h, are single-use, and include rate limiting.

References: /v3docs/modules/02-DSR-IMPLEMENTATION.md - Security & Privacy section

INPUT STATE (verify these exist):
✅ DSR_Repository with create_request() and find_by_token() (Task 3.1)
✅ DSR_Service with submit_request() (Task 3.2)
✅ REST API infrastructure (Task 1.6)
✅ Email templates directory

YOUR TASK:

1) **Add Verification Service**

File: `includes/Services/DSR_Verification_Service.php`

```php
<?php
namespace Shahi\LegalOps\Services;

class DSR_Verification_Service {
    private $repository;
    private $rate_limit_key = 'slos_dsr_verify_attempts';
    
    public function __construct( $repository ) {
        $this->repository = $repository;
    }
    
    /**
     * Send verification email with token
     */
    public function send_verification_email( $request_id ) {
        $request = $this->repository->find( $request_id );
        if ( ! $request ) {
            return false;
        }
        
        $verify_url = rest_url( 'slos/v1/dsr/verify' ) . '?token=' . $request->verification_token;
        $subject = __( 'Verify Your Data Subject Request', 'shahi-legalops' );
        
        $message = $this->get_email_template( 'verification', [
            'requester_name' => $request->requester_name ?? $request->email,
            'request_type' => $request->request_type,
            'verify_url' => $verify_url,
            'expires_hours' => 24,
        ] );
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $sent = wp_mail( $request->email, $subject, $message, $headers );
        
        if ( $sent ) {
            $this->repository->update( $request_id, [
                'verification_sent_at' => current_time( 'mysql' ),
            ] );
        }
        
        return $sent;
    }
    
    /**
     * Verify token and update status
     */
    public function verify_token( $token, $ip_address ) {
        // Rate limiting
        if ( ! $this->check_rate_limit( $ip_address ) ) {
            return new \WP_Error( 'rate_limit', __( 'Too many verification attempts. Please try again later.', 'shahi-legalops' ) );
        }
        
        $request = $this->repository->find_by_token( $token );
        if ( ! $request ) {
            $this->increment_rate_limit( $ip_address );
            return new \WP_Error( 'invalid_token', __( 'Invalid or expired verification token.', 'shahi-legalops' ) );
        }
        
        // Check expiration (24 hours)
        $sent_at = new \DateTime( $request->verification_sent_at );
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $sent_at->getTimestamp();
        
        if ( $diff > 86400 ) { // 24 hours
            return new \WP_Error( 'token_expired', __( 'Verification token has expired.', 'shahi-legalops' ) );
        }
        
        // Update status to verified
        $this->repository->update_status( $request->id, 'verified', [
            'verified_at' => current_time( 'mysql' ),
        ] );
        
        // Clear verification token (single-use)
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'slos_dsr_requests',
            ['verification_token' => ''],
            ['id' => $request->id],
            ['%s'],
            ['%d']
        );
        
        return true;
    }
    
    /**
     * Check rate limit (max 5 attempts per hour per IP)
     */
    private function check_rate_limit( $ip_address ) {
        $ip_hash = hash( 'sha256', $ip_address );
        $transient_key = $this->rate_limit_key . '_' . $ip_hash;
        $attempts = get_transient( $transient_key );
        
        return ! $attempts || $attempts < 5;
    }
    
    /**
     * Increment rate limit counter
     */
    private function increment_rate_limit( $ip_address ) {
        $ip_hash = hash( 'sha256', $ip_address );
        $transient_key = $this->rate_limit_key . '_' . $ip_hash;
        $attempts = get_transient( $transient_key ) ?: 0;
        set_transient( $transient_key, $attempts + 1, HOUR_IN_SECONDS );
    }
    
    /**
     * Get email template
     */
    private function get_email_template( $template_name, $vars ) {
        ob_start();
        include SLOS_PLUGIN_DIR . "templates/emails/dsr-{$template_name}.php";
        $content = ob_get_clean();
        
        return apply_filters( "slos_dsr_email_{$template_name}", $content, $vars );
    }
}
```

2) **Add Verify Endpoint to REST API**

File: `includes/API/DSR_Controller.php` (add method):

```php
public function verify_email( \WP_REST_Request $request ) {
    $token = $request->get_param( 'token' );
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $verification_service = new \Shahi\LegalOps\Services\DSR_Verification_Service( $this->repository );
    $result = $verification_service->verify_token( $token, $ip_address );
    
    if ( is_wp_error( $result ) ) {
        return new \WP_REST_Response( [
            'success' => false,
            'message' => $result->get_error_message(),
        ], 400 );
    }
    
    return new \WP_REST_Response( [
        'success' => true,
        'message' => __( 'Email verified successfully. Your request is now being processed.', 'shahi-legalops' ),
    ], 200 );
}
```

3) **Verification Tests**

```bash
# Test email sending
wp eval "
\$repo = new Shahi\LegalOps\Repositories\DSR_Repository();
\$service = new Shahi\LegalOps\Services\DSR_Verification_Service(\$repo);
\$id = \$repo->create_request([
    'email' => 'test@example.com',
    'request_type' => 'access',
    'regulation' => 'GDPR'
]);
\$sent = \$service->send_verification_email(\$id);
echo 'Email sent: ' . (\$sent ? 'Yes' : 'No') . PHP_EOL;
"

# Get token from DB
wp db query "SELECT id, email, verification_token, status FROM wp_slos_dsr_requests WHERE email = 'test@example.com' ORDER BY id DESC LIMIT 1"

# Test verification (replace TOKEN)
curl "http://yoursite.local/wp-json/slos/v1/dsr/verify?token=TOKEN"

# Verify status changed
wp db query "SELECT id, status, verified_at FROM wp_slos_dsr_requests WHERE email = 'test@example.com' ORDER BY id DESC LIMIT 1"
```

OUTPUT STATE:
✅ Verification service with rate limiting
✅ Email template with verification link
✅ REST endpoint for verification
✅ Tokens expire in 24 hours
✅ Single-use tokens
✅ Rate limit: 5 attempts/hour per IP

SUCCESS CRITERIA:
✅ Email sent with unique token
✅ Token expires after 24 hours
✅ Status changes to verified
✅ Token cleared after use
✅ Rate limiting blocks abuse
✅ Template customizable via filter

ROLLBACK:
```bash
rm includes/Services/DSR_Verification_Service.php
git checkout includes/API/DSR_Controller.php
```

COMMIT MESSAGE:
```
feat(dsr): add email verification with rate limiting

- Single-use tokens expire in 24h
- Rate limit: 5 attempts/hour per IP
- Customizable email templates
- Hashed IP for privacy

Task: 3.5 (4-6 hours)
Next: Task 3.6 - Admin Workflow
```
```
