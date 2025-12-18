<?php
/**
 * Unit Tests for ConsentRepository
 *
 * @package ShahiLegalOpsSuite\Modules\Consent\Tests
 * @since 1.0.0
 */

namespace ShahiLegalOpsSuite\Modules\Consent\Tests;

use ShahiLegalOpsSuite\Modules\Consent\Repositories\ConsentRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ConsentRepository Test Suite
 *
 * Tests all methods of ConsentRepository for correctness and robustness.
 * Assumes WordPress testing environment (use wp-cli tests or phpunit).
 *
 * @since 1.0.0
 */
class ConsentRepositoryTest {

	/**
	 * Repository instance for testing.
	 *
	 * @var ConsentRepository
	 */
	private ConsentRepository $repository;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function setUp(): void {
		$this->repository = new ConsentRepository();
	}

	/**
	 * Test save_consent() with valid data.
	 *
	 * @test
	 * @return void
	 */
	public function test_save_consent_valid_data() {
		$preferences = array(
			'session_id'     => 'test-session-123',
			'region'         => 'EU',
			'categories'     => array(
				'necessary'  => true,
				'analytics'  => false,
				'marketing'  => false,
			),
			'banner_version' => '1.0.0',
			'user_id'        => 0,
			'source'         => 'banner',
		);

		$log_id = $this->repository->save_consent( $preferences );

		$this->assertIsInt( $log_id );
		$this->assertGreaterThan( 0, $log_id );
	}

	/**
	 * Test save_consent() without required fields.
	 *
	 * @test
	 * @return void
	 */
	public function test_save_consent_missing_session_id() {
		$preferences = array(
			'region'     => 'EU',
			'categories' => array( 'necessary' => true ),
		);

		$log_id = $this->repository->save_consent( $preferences );

		$this->assertFalse( $log_id );
	}

	/**
	 * Test save_consent() without region.
	 *
	 * @test
	 * @return void
	 */
	public function test_save_consent_missing_region() {
		$preferences = array(
			'session_id' => 'test-session-456',
			'categories' => array( 'necessary' => true ),
		);

		$log_id = $this->repository->save_consent( $preferences );

		$this->assertFalse( $log_id );
	}

	/**
	 * Test save_consent() without categories.
	 *
	 * @test
	 * @return void
	 */
	public function test_save_consent_missing_categories() {
		$preferences = array(
			'session_id' => 'test-session-789',
			'region'     => 'US-CA',
		);

		$log_id = $this->repository->save_consent( $preferences );

		$this->assertFalse( $log_id );
	}

	/**
	 * Test save_consent() with authenticated user.
	 *
	 * @test
	 * @return void
	 */
	public function test_save_consent_with_user_id() {
		$preferences = array(
			'session_id'     => 'test-session-user',
			'region'         => 'EU',
			'categories'     => array( 'necessary' => true, 'analytics' => true ),
			'banner_version' => '1.0.0',
			'user_id'        => 1,
			'source'         => 'banner',
		);

		$log_id = $this->repository->save_consent( $preferences );

		$this->assertIsInt( $log_id );
		$this->assertGreaterThan( 0, $log_id );

		$consent = $this->repository->get_consent_status( 'test-session-user' );
		$this->assertIsArray( $consent );
		$this->assertEquals( 1, $consent['user_id'] );
	}

	/**
	 * Test save_consent() with IP and user agent hashing.
	 *
	 * @test
	 * @return void
	 */
	public function test_save_consent_with_hashing() {
		$ip         = '192.168.1.1';
		$user_agent = 'Mozilla/5.0...';

		$preferences = array(
			'session_id'      => 'test-session-hash',
			'region'          => 'BR',
			'categories'      => array( 'necessary' => true ),
			'banner_version'  => '1.0.0',
			'ip_hash'         => ConsentRepository::hash_ip( $ip ),
			'user_agent_hash' => ConsentRepository::hash_user_agent( $user_agent ),
		);

		$log_id = $this->repository->save_consent( $preferences );

		$this->assertGreaterThan( 0, $log_id );

		$consent = $this->repository->get_consent_status( 'test-session-hash' );
		$this->assertNotEmpty( $consent['ip_hash'] );
		$this->assertNotEmpty( $consent['user_agent_hash'] );
		// Verify hashes are consistent.
		$this->assertEquals(
			ConsentRepository::hash_ip( $ip ),
			$consent['ip_hash']
		);
	}

