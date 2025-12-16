<?php
/**
 * Checker Registry
 *
 * Singleton registry for managing all accessibility checker classes.
 * Provides centralized registration and retrieval of checkers.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner\Scanner
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Modules\AccessibilityScanner\Scanner;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CheckerRegistry Class
 *
 * Manages registration and instantiation of all accessibility checkers.
 * Uses singleton pattern to ensure single instance across plugin.
 *
 * @since 1.0.0
 */
class CheckerRegistry {
    
    /**
     * Singleton instance
     *
     * @var CheckerRegistry|null
     */
    private static $instance = null;
    
    /**
     * Registered checker classes
     *
     * Array format: ['checker_type' => 'CheckerClassName']
     *
     * @var array
     */
    private $checkers = [];
    
    /**
     * Private constructor for singleton pattern
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Register default checkers
        $this->register_default_checkers();
    }
    
    /**
     * Get singleton instance
     *
     * @since 1.0.0
     *
     * @return CheckerRegistry Registry instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Register a checker class
     *
     * Stores checker class name indexed by its type identifier.
     * Does not instantiate the checker until requested.
     *
     * @since 1.0.0
     *
     * @param string $checker_class Fully qualified checker class name
     * @return void
     */
    public function register($checker_class) {
        // Validate that class exists
        if (!class_exists($checker_class)) {
            error_log("Checker class not found: {$checker_class}");
            return;
        }
        
        // Validate that class extends AbstractChecker
        if (!is_subclass_of($checker_class, AbstractChecker::class)) {
            error_log("Checker class must extend AbstractChecker: {$checker_class}");
            return;
        }
        
        // Get checker type from static method
        $checker_type = call_user_func([$checker_class, 'get_check_type']);
        
        // Register checker
        $this->checkers[$checker_type] = $checker_class;
    }
    
    /**
     * Unregister a checker
     *
     * Removes checker from registry by its type identifier.
     *
     * @since 1.0.0
     *
     * @param string $checker_type Checker type identifier
     * @return void
     */
    public function unregister($checker_type) {
        if (isset($this->checkers[$checker_type])) {
            unset($this->checkers[$checker_type]);
        }
    }
    
    /**
     * Get all registered checker instances
     *
     * Instantiates and returns all registered checkers.
     *
     * @since 1.0.0
     *
     * @return array<AbstractChecker> Array of checker instances
     */
    public function get_all_checkers() {
        $instances = [];
        
        foreach ($this->checkers as $checker_type => $checker_class) {
            $instances[] = new $checker_class();
        }
        
        return $instances;
    }
    
    /**
     * Get specific checker by type
     *
     * Returns a new instance of the specified checker.
     *
     * @since 1.0.0
     *
     * @param string $checker_type Checker type identifier
     * @return AbstractChecker|null Checker instance or null if not found
     */
    public function get_checker($checker_type) {
        if (isset($this->checkers[$checker_type])) {
            return new $this->checkers[$checker_type]();
        }
        
        return null;
    }
    
    /**
     * Check if checker is registered
     *
     * @since 1.0.0
     *
     * @param string $checker_type Checker type identifier
     * @return bool True if checker is registered
     */
    public function has_checker($checker_type) {
        return isset($this->checkers[$checker_type]);
    }
    
    /**
     * Get all registered checker types
     *
     * Returns array of checker type identifiers.
     *
     * @since 1.0.0
     *
     * @return array Array of checker type strings
     */
    public function get_registered_types() {
        return array_keys($this->checkers);
    }
    
    /**
     * Get count of registered checkers
     *
     * @since 1.0.0
     *
     * @return int Number of registered checkers
     */
    public function count() {
        return count($this->checkers);
    }
    
    /**
     * Register default accessibility checkers
     *
     * Registers all built-in checkers. Additional checkers can be
     * registered via the 'shahi_a11y_register_checkers' filter.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function register_default_checkers() {
        // Register built-in accessibility checkers
        $default_checkers = [
            Checkers\ImageChecker::class,
            Checkers\HeadingChecker::class,
            Checkers\LinkChecker::class,
            Checkers\FormChecker::class,
            Checkers\ARIAChecker::class,
        ];
        
        foreach ($default_checkers as $checker_class) {
            $this->register($checker_class);
        }
        
        /**
         * Filter: Allow registration of custom checkers
         *
         * Developers can add custom accessibility checkers by hooking
         * into this filter and calling $registry->register().
         *
         * @since 1.0.0
         *
         * @param CheckerRegistry $registry The checker registry instance
         */
        do_action('shahi_a11y_register_checkers', $this);
    }
    
    /**
     * Get checker metadata
     *
     * Returns information about a registered checker without instantiating it.
     *
     * @since 1.0.0
     *
     * @param string $checker_type Checker type identifier
     * @return array|null Checker metadata or null if not found
     */
    public function get_checker_metadata($checker_type) {
        if (!isset($this->checkers[$checker_type])) {
            return null;
        }
        
        $checker_class = $this->checkers[$checker_type];
        
        return [
            'type' => $checker_type,
            'class' => $checker_class,
            'name' => call_user_func([$checker_class, 'get_check_name']),
        ];
    }
    
    /**
     * Get all checker metadata
     *
     * Returns metadata for all registered checkers.
     *
     * @since 1.0.0
     *
     * @return array Array of checker metadata
     */
    public function get_all_metadata() {
        $metadata = [];
        
        foreach ($this->checkers as $checker_type => $checker_class) {
            $metadata[$checker_type] = [
                'type' => $checker_type,
                'class' => $checker_class,
                'name' => call_user_func([$checker_class, 'get_check_name']),
            ];
        }
        
        return $metadata;
    }
    
    /**
     * Clear all registered checkers
     *
     * Removes all checkers from registry. Useful for testing.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function clear() {
        $this->checkers = [];
    }
    
    /**
     * Prevent cloning of singleton
     *
     * @since 1.0.0
     */
    private function __clone() {
        // Singleton - prevent cloning
    }
    
    /**
     * Prevent unserialization of singleton
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
}
