<?php
/**
 * REST API.
 * Handles all rest related functionality.
 *
 * @package FormyChat
 * @since 1.0.0
 */

// Namespace .
namespace FormyChat\Admin;

// Load Widget Model.
require_once FORMYCHAT_INCLUDES . '/models/class-widget.php';

// Use Widget Model.
use FormyChat\Models\Widget;

// Exit if accessed directly.
defined('ABSPATH') || exit;


if ( ! class_exists ( __NAMESPACE__ . '\Rest') ) {
	/**
	 * REST API.
	 * Handles all rest related functionality.
	 *
	 * @package FormyChat
	 * @since 3.0.0
	 */
	class Rest extends \FormyChat\Base {
		/**
		 * Actions.
		 */
		public function actions() {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}

		/**
		 * Register routes.
		 */
		public function register_routes() {
			$routes = apply_filters('formychat_admin_rest_routes', [
				'widgets' => [
					[
						'methods' => 'GET',
						'callback' => [ $this, 'get_widgets' ],
					],
					[
						'methods' => 'DELETE',
						'callback' => [ $this, 'delete_widgets' ],
					],
				],
				'widget' => [
					'methods' => 'POST',
					'callback' => [ $this, 'create_widget' ],
				],
				'widget/(?P<id>[\d]+)' => [
					[
						'methods' => 'GET',
						'callback' => [ $this, 'get_widget' ],
					],
					[
						'methods' => 'PUT',
						'callback' => [ $this, 'update_widget' ],
					],
				],
				// Leads.
				'leads' => [
					[
						'methods' => 'GET',
						'callback' => [ $this, 'get_leads' ],
					],
					[
						'methods' => 'DELETE',
						'callback' => [ $this, 'delete_leads' ],
					],
				],
				'contents' => [
					'methods' => 'GET',
					'callback' => [ $this, 'get_contents' ],
				],
				'action' => [
					[
						'methods' => 'GET',
						'callback' => [ $this, 'perform_action' ],
					],
				],
			]);

			if ( ! empty($routes) ) {
				foreach ( $routes as $route => $args ) {
					if ( isset($args[0]) ) {
						foreach ( $args as $arg ) {

							$arg['permission_callback'] = function () {
								return current_user_can('manage_options');
							};

							register_rest_route('formychat', $route, $arg);
						}
					} else {

						$args['permission_callback'] = function () {
							return current_user_can('manage_options');
						};

						register_rest_route('formychat', $route, $args);
					}
				}
			}
		}

		/**
		 * Get widgets.
		 *
		 * @param \WP_REST_Request $request Request object.
		 */
		public function get_widgets( $request ) {
			$widgets = Widget::get_all();

			return new \WP_REST_Response( $widgets );
		}

		/**
		 * Get widget.
		 *
		 * @param \WP_REST_Request $request Request object.
		 */
		public function get_widget( $request ) {
			$widget_id = $request->get_param( 'id' );

			$widget = Widget::find( $widget_id );

			if ( $widget ) {
				return new \WP_REST_Response( [
					'success' => true,
					'data' => $widget,
				]);
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Widget not found.', 'social-contact-form' ),
			]);
		}

		/**
		 * Create widget.
		 *
		 * @param \WP_REST_Request $request Request object.
		 */
		public function create_widget( $request ) {

			$name = $request->get_param( 'name' ) ? $request->get_param( 'name' ) : 'Untitled';
			$is_active = $request->get_param( 'is_active' ) ? wp_validate_boolean( $request->get_param( 'is_active' ) ) : 1;
			$config = $request->get_param( 'config' ) ? $request->get_param( 'config' ) : [];

			$widget_id = Widget::create( [
				'name' => $name,
				'is_active' => $is_active,
				'config' => $config,
			] );

			if ( $widget_id ) {
				return new \WP_REST_Response( [
					'success' => true,
					'id' => $widget_id,
					'data' => Widget::find( $widget_id ),
				]);
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Widget not created.', 'social-contact-form' ),
			]);
		}

		/**
		 * Update widget.
		 *
		 * @param \WP_REST_Request $request Request object.
		 */
		public function update_widget( $request ) {
			$widget_id = $request->get_param( 'id' );

			// Bail if widget not found.
			$widget = Widget::find( $widget_id );

			if ( ! $widget ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => __( 'Widget not found.', 'social-contact-form' ),
				]);
			}

			$data = [];

			$allowed = [ 'name', 'is_active', 'config' ];

			foreach ( $allowed as $key ) {
				if ( $request->has_param( $key ) ) {
					$data[ $key ] = $request->get_param( $key );
				}
			}

			// Bail if no data.
			if ( empty( $data ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => __( 'No data to update.', 'social-contact-form' ),
				]);
			}

			$updated = Widget::update( $widget_id, $data );

			if ( $updated ) {
				return new \WP_REST_Response( [
					'success' => true,
					'data' => Widget::find( $widget_id ),
				]);
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Widget not updated.', 'social-contact-form' ),
			] );
		}

		/**
		 * Delete widget.
		 *
		 * @param \WP_REST_Request $request Request object.
		 */
		public function delete_widgets( $request ) {
			$id = $request->get_param( 'id' );

			if ( ! $id ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => __( 'No widget ID provided.', 'social-contact-form' ),
				]);
			}

			// If not array.
			$ids = is_array( $id ) ? $id : [ $id ];

			$deleted = Widget::delete( $ids );

			if ( $deleted ) {
				return new \WP_REST_Response( [
					'success' => true,
					'message' => __( 'Widget deleted.', 'social-contact-form' ),
				]);
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Widget not deleted.', 'social-contact-form' ),
			]);
		}

		/**
		 * Perform action.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function perform_action( $request ) {
			$action = $request->get_param( 'action' );

			if ( ! $action || ! method_exists( $this, 'action_' . $action ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => __( 'Action not found.', 'social-contact-form' ),
				]);
			}

			return $this->{'action_' . $action}( $request );
		}

		/**
		 * Get CF7 form.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function action_load_cf7_form( $request ) {
			$id = $request->get_param( 'id' );

			if ( ! $id ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => __( 'No form ID provided.', 'social-contact-form' ),
				]);
			}
			// do_shortcode( wp_sprintf( '[contact-form-7 id=%s]', $id ));

			return new \WP_REST_Response( [
				'success' => true,
				'form' => do_shortcode( wp_sprintf( '[contact-form-7 id=%s]', $id )),
			]);
		}

		/**
		 * Action activate CF7 plugin.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function action_activate_cf7( $request ) {

			// Include plugin.php for get_plugin_data() function.
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( ! file_exists( WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php' ) ) {

				// Include necessary WordPress files for installing and activating plugins.
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/misc.php';

				// Request filesystem credentials if necessary.
				$creds = request_filesystem_credentials('', '', false, false, null);

				// Check if we can use the filesystem, if not, throw an error.
				if ( ! WP_Filesystem( $creds ) ) {
					return new \WP_REST_Response( [
						'success' => false,
						'message' => __( 'Could not access filesystem.', 'social-contact-form' ),
					], 500 );
				}

				$api = plugins_api( 'plugin_information', [ 'slug' => 'contact-form-7' ] );

				if ( is_wp_error( $api ) ) {
					return new \WP_REST_Response( [
						'success' => false,
						'message' => $api->get_error_message(),
					], 500 );
				}

				try {
					$upgrader = new \Plugin_Upgrader( new \WP_Upgrader_Skin() );
					$install = $upgrader->install( $api->download_link );

					if ( is_wp_error( $install ) ) {
						return new \WP_REST_Response( [
							'success' => false,
							'message' => $install->get_error_message(),
						], 500 );
					}
				} catch ( \Exception $e ) {
					return new \WP_REST_Response( [
						'success' => false,
						'message' => $e->getMessage(),
					], 500 );
				}
			}

			// Activate plugin.
			$activated = activate_plugin( 'contact-form-7/wp-contact-form-7.php' );

			if ( is_wp_error( $activated ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => $activated->get_error_message(),
				], 500 );
			}

			return new \WP_REST_Response( [
				'success' => true,
				'message' => __( 'Contact Form 7 plugin activated.', 'social-contact-form' ),
			]);
		}


		/**
		 * Get leads.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function get_leads( $request ) {

			$mode = $request->has_param( 'mode' ) ? $request->get_param( 'mode' ) : 'formychat';

			$after = $request->has_param( 'after' ) ? $request->get_param( 'after' ) : '';
			$before = $request->has_param( 'before' ) ? $request->get_param( 'before' ) : '';

			// Before is the first moment of the day.
			if ( $after ) {
				$after = gmdate( 'Y-m-d 00:00:00', strtotime( $after ) );
			}

			// After is the last moment of the day.
			if ( $before ) {
				$before = gmdate( 'Y-m-d 23:59:59', strtotime( $before ) );
			}

			$filter = [
				'search' => $request->has_param( 'search' ) ? $request->get_param( 'search' ) : '',
				'order' => $request->has_param( 'order' ) ? $request->get_param( 'order' ) : 'DESC',
				'per_page' => $request->has_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 10,
				'page' => $request->has_param( 'page' ) ? intval( $request->get_param( 'page' ) ) : 1,
				'order_by' => $request->has_param( 'order_by' ) ? $request->get_param( 'order_by' ) : 'created_at',
				'cf7_id' => $request->has_param( 'cf7_id' ) ? $request->get_param( 'cf7_id' ) : '',
				'widget_id' => $request->has_param( 'widget_id' ) ? $request->get_param( 'widget_id' ) : '',
				'before' => $before,
				'after' => $after,
			];

			error_log( print_r( $filter, true ) );

			if ( 'formychat' === $mode ) {
				$leads = \FormyChat\Models\Lead::get( $filter );
			} else {
				$leads = \FormyChat\Models\LeadCF7::get( $filter );
			}

			// If no leads.
			if ( empty( $leads ) ) {
				return new \WP_REST_Response([]);
			}

			return new \WP_REST_Response( $leads );
		}

		/**
		 * Delete leads.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function delete_leads( $request ) {
			$id = $request->get_param( 'id' );

			if ( ! $id ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => __( 'No lead ID provided.', 'social-contact-form' ),
				]);
			}

			// If not array.
			$ids = is_array( $id ) ? $id : [ $id ];

			$mode = $request->has_param( 'mode' ) ? $request->get_param( 'mode' ) : 'formychat';

			$deleted = false;

			if ( 'formychat' === $mode ) {
				$deleted = \FormyChat\Models\Lead::delete( $ids );
			} else {
				$deleted = \FormyChat\Models\LeadCF7::delete( $ids );
			}

			if ( $deleted ) {
				return new \WP_REST_Response( [
					'success' => true,
					'message' => __( 'Leads deleted.', 'social-contact-form' ),
				]);
			}

			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( 'Leads not deleted.', 'social-contact-form' ),
			]);
		}

		/**
		 * Get contents.
		 *
		 * @param \WP_REST_Request $request Request object.
		 * @return \WP_REST_Response
		 */
		public function get_contents( $request ) {
			$contents = [
				'countries' => \FormyChat\App::countries(),
				'fonts' => \FormyChat\App::fonts(),
				'pages' => $this->get_pages(),
				'widgets' => Widget::get_names(),
				'cf7_forms'   => $this->get_cf7_forms(),
			];

			return new \WP_REST_Response( $contents );
		}


		/**
		 * Get all pages.
		 *
		 * @return array
		 */
		public function get_pages() {
			global $wpdb;

			$pages = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish'" ); // db call ok; no-cache ok.

			if ( empty( $pages ) ) {
				return [];
			}

			return $pages;
		}



		/**
		 * Get all CF7 forms.
		 *
		 * @return array
		 */
		public function get_cf7_forms() {
			$forms = [];
			if ( ! class_exists('WPCF7') ) {
				return $forms;
			}

			$args = [
				'post_type' => 'wpcf7_contact_form',
				'posts_per_page' => -1,
			];

			$query = new \WP_Query($args);

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$forms[] = [
						'value' => get_the_ID(),
						'name' => get_the_title(),
						'label' => get_the_title(),
					];
				}
			}

			wp_reset_postdata();

			return $forms;
		}
	}


	// Initialize the plugin.
	Rest::init();
}