	/**
	 * Test save_consent() with metadata and purposes (PRO).
	 *
	 * @test
	 * @return void
	 */
	public function test_save_consent_with_metadata_and_purposes() {
		$preferences = array(
			'session_id'     => 'test-session-pro',
			'region'         => 'EU',
			'categories'     => array( 'necessary' => true, 'analytics' => true ),
			'banner_version' => '1.0.0',
			'purposes'       => array(
				'analytics' => array( 'legitimate_interest', 'consent' ),
			),
			'metadata'       => array(
				'device_type' => 'mobile',
				'language'    => 'en-US',
			),
		);

		$log_id = $this->repository->save_consent( $preferences );

		$this->assertGreaterThan( 0, $log_id );

		$consent = $this->repository->get_consent_status( 'test-session-pro' );
		$this->assertIsArray( $consent['purposes'] );
		$this->assertIsArray( $consent['metadata'] );
		$this->assertEquals( 'mobile', $consent['metadata']['device_type'] );
	}

	/**
	 * Test get_consent_status() for active consent.
	 *
	 * @test
	 * @return void
	 */
	public function test_get_consent_status_active() {
		$session_id = 'test-session-active';
		$preferences = array(
			'session_id'     => $session_id,
			'region'         => 'EU',
			'categories'     => array(
				'necessary'  => true,
				'analytics'  => true,
				'marketing'  => false,
			),
			'banner_version' => '1.0.0',
		);

		$this->repository->save_consent( $preferences );

		$consent = $this->repository->get_consent_status( $session_id );

		$this->assertIsArray( $consent );
		$this->assertEquals( $session_id, $consent['session_id'] );
		$this->assertEquals( 'EU', $consent['region'] );
		$this->assertTrue( $consent['categories']['necessary'] );
		$this->assertTrue( $consent['categories']['analytics'] );
		$this->assertFalse( $consent['categories']['marketing'] );
		$this->assertNull( $consent['withdrawn_at'] );
	}

	/**
	 * Test get_consent_status() for non-existent session.
	 *
	 * @test
	 * @return void
	 */
	public function test_get_consent_status_nonexistent() {
		$consent = $this->repository->get_consent_status( 'non-existent-session' );

		$this->assertNull( $consent );
	}

	/**
	 * Test get_consent_status() with empty session ID.
	 *
	 * @test
	 * @return void
	 */
	public function test_get_consent_status_empty_session() {
		$consent = $this->repository->get_consent_status( '' );

		$this->assertNull( $consent );
	}

	/**
	 * Test withdraw_consent() for full withdrawal.
	 *
	 * @test
	 * @return void
	 */
	public function test_withdraw_consent_full() {
		$session_id = 'test-session-withdraw-full';
		$preferences = array(
			'session_id'     => $session_id,
			'region'         => 'EU',
			'categories'     => array(
				'necessary'  => true,
				'analytics'  => true,
				'marketing'  => true,
			),
			'banner_version' => '1.0.0',
		);

		$this->repository->save_consent( $preferences );

		// Verify consent is active.
		$before = $this->repository->get_consent_status( $session_id );
		$this->assertNull( $before['withdrawn_at'] );

		// Withdraw all.
		$result = $this->repository->withdraw_consent( $session_id );

		$this->assertTrue( $result );

		// Verify consent is now withdrawn.
		$after = $this->repository->get_consent_status( $session_id );
		$this->assertNull( $after ); // No active consent.
	}

	/**
	 * Test withdraw_consent() for partial withdrawal.
	 *
	 * @test
	 * @return void
	 */
	public function test_withdraw_consent_partial() {
		$session_id = 'test-session-withdraw-partial';
		$preferences = array(
			'session_id'     => $session_id,
			'region'         => 'EU',
			'categories'     => array(
				'necessary'  => true,
				'analytics'  => true,
				'marketing'  => true,
			),
			'banner_version' => '1.0.0',
		);

		$this->repository->save_consent( $preferences );

		// Withdraw only analytics and marketing.
		$result = $this->repository->withdraw_consent(
			$session_id,
			array( 'analytics', 'marketing' )
		);

		$this->assertTrue( $result );

		// Verify new consent has only necessary.
		$updated = $this->repository->get_consent_status( $session_id );
		$this->assertNotNull( $updated );
		$this->assertTrue( $updated['categories']['necessary'] );
		$this->assertFalse( isset( $updated['categories']['analytics'] ) );
		$this->assertFalse( isset( $updated['categories']['marketing'] ) );
	}

