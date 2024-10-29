<?php
/**
 * @link              http://store.wphound.com/?plugin=admin-hide-login
 * @since             1.0.0
 * @package           Admin_Hide_Login
 *
 * @wordpress-plugin
 * Plugin Name:       Admin Hide Login
 * Plugin URI:        http://store.wphound.com/?plugin=admin-hide-login
 * Description:       Protect your website by changing the login URL and preventing access to wp-login.php page.
 * Version:           1.0.0
 * Author:            WP Hound
 * Author URI:        http://www.wphound.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       admin-hide-login
 */



if ( defined( 'ABSPATH' )
	&& ! class_exists( 'admin_hide_login' ) ) {

	class admin_hide_login {

		private $wp_login_php;

        protected static $instance = null;

		private function basename() {

			return plugin_basename( __FILE__ );

		}

		private function path() {

			return trailingslashit( dirname( __FILE__ ) );

		}

		private function use_trailing_slashes() {

			return ( '/' === substr( get_option( 'permalink_structure' ), -1, 1 ) );

		}

		private function user_trailingslashit( $string ) {

			return $this->use_trailing_slashes()
				? trailingslashit( $string )
				: untrailingslashit( $string );

		}

		private function wp_template_loader() {

			global $pagenow;

			$pagenow = 'index.php';

			if ( ! defined( 'WP_USE_THEMES' ) ) {

				define( 'WP_USE_THEMES', true );

			}

			wp();

			if ( $_SERVER['REQUEST_URI'] === $this->user_trailingslashit( str_repeat( '-/', 10 ) ) ) {

				$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/wp-login-php/' );

			}

			require_once( ABSPATH . WPINC . '/template-loader.php' );

			die;

		}

		private function new_login_slug() {

			if ( $slug = get_option( 'admin_hide_login_page' ) ) {
				return $slug;
			} else if ( ( is_multisite() && is_plugin_active_for_network( $this->basename() ) && ( $slug = get_site_option( 'admin_hide_login_page', 'admin' ) ) ) ) {
    			return $slug;
			} else if ( $slug = 'admin' ) {
    			return $slug;
			}

		}

		public function new_login_url( $scheme = null ) {

			if ( get_option( 'permalink_structure' ) ) {

				return $this->user_trailingslashit( home_url( '/', $scheme ) . $this->new_login_slug() );

			} else {

				return home_url( '/', $scheme ) . '?' . $this->new_login_slug();

			}

		}

		public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'ahl_load_textdomain' ), 9 );

			global $wp_version;



            if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) || !function_exists( 'is_plugin_active' ) ) {
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
                
			}

            if ( is_plugin_active_for_network( 'rename-wp-login/rename-wp-login.php' ) ) {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                add_action( 'network_admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
                return;
            }

            if ( is_plugin_active( 'rename-wp-login/rename-wp-login.php' ) ) {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                add_action( 'admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
                return;
            }

			register_activation_hook( $this->basename(), array( $this, 'activate' ) );

			if ( is_multisite() && is_plugin_active_for_network( $this->basename() ) ) {
                add_action( 'wpmu_options', array( $this, 'wpmu_options' ) );
				add_action( 'update_wpmu_options', array( $this, 'update_wpmu_options' ) );

				add_filter( 'network_admin_plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
			}

            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 2 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );

            add_filter( 'plugin_action_links_' . $this->basename(), array( $this, 'plugin_action_links' ) );
			add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
			add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
			add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
			add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );

			remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

		}

      
	    public static function get_instance() {
        
	    	
	    	if ( null == self::$instance ) {
	    		self::$instance = new self;
	    	}
        
	    	return self::$instance;
	    }

		
        public function admin_notices_plugin_conflict() {

			echo '<div class="error notice is-dismissible"><p>' . __( 'Admin Hide Login could not be activated because you already have Rename wp-login.php active. Please uninstall rename wp-login.php to use Admin Hide Login', 'admin-hide-login') . '</p></div>';

		}

		public function activate() {
                
			add_option( 'ahl_redirect', '1' );

			delete_option( 'ahl_admin' );

		}

		public function wpmu_options() {

			$out = '';

			$out .= '<h3>' . __( 'Admin Hide Login', 'admin-hide-login') . '</h3>';
			$out .= '<p>' . __( 'This option allows you to set a networkwide default, which can be overridden by individual sites. Simply go to to the site’s permalink settings to change the url.', 'admin-hide-login' ) . '</p>';
			$out .= '<table class="form-table">';
				$out .= '<tr valign="top">';
					$out .= '<th scope="row"><label for="admin_hide_login_page">' . __( 'Networkwide default', 'admin-hide-login' ) . '</label></th>';
					$out .= '<td><input id="admin_hide_login_page" type="text" name="admin_hide_login_page" value="' . esc_attr( get_site_option( 'admin_hide_login_page', 'admin' ) )  . '"></td>';
				$out .= '</tr>';
			$out .= '</table>';

			echo $out;

		}

		public function update_wpmu_options() {
            if ( check_admin_referer( 'siteoptions' ) ) {
			    if ( ( $admin_hide_login_page = sanitize_title_with_dashes( $_POST['admin_hide_login_page'] ) )
			    	&& strpos( $admin_hide_login_page, 'wp-login' ) === false
			    	&& ! in_array( $admin_hide_login_page, $this->forbidden_slugs() ) ) {
                
			    	update_site_option( 'admin_hide_login_page', $admin_hide_login_page );
                
			    }
            }
		}

		public function admin_init() {

			global $pagenow;

			add_settings_section(
				'admin-hide-login-section',
				'Admin Hide Login',
				array( $this, 'ahl_section_desc' ),
				'general'
			);

			add_settings_field(
				'admin_hide_login_page',
				'<label for="admin_hide_login_page">' . __( 'New Login url', 'admin-hide-login' ) . '</label>',
				array( $this, 'admin_hide_login_page_input' ),
				'general',
				'admin-hide-login-section'
			);
			
			register_setting( 'general', 'admin_hide_login_page', 'sanitize_title_with_dashes' );

			if ( get_option( 'ahl_redirect' ) ) {

				delete_option( 'ahl_redirect' );

				if ( is_multisite()
					&& is_super_admin()
					&& is_plugin_active_for_network( $this->basename() ) ) {

					$redirect = network_admin_url( 'settings.php#admin_hide_login_page' );

				} else {

					$redirect = admin_url( 'options-general.php#admin_hide_login_page' );

				}

				wp_safe_redirect( $redirect );

				die;

			}

		}

		public function ahl_section_desc() {

			$out = '';

			if ( ! is_multisite()
				|| is_super_admin() ) {

			$out .= '<p>' . sprintf( __( 'Add/Change The Login Url Of website.', 'admin-hide-login' ), ' ', ' ' ) . '</p>';
			}

			if ( is_multisite()
				&& is_super_admin()
				&& is_plugin_active_for_network( $this->basename() ) ) {

				$out .= '<p>' . sprintf( __( 'To set a networkwide default, go to <a href="%s">Network Settings</a>.', 'admin-hide-login' ), network_admin_url( 'settings.php#admin-hide-login-page-input' ) ) . '</p>';

			}

			echo $out;

		}

		public function admin_hide_login_page_input() {?>
			<style>
            code#adminurl { background: #007395;color:white; }
            </style>
			<?php 
			if ( get_option( 'permalink_structure' ) ) {

				echo '<code id="adminurl">' . trailingslashit( home_url() ) . '</code> <input id="admin_hide_login_page" type="text" name="admin_hide_login_page" value="' . $this->new_login_slug()  . '">' . ( $this->use_trailing_slashes() ? ' <code id="adminurl">/</code>' : '' );

			} else {

				echo '<code>' . trailingslashit( home_url() ) . '?</code> <input id="admin_hide_login_page" type="text" name="admin_hide_login_page" value="' . $this->new_login_slug()  . '">';

			}

		}

		public function admin_notices() {

			global $pagenow;

			$out = '';

			if ( ! is_network_admin()
				&& $pagenow === 'options-general.php'
				&& isset( $_GET['settings-updated'] )
				&& ! isset( $_GET['page'] ) ) {

				echo '<div class="updated notice is-dismissible"><p>' . sprintf( __( 'Your login page is now here: <strong><a href="%1$s">%2$s</a></strong>. Bookmark this page!', 'admin-hide-login' ), $this->new_login_url(), $this->new_login_url() ) . '</p></div>';

			}

		}

		public function plugin_action_links( $links ) {

			if ( is_network_admin()
				&& is_plugin_active_for_network( $this->basename() ) ) {

				array_unshift( $links, '<a href="' . network_admin_url( 'settings.php#admin_hide_login_page' ) . '">' . __( 'Settings', 'admin-hide-login' ) . '</a>' );

			} elseif ( ! is_network_admin() ) {

				array_unshift( $links, '<a href="' . admin_url( 'options-general.php#admin_hide_login_page' ) . '">' . __( 'Settings', 'admin-hide-login' ) . '</a>' );

			}

			return $links;

		}

		public function plugins_loaded() {

			global $pagenow;

			if ( ! is_multisite()
				&& ( strpos( $_SERVER['REQUEST_URI'], 'wp-signup' )  !== false
					|| strpos( $_SERVER['REQUEST_URI'], 'wp-activate' ) )  !== false ) {

				wp_die( __( 'This feature is not enabled.', 'admin-hide-login' ) );

			}

			$request = parse_url( $_SERVER['REQUEST_URI'] );

			if ( ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false
					|| untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) )
				&& ! is_admin() ) {

				$this->wp_login_php = true;

				$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

				$pagenow = 'index.php';

			} elseif ( untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' )
				|| ( ! get_option( 'permalink_structure' )
					&& isset( $_GET[$this->new_login_slug()] )
					&& empty( $_GET[$this->new_login_slug()] ) ) ) {

				$pagenow = 'wp-login.php';

			}

		}

		public function wp_loaded() {

			global $pagenow;

			if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) && $pagenow !== 'admin-post.php' ) {
                wp_die( __( 'Opps! This is Wrong Url', 'admin-hide-login' ), 403 );
			}

			$request = parse_url( $_SERVER['REQUEST_URI'] );

			if ( $pagenow === 'wp-login.php'
				&& $request['path'] !== $this->user_trailingslashit( $request['path'] )
				&& get_option( 'permalink_structure' ) ) {

				wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() )
					. ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

				die;

			} elseif ( $this->wp_login_php ) {

				if ( ( $referer = wp_get_referer() )
					&& strpos( $referer, 'wp-activate.php' ) !== false
					&& ( $referer = parse_url( $referer ) )
					&& ! empty( $referer['query'] ) ) {

					parse_str( $referer['query'], $referer );

					if ( ! empty( $referer['key'] )
						&& ( $result = wpmu_activate_signup( $referer['key'] ) )
						&& is_wp_error( $result )
						&& ( $result->get_error_code() === 'already_active'
							|| $result->get_error_code() === 'blog_taken' ) ) {

						wp_safe_redirect( $this->new_login_url()
							. ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

						die;

					}

				}

				$this->wp_template_loader();

			} elseif ( $pagenow === 'wp-login.php' ) {

				global $error, $interim_login, $action, $user_login;

				@require_once ABSPATH . 'wp-login.php';

				die;

			}

		}

		public function site_url( $url, $path, $scheme, $blog_id ) {

			return $this->filter_wp_login_php( $url, $scheme );

		}

		public function network_site_url( $url, $path, $scheme ) {

			return $this->filter_wp_login_php( $url, $scheme );

		}

		public function wp_redirect( $location, $status ) {

			return $this->filter_wp_login_php( $location );

		}

		public function filter_wp_login_php( $url, $scheme = null ) {

			if ( strpos( $url, 'wp-login.php' ) !== false ) {

				if ( is_ssl() ) {

					$scheme = 'https';

				}

				$args = explode( '?', $url );

				if ( isset( $args[1] ) ) {

					parse_str( $args[1], $args );

					$url = add_query_arg( $args, $this->new_login_url( $scheme ) );

				} else {

					$url = $this->new_login_url( $scheme );

				}

			}

			return $url;

		}

		public function welcome_email( $value ) {

			return $value = str_replace( 'wp-login.php', trailingslashit( get_site_option( 'admin_hide_login_page', 'admin' ) ), $value );

		}

		public function forbidden_slugs() {

			$wp = new WP;

			return array_merge( $wp->public_query_vars, $wp->private_query_vars );

		}

        public function ahl_load_textdomain() {
            load_plugin_textdomain( 'admin-hide-login', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

	}

	add_action( 'plugins_loaded', array( 'admin_hide_login', 'get_instance' ), 1 );
}