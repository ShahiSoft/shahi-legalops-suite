<?php
/**
 * REST API Endpoint Boilerplate Template
 * 
 * PLACEHOLDER FILE - This is a template for creating custom REST API endpoints.
 * Copy this file to includes/api/ and customize it.
 * 
 * Instructions:
 * 1. Copy this file to: includes/api/{EndpointName}_Endpoint.php
 * 2. Replace all PLACEHOLDER values with your actual endpoint information
 * 3. Replace {PluginNamespace} with your actual namespace (e.g., ShahiTemplate)
 * 4. Replace {EndpointName} with your endpoint name in PascalCase (e.g., Analytics_Data)
 * 5. Replace {api-namespace} with your API namespace (e.g., shahi-template/v1)
 * 6. Replace {endpoint-route} with your route (e.g., analytics/data)
 * 7. Implement the endpoint methods and add your business logic
 * 
 * @package    {PluginNamespace}
 * @subpackage API
 * @since      1.0.0
 */

namespace {PluginNamespace}\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// PLACEHOLDER: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * {EndpointName} REST API Endpoint Class
 * 
 * PLACEHOLDER DESCRIPTION: Add your endpoint description here.
 * Explain what data this endpoint provides or what actions it performs.
 * 
 * Example endpoints:
 * - GET    /{api-namespace}/{endpoint-route}        - Get items
 * - GET    /{api-namespace}/{endpoint-route}/{id}   - Get single item
 * - POST   /{api-namespace}/{endpoint-route}        - Create item
 * - PUT    /{api-namespace}/{endpoint-route}/{id}   - Update item
 * - DELETE /{api-namespace}/{endpoint-route}/{id}   - Delete item
 * 
 * @since 1.0.0
 */
class {EndpointName}_Endpoint extends WP_REST_Controller {
    
    /**
     * API namespace
     * 
     * PLACEHOLDER: Replace with your plugin's API namespace
     * Format: plugin-slug/v1
     * 
     * @var string
     */
    protected $namespace = '{api-namespace}';
    
    /**
     * REST base (route)
     * 
     * PLACEHOLDER: Replace with your endpoint route
     * Example: 'analytics/data', 'users/profile', 'settings'
     * 
     * @var string
     */
    protected $rest_base = '{endpoint-route}';
    