	/**
	 * Test get_logs() without filters.
	 *
	 * @test
	 * @return void
	 */
	public function test_get_logs_all() {
		// Create multiple consents.
		for ( $i = 0; $i < 5; $i++ ) {
			$this->repository->save_consent(
				array(
					'session_id' => "test-session-log-{$i}",
					'region'     => ( 0 === $i % 2 ) ? 'EU' : 'US-CA',
					'categories' => array( 'necessary' => true ),
				)
			);
		}

		$logs = $this->repository->get_logs();

		$this->assertIsArray( $logs );
		$this->assertCount( 5, $logs );
	}

	/**
	 * Test get_logs() with region filter.
	 *
	 * @test
	 * @return void
	 */
	public function test_get_logs_filter_region() {
		// Create consents with different regions.
		$this->repository->save_consent(
			array(
				'session_id' => 'test-session-log-eu-1',
				'region'     => 'EU',
				'categories' => array( 'necessary' => true ),
			)
		);
		$this->repository->save_consent(
			array(
				'session_id' => 'test-session-log-ca-1',
				'region'     => 'CA',
				'categories' => array( 'necessary' => true ),
			)
		);

		$eu_logs = $this->repository->get_logs( array( 'region' => 'EU' ) );
		$ca_logs = $this->repository->get_logs( array( 'region' => 'CA' ) );

		$this->assertTrue( all( array_map( fn( $log ) => 'EU' === $log['region'], $eu_logs ) ) );
		$this->assertTrue( all( array_map( fn( $log ) => 'CA' === $log['region'], $ca_logs ) ) );
	}

	/**
	 * Test get_logs() with pagination.
	 *
	 * @test
	 * @return void
	 */
	public function test_get_logs_pagination() {
		// Create 25 consents.
		for ( $i = 0; $i < 25; $i++ ) {
			$this->repository->save_consent(
				array(
					'session_id' => "test-session-page-{$i}",
					'region'     => 'EU',
					'categories' => array( 'necessary' => true ),
				)
			);
		}

		// Get page 1, 10 per page.
		$page1 = $this->repository->get_logs(
			array(
				'per_page' => 10,
				'page'     => 1,
			)
		);

		// Get page 2.
		$page2 = $this->repository->get_logs(
			array(
				'per_page' => 10,
				'page'     => 2,
			)
		);

		$this->assertCount( 10, $page1 );
		$this->assertCount( 10, $page2 );
		$this->assertNotEqual( $page1[0]['id'], $page2[0]['id'] );
	}

	/**
	 * Test get_logs() with date range filter.
	 *
	 * @test
	 * @return void
	 */
	public function test_get_logs_filter_date_range() {
		$start_date = '2025-01-01 00:00:00';
		$end_date   = '2025-12-31 23:59:59';

		$this->repository->save_consent(
			array(
				'session_id' => 'test-session-date-filter',
				'region'     => 'EU',
				'categories' => array( 'necessary' => true ),
			)
		);

		$logs = $this->repository->get_logs(
			array(
				'start_date' => $start_date,
				'end_date'   => $end_date,
			)
		);

		// Verify logs are within date range.
		foreach ( $logs as $log ) {
			$this->assertGreaterThanOrEqual( $start_date, $log['timestamp'] );
			$this->assertLessThanOrEqual( $end_date, $log['timestamp'] );
		}
	}

	/**
	 * Test count_logs() without filters.
	 *
	 * @test
	 * @return void
	 */
	public function test_count_logs_all() {
		$initial_count = $this->repository->count_logs();

		$this->repository->save_consent(
			array(
				'session_id' => 'test-session-count-1',
				'region'     => 'EU',
				'categories' => array( 'necessary' => true ),
			)
		);

		$final_count = $this->repository->count_logs();

		$this->assertEquals( $initial_count + 1, $final_count );
	}

	/**
	 * Test count_logs() with region filter.
	 *
	 * @test
	 * @return void
	 */
	public function test_count_logs_filter_region() {
		$this->repository->save_consent(
			array(
				'session_id' => 'test-session-count-eu',
				'region'     => 'EU',
				'categories' => array( 'necessary' => true ),
			)
		);

		$eu_count = $this->repository->count_logs( array( 'region' => 'EU' ) );

		$this->assertGreaterThan( 0, $eu_count );
	}

	/**
	 * Test export_logs() to CSV.
	 *
	 * @test
	 * @return void
	 */
	public function test_export_logs_csv() {
		$this->repository->save_consent(
			array(
				'session_id'     => 'test-session-export-csv',
				'region'         => 'EU',
				'categories'     => array( 'necessary' => true, 'analytics' => false ),
				'banner_version' => '1.0.0',
			)
		);

		$csv = $this->repository->export_logs( 'csv' );

		$this->assertIsString( $csv );
		$this->assertStringContainsString( 'ID', $csv );
		$this->assertStringContainsString( 'Session ID', $csv );
		$this->assertStringContainsString( 'test-session-export-csv', $csv );
	}

	/**
	 * Test export_logs() to JSON.
	 *
	 * @test
	 * @return void
	 */
	public function test_export_logs_json() {
		$this->repository->save_consent(
			array(
				'session_id'     => 'test-session-export-json',
				'region'         => 'BR',
				'categories'     => array( 'necessary' => true, 'functional' => true ),
				'banner_version' => '1.0.0',
			)
		);

		$json = $this->repository->export_logs( 'json' );

		$this->assertIsString( $json );
		$decoded = json_decode( $json, true );
		$this->assertIsArray( $decoded );
		$this->assertCount( 1, $decoded );
		$this->assertEquals( 'test-session-export-json', $decoded[0]['session_id'] );
	}

	/**
	 * Test cleanup_expired_logs().
	 *
	 * @test
	 * @return void
	 */
	public function test_cleanup_expired_logs() {
		// This test would require database manipulation or mocking.
		// For now, we verify the method returns an integer.
		$deleted = $this->repository->cleanup_expired_logs( 365 );

		$this->assertIsInt( $deleted );
		$this->assertGreaterThanOrEqual( 0, $deleted );
	}

	/**
	 * Test static helper: hash_ip().
	 *
	 * @test
	 * @return void
	 */
	public function test_hash_ip() {
		$ip    = '192.168.1.1';
		$hash  = ConsentRepository::hash_ip( $ip );
		$hash2 = ConsentRepository::hash_ip( $ip );

		$this->assertIsString( $hash );
		$this->assertEquals( 64, strlen( $hash ) ); // SHA256 = 64 hex chars.
		$this->assertEquals( $hash, $hash2 ); // Consistent.
	}

	/**
	 * Test static helper: hash_user_agent().
	 *
	 * @test
	 * @return void
	 */
	public function test_hash_user_agent() {
		$ua   = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)';
		$hash = ConsentRepository::hash_user_agent( $ua );

		$this->assertIsString( $hash );
		$this->assertEquals( 64, strlen( $hash ) );
	}

	/**
	 * Test static helper: generate_session_id().
	 *
	 * @test
	 * @return void
	 */
	public function test_generate_session_id() {
		$sid1 = ConsentRepository::generate_session_id();
		$sid2 = ConsentRepository::generate_session_id();

		$this->assertIsString( $sid1 );
		$this->assertIsString( $sid2 );
		$this->assertNotEmpty( $sid1 );
		$this->assertNotEmpty( $sid2 );
		$this->assertNotEqual( $sid1, $sid2 ); // Should be unique.
	}

	/**
	 * Test static helper: get_client_ip().
	 *
	 * @test
	 * @return void
	 */
	public function test_get_client_ip() {
		$ip = ConsentRepository::get_client_ip();

		$this->assertIsString( $ip );
		$this->assertTrue( filter_var( $ip, FILTER_VALIDATE_IP ) );
	}

	/**
	 * Integration test: Full consent lifecycle.
	 *
	 * @test
	 * @return void
	 */
	public function test_full_lifecycle() {
		$session_id = 'test-session-lifecycle';

		// 1. No consent yet.
		$before = $this->repository->get_consent_status( $session_id );
		$this->assertNull( $before );

		// 2. User accepts analytics.
		$log_id = $this->repository->save_consent(
			array(
				'session_id' => $session_id,
				'region'     => 'EU',
				'categories' => array(
					'necessary'  => true,
					'analytics'  => true,
					'marketing'  => false,
				),
			)
		);
		$this->assertGreaterThan( 0, $log_id );

		// 3. Consent is active.
		$after = $this->repository->get_consent_status( $session_id );
		$this->assertNotNull( $after );
		$this->assertTrue( $after['categories']['analytics'] );

		// 4. User withdraws analytics.
		$this->repository->withdraw_consent( $session_id, array( 'analytics' ) );

		// 5. Analytics is withdrawn.
		$updated = $this->repository->get_consent_status( $session_id );
		$this->assertFalse( isset( $updated['categories']['analytics'] ) );
		$this->assertTrue( $updated['categories']['necessary'] );

		// 6. Log is counted.
		$count = $this->repository->count_logs();
		$this->assertGreaterThan( 0, $count );
	}

	/**
	 * Test helper: all() function (PHP 8 standard).
	 *
	 * @param array $array Array to check.
	 * @return bool True if all elements are truthy.
	 */
	private function all( array $array ): bool {
		return ! in_array( false, $array, true );
	}
}