    /**
     * Register routes
     * 
     * Called by WordPress to register all endpoint routes
     * 
     * @since 1.0.0
     * @return void
     */
    public function register_routes() {
        // PLACEHOLDER: Route 1 - Get collection
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE, // GET
                    'callback'            => [$this, 'get_items'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args'                => $this->get_collection_params(),
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE, // POST
                    'callback'            => [$this, 'create_item'],
                    'permission_callback' => [$this, 'create_item_permissions_check'],
                    'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
        
        // PLACEHOLDER: Route 2 - Get/Update/Delete single item
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'id' => [
                        'description' => __('Unique identifier for the item.', 'shahi-template'),
                        'type'        => 'integer',
                        'required'    => true,
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE, // GET
                    'callback'            => [$this, 'get_item'],
                    'permission_callback' => [$this, 'get_item_permissions_check'],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE, // PUT, PATCH
                    'callback'            => [$this, 'update_item'],
                    'permission_callback' => [$this, 'update_item_permissions_check'],
                    'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE, // DELETE
                    'callback'            => [$this, 'delete_item'],
                    'permission_callback' => [$this, 'delete_item_permissions_check'],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
        
        // PLACEHOLDER: Add custom routes here
        // Example: Batch operations, search, etc.
        /*
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/batch',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'batch_items'],
                    'permission_callback' => [$this, 'batch_items_permissions_check'],
                ],
            ]
        );
        */
    }
    
    /**
     * Get a collection of items
     * 
     * PLACEHOLDER: Implement your collection retrieval logic
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure
     */
    public function get_items($request) {
        // Get parameters
        $page     = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 10;
        $search   = $request->get_param('search') ?? '';
        $orderby  = $request->get_param('orderby') ?? 'id';
        $order    = $request->get_param('order') ?? 'desc';
        
        // PLACEHOLDER: Fetch items from database or external API
        // Example:
        /*
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_table';
        
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT * FROM $table_name";
        if (!empty($search)) {
            $query .= $wpdb->prepare(" WHERE name LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }
        $query .= $wpdb->prepare(" ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset);
        
        $items = $wpdb->get_results($query);
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        */
        
        // PLACEHOLDER: Mock data for demonstration
        $items = [
            [
                'id'          => 1,
                'name'        => 'Item 1',
                'description' => 'PLACEHOLDER: This is item 1',
                'created_at'  => current_time('mysql'),
            ],
            [
                'id'          => 2,
                'name'        => 'Item 2',
                'description' => 'PLACEHOLDER: This is item 2',
                'created_at'  => current_time('mysql'),
            ],
        ];
        $total_items = 2;
        
        // Prepare response
        $data = [];
        foreach ($items as $item) {
            $data[] = $this->prepare_item_for_response($item, $request);
        }
        
        $response = rest_ensure_response($data);
        
        // Add pagination headers
        $response->header('X-WP-Total', $total_items);
        $response->header('X-WP-TotalPages', ceil($total_items / $per_page));
        
        return $response;
    }
    
    /**
     * Get one item from the collection
     * 
     * PLACEHOLDER: Implement your single item retrieval logic
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure
     */
    public function get_item($request) {
        $id = $request->get_param('id');
        
        // PLACEHOLDER: Fetch item from database
        // Example:
        /*
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_table';
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        
        if (!$item) {
            return new WP_Error(
                'rest_item_not_found',
                __('Item not found.', 'shahi-template'),
                ['status' => 404]
            );
        }
        */
        
        // PLACEHOLDER: Mock data
        $item = [
            'id'          => $id,
            'name'        => 'Item ' . $id,
            'description' => 'PLACEHOLDER: This is item ' . $id,
            'created_at'  => current_time('mysql'),
        ];
        
        $data = $this->prepare_item_for_response($item, $request);
        
        return rest_ensure_response($data);
    }
    
    /**
     * Create one item from the collection
     * 
     * PLACEHOLDER: Implement your item creation logic
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure
     */
    public function create_item($request) {
        // Get and sanitize parameters
        $name        = sanitize_text_field($request->get_param('name'));
        $description = sanitize_textarea_field($request->get_param('description'));
        
        // PLACEHOLDER: Validate data
        if (empty($name)) {
            return new WP_Error(
                'rest_invalid_param',
                __('Name is required.', 'shahi-template'),
                ['status' => 400]
            );
        }
        
        // PLACEHOLDER: Insert into database
        // Example:
        /*
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_table';
        
        $inserted = $wpdb->insert(
            $table_name,
            [
                'name'        => $name,
                'description' => $description,
                'created_at'  => current_time('mysql'),
            ],
            ['%s', '%s', '%s']
        );
        
        if (!$inserted) {
            return new WP_Error(
                'rest_cannot_create',
                __('Cannot create item.', 'shahi-template'),
                ['status' => 500]
            );
        }
        
        $id = $wpdb->insert_id;
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        */
        
        // PLACEHOLDER: Mock response
        $item = [
            'id'          => wp_rand(100, 999),
            'name'        => $name,
            'description' => $description,
            'created_at'  => current_time('mysql'),
        ];
        
        $response = $this->prepare_item_for_response($item, $request);
        $response = rest_ensure_response($response);
        $response->set_status(201); // Created
        
        return $response;
    }
    
    /**
     * Update one item from the collection
     * 
     * PLACEHOLDER: Implement your item update logic
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure
     */
    public function update_item($request) {
        $id = $request->get_param('id');
        
        // PLACEHOLDER: Check if item exists
        // $existing_item = ...
        
        // Get and sanitize parameters
        $name        = sanitize_text_field($request->get_param('name'));
        $description = sanitize_textarea_field($request->get_param('description'));
        
        // PLACEHOLDER: Update in database
        // Example:
        /*
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_table';
        
        $updated = $wpdb->update(
            $table_name,
            [
                'name'        => $name,
                'description' => $description,
                'updated_at'  => current_time('mysql'),
            ],
            ['id' => $id],
            ['%s', '%s', '%s'],
            ['%d']
        );
        
        if ($updated === false) {
            return new WP_Error(
                'rest_cannot_update',
                __('Cannot update item.', 'shahi-template'),
                ['status' => 500]
            );
        }
        
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        */
        
        // PLACEHOLDER: Mock response
        $item = [
            'id'          => $id,
            'name'        => $name,
            'description' => $description,
            'updated_at'  => current_time('mysql'),
        ];
        
        $response = $this->prepare_item_for_response($item, $request);
        
        return rest_ensure_response($response);
    }
    
    /**
     * Delete one item from the collection
     * 
     * PLACEHOLDER: Implement your item deletion logic
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure
     */
    public function delete_item($request) {
        $id = $request->get_param('id');
        
        // PLACEHOLDER: Check if item exists and get it before deletion
        // $item = ...
        
        // PLACEHOLDER: Delete from database
        // Example:
        /*
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_table';
        
        $deleted = $wpdb->delete(
            $table_name,
            ['id' => $id],
            ['%d']
        );
        
        if (!$deleted) {
            return new WP_Error(
                'rest_cannot_delete',
                __('Cannot delete item.', 'shahi-template'),
                ['status' => 500]
            );
        }
        */
        
        $response = new WP_REST_Response();
        $response->set_data([
            'deleted'  => true,
            'previous' => [
                'id' => $id,
            ],
        ]);
        
        return $response;
    }
    
    /**
     * Permissions check for getting items collection
     * 
     * PLACEHOLDER: Customize based on your requirements
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has read access, WP_Error object otherwise
     */
    public function get_items_permissions_check($request) {
        // PLACEHOLDER: Customize permission check
        // Examples:
        // - Public access: return true;
        // - Logged in users: return is_user_logged_in();
        // - Specific capability: return current_user_can('manage_options');
        // - Custom logic: return $this->check_custom_permission();
        
        return current_user_can('read');
    }
    
    /**
     * Permissions check for getting a single item
     * 
     * PLACEHOLDER: Customize based on your requirements
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has read access, WP_Error object otherwise
     */
    public function get_item_permissions_check($request) {
        return $this->get_items_permissions_check($request);
    }
    
    /**
     * Permissions check for creating an item
     * 
     * PLACEHOLDER: Customize based on your requirements
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has access to create items, WP_Error object otherwise
     */
    public function create_item_permissions_check($request) {
        // PLACEHOLDER: Usually requires specific capability
        return current_user_can('edit_posts');
    }
    
    /**
     * Permissions check for updating an item
     * 
     * PLACEHOLDER: Customize based on your requirements
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has access to update the item, WP_Error object otherwise
     */
    public function update_item_permissions_check($request) {
        return $this->create_item_permissions_check($request);
    }
    
    /**
     * Permissions check for deleting an item
     * 
     * PLACEHOLDER: Customize based on your requirements
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request
     * @return bool|WP_Error True if the request has access to delete the item, WP_Error object otherwise
     */
    public function delete_item_permissions_check($request) {
        // PLACEHOLDER: Usually requires higher capability
        return current_user_can('delete_posts');
    }
    
    /**
     * Prepare item for response
     * 
     * PLACEHOLDER: Format your data for API response
     * 
     * @since 1.0.0
     * @param mixed           $item    Item data
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function prepare_item_for_response($item, $request) {
        // PLACEHOLDER: Convert item to array if it's an object
        if (is_object($item)) {
            $item = (array) $item;
        }
        
        // PLACEHOLDER: Prepare data structure
        $data = [
            'id'          => (int) $item['id'],
            'name'        => $item['name'],
            'description' => $item['description'],
            'created_at'  => isset($item['created_at']) ? $item['created_at'] : '',
            // Add more fields as needed
        ];
        
        // Wrap data in response object
        $response = rest_ensure_response($data);
        
        // Add links
        $response->add_links($this->prepare_links($item));
        
        return $response;
    }
    
    /**
     * Prepare links for the item
     * 
     * @since 1.0.0
     * @param array $item Item data
     * @return array Links for the given item
     */
    protected function prepare_links($item) {
        $base = sprintf('%s/%s', $this->namespace, $this->rest_base);
        
        return [
            'self' => [
                'href' => rest_url(sprintf('%s/%d', $base, $item['id'])),
            ],
            'collection' => [
                'href' => rest_url($base),
            ],
        ];
    }
    
    /**
     * Get the query params for collections
     * 
     * PLACEHOLDER: Define your collection parameters
     * 
     * @since 1.0.0
     * @return array Collection parameters
     */
    public function get_collection_params() {
        return [
            'page' => [
                'description'       => __('Current page of the collection.', 'shahi-template'),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'per_page' => [
                'description'       => __('Maximum number of items to be returned in result set.', 'shahi-template'),
                'type'              => 'integer',
                'default'           => 10,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'search' => [
                'description'       => __('Limit results to those matching a string.', 'shahi-template'),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'orderby' => [
                'description'       => __('Sort collection by attribute.', 'shahi-template'),
                'type'              => 'string',
                'default'           => 'id',
                'enum'              => ['id', 'name', 'created_at'], // PLACEHOLDER: Add your sortable fields
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'order' => [
                'description'       => __('Order sort attribute ascending or descending.', 'shahi-template'),
                'type'              => 'string',
                'default'           => 'desc',
                'enum'              => ['asc', 'desc'],
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }
    
    /**
     * Get the item's schema, conforming to JSON Schema
     * 
     * PLACEHOLDER: Define your data schema
     * 
     * @since 1.0.0
     * @return array Item schema data
     */
    public function get_item_schema() {
        if ($this->schema) {
            return $this->add_additional_fields_schema($this->schema);
        }
        
        // PLACEHOLDER: Define your schema
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => '{endpoint-name}',
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'description' => __('Unique identifier for the item.', 'shahi-template'),
                    'type'        => 'integer',
                    'context'     => ['view', 'edit', 'embed'],
                    'readonly'    => true,
                ],
                'name' => [
                    'description' => __('The name for the item.', 'shahi-template'),
                    'type'        => 'string',
                    'context'     => ['view', 'edit'],
                    'required'    => true,
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'description' => [
                    'description' => __('The description for the item.', 'shahi-template'),
                    'type'        => 'string',
                    'context'     => ['view', 'edit'],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
                'created_at' => [
                    'description' => __('The date the item was created, in the site\'s timezone.', 'shahi-template'),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => ['view', 'edit'],
                    'readonly'    => true,
                ],
                // PLACEHOLDER: Add more properties as needed
            ],
        ];
        
        $this->schema = $schema;
        
        return $this->add_additional_fields_schema($this->schema);
    }
}
