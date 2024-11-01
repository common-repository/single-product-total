<?php
/**
 * Single product total admin loader class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      2.0
 */

if ( ! class_exists( 'SPTotalLoader' ) ) {
	/**
	 * Single product total admin loader class
	 */
	class SPTotalLoader {



		/**
		 * Plugin init action hook - main entry of the plugin
		 */
		public function init() {
            register_activation_hook( SPTOTAL, array( $this, 'activate' ) );
            register_deactivation_hook( SPTOTAL, array( $this, 'deactivate' ) );

            add_action( 'init', array( $this, 'do_activate' ) );
            add_action( 'before_woocommerce_init', array( $this,'wc_init' ) );

        }
        


        public function activate(){
            $this->do_activate();

            update_option( 'sptotal_total_text', 'Total :' );
            flush_rewrite_rules();
        }
        public function deactivate(){
            flush_rewrite_rules();
        }
        public function do_activate(){

            // check prerequisits
            if( ! $this->should_activate() ) return;

            // needs to be off the hook in the next version
            include( SPTOTAL_PATH . 'includes/core-data.php' );

            // add extra links right under plug
            add_filter( 'plugin_action_links_' . plugin_basename( SPTOTAL ), array( $this, 'plugin_action_links' ), 10, 1 );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_desc_meta' ), 10, 2 );

            include( SPTOTAL_PATH . 'includes/class/admin/class-sptotalsettings.php' );
            include( SPTOTAL_PATH . 'includes/class/class-sptotal.php' );

            add_action( 'admin_head', array( $this, 'admin_head' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );

            // Enqueue admin script and style
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
        }
        public function wc_init(){
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SPTOTAL, true );
            }
        }
        public function should_activate(){
            $plugin           = 'single-product-total/single-product-total.php';

            $is_wc_active     = is_plugin_active( 'woocommerce/woocommerce.php' );
            $is_plugin_active = is_plugin_active( $plugin );

            if( $is_plugin_active && ! $is_wc_active ){
                // deactivate plugin.
                deactivate_plugins( $plugin );
                add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );

                return false;
            }
            
            $this->client_feedback();
            return true;
        }



        public function admin_head(){
            $this->handle_admin_notice();
            $this->admin_menu_css();
        }
        public function admin_menu() {

            // Main menu
            add_menu_page( 
                __( 'Product Total', 'single-product-total' ),
                __( 'Product Total', 'single-product-total'  ),
                'manage_options',
                'sptotal-settings',
                array( $this, 'settings_page' ),
                plugin_dir_url( SPTOTAL ) . 'assets/images/logo.svg',
                57
            );
        
            // settings submenu - settings
            add_submenu_page(
                'sptotal-settings',
                __( 'Single Product Total - Settings', 'single-product-total' ),
                __( 'Settings', 'single-product-total' ),
                'manage_options',
                'sptotal-settings'
            );
        }



        public function plugin_action_links( $links ){
            global $sptotal__;

            $action_links             = array();
            $action_links['settings'] = '<a href="' . esc_url( admin_url( 'admin.php?page=sptotal-settings' ) ) . '">' . esc_html__( 'Settings', 'single-product-total' ) . '</a>';
        
            return array_merge( $action_links, $links );
        }
        public function plugin_desc_meta( $links, $file ){
            global $sptotal__;

            // if it's not Role Based Product plugin, return.
			if ( plugin_basename( SPTOTAL ) !== $file ) {
				return $links;
			}
        
            $row_meta            = array();
            $row_meta['apidocs'] = '<a href="' . esc_url( $sptotal__['plugin']['request_quote'] ) . '">' . esc_html__( 'Support', 'single-product-total' ) . '</a>';
            
            return array_merge( $links, $row_meta );
        }
        public function admin_menu_css() {

            ?>
            <style>
                #toplevel_page_sptotal-settings img {
                    width: 18px;
                    opacity: 1 !important;
                }
                .notice h3{
                    margin-top: .5em;
                    margin-bottom: 0;
                }
            </style>
            <?php

        }


        public function admin_scripts() {
            global $sptotal__;
        
            // check scope, without it return
            if( ! $this->is_in_scope() ) return;
            
            // enqueue style
            wp_register_style( 'sptotal_admin_style', plugin_dir_url( SPTOTAL ) . 'assets/admin/admin.css', array(), SPTOTAL_VER );
            wp_enqueue_style( 'sptotal_admin_style' );

            // colorpicker.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
            
            wp_register_script( 'sptotal_admin_script', plugin_dir_url( SPTOTAL ) . 'assets/admin/admin.js', array( 'jquery' ), SPTOTAL_VER, true );
            wp_enqueue_script( 'sptotal_admin_script' );
            
            $var = array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce('ajax-nonce')
            );
        
            // apply hook for editing localized variables in admin script
            $var = apply_filters( 'sptotal_update_admin_local_val', $var );
            
            wp_localize_script( 'sptotal_admin_script', 'sptotal_admin_data', $var );
        }
        public function frontend_scripts() {
            global $sptotal__;
        
            // enqueue style
            wp_register_style( 'sptotal_frontend_style', plugin_dir_url( SPTOTAL ) . 'assets/frontend.css', array(), SPTOTAL_VER );
            wp_enqueue_style( 'sptotal_frontend_style' );
            
            wp_register_script( 'sptotal_frontend_script', plugin_dir_url( SPTOTAL ) . 'assets/frontend.js', array( 'jquery', 'jquery-ui-slider', 'jquery-ui-sortable' ), SPTOTAL_VER , true );
            wp_enqueue_script( 'sptotal_frontend_script' );
        
            $this->add_local_var();
        }
        public function add_local_var(){
            global $post;

            if( empty( $post ) || ! isset( $post->ID ) ){
				return;
			}

            $cls   = new SPTotal();

            $data  = array();
            $theme = wp_get_theme();

            // add localized variables.
            $data = array(
                'ajaxurl'  => admin_url( 'admin-ajax.php' ),
                'theme'    => $theme->name,
                'dp'       => get_option( 'woocommerce_price_num_decimals', 2 ), // decimal point.
                'ds'       => wc_get_price_decimal_separator(), // decimal separator.
                'ts'       => wc_get_price_thousand_separator(), // thousand separator.
                'settings' => array(
                    'position' => get_option( 'sptotal_total_position' ) ?? 'before_cart_btn'
                ),
            );
            
            ob_start();
            $cls->display_total();
            $data['html'] = ob_get_clean();

            // get price.
            $product = wc_get_product( $post->ID );
            if( isset( $product ) && !empty( $product ) ){
                $data['type']  = $product->get_type();
            }
        
            // apply filter.
            $data = apply_filters( 'sptotal_clocal_variables', $data );
            
            // localize frontend data.
            wp_localize_script( 'sptotal_frontend_script', 'sptotal_data', $data );
        }



        public function wc_missing_notice(){
            global $sptotal__;

			$wc = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $sptotal__['plugin']['woo_url'] ),
				esc_html__( 'WooCommerce', 'single-product-total' )
			);

			?>
			<div class="error">
				<p>
					<?php
						printf(
							// translators: %1$s: plugin name with url you need to activate first.
							esc_html__( 'Please install and activate %1$s plugin first.', 'single-product-total' ),
							wp_kses_post( $wc )
						);
					?>
				</p>
			</div>
			<?php
        }
        public function handle_admin_notice(){
            global $sptotal__;
        
            // check scope, without it return
            if( ! $this->is_in_scope() ) return;
        
            // Buffer only the notices
            ob_start();
            do_action( 'admin_notices' );
            $content = ob_get_contents();
            ob_get_clean();
            
            // Keep the notices in global $sptotal__;
            array_push( $sptotal__['notice'], $content );
        
            // Remove all admin notices as we don't need to display in it's place
            remove_all_actions( 'admin_notices' );
        }
        public function client_feedback(){
            global $sptotal__;

            if( isset( $_GET['rate_sptotal'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'sptotal_rate' ) ){
                $task = sanitize_key( wp_unslash( $_GET['rate_sptotal'] ) );
                
                if( $task == 'done' ) update_option( 'rate_sptotal', "done" );
                else if( $task == 'cancel' ) update_option( 'rate_sptotal', gmdate( 'Y-m-d' ) );
            }
        
            if ( $this->if_show_notice( 'rate_sptotal' ) ) {
				add_action( 'admin_notices', array( $this, 'feedback_notice' ) );
			}
        }
        public function feedback_notice(){
            global $sptotal__;
        
            $page  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$page .= false !== strpos( $page, '?' ) ? '&' : '?';
			$page .= 'nonce=' . wp_create_nonce( 'sptotal_rate' ) . '&';

            $plugin = sprintf(
				'<strong><a href="%s">%s</a></strong>',
				esc_url( $sptotal__['plugin']['free_url'] ),
				esc_html( $sptotal__['plugin']['name'] )
			);

			$review = sprintf(
				'<strong><a href="%s">%s</a></strong>',
				esc_url( $sptotal__['plugin']['review_link'] ),
				esc_html__( 'WordPress.org', 'single-product-total' )
			);
            
            ?>
            <div class="notice notice-info is-dismissible">
                <h3><?php echo esc_html( $sptotal__['plugin']['name'] ); ?></h3>
                <p>
                    <?php
						printf(
							// translators: %1$s: plugin name with url, %2$s: plugin review url on WordPress.
							esc_html__( 'Excellent! You\'ve been using %1$s for a while. We\'d appreciate if you kindly rate us on %2$s', 'single-product-total' ),
							wp_kses_post( $plugin ),
							wp_kses_post( $review )
						);
					?>
                </p>
                <p>
                    <a href="<?php echo esc_url( $sptotal__['plugin']['review_link'] ); ?>" class="button-primary"><?php echo esc_html__( 'Rate it', 'single-product-total' ); ?></a> <a href="<?php echo esc_url( $page ); ?>rate_sptotal=done&nonce=<?php echo esc_attr( wp_create_nonce( 'sptotal_rate' ) ); ?>" class="button"><?php echo esc_html__( 'Already Did', 'single-product-total' ); ?></a> <a href="<?php echo esc_url( $page ); ?>rate_sptotal=cancel" class="button"><?php echo esc_html__( 'Cancel', 'single-product-total' ); ?></a>
                </p>
            </div>
            <?php
            
        }



        public function is_in_scope(){
            global $sptotal__;
        
            $screen = get_current_screen();
        
            // check with our plugin screens
            if( in_array( $screen->id, $sptotal__['plugin']['screen'] ) ) return true;
            else return false;
        }
        public function settings_page() {
            // check user capabilities
            if ( ! current_user_can( 'manage_options' ) ) return;
          
            // show error/update messages
            settings_errors( 'wporg_messages' );
        
            $settings_class = new SPTotalSettings();
            $settings_class->settings_page();
        }

        /**
		 * Check if the 15 days period passed for the notice key or is it done displaying
		 *
		 * @param string $key option meta key to determing the notice type.
		 */
		public function if_show_notice( $key ) {
			$value = get_option( $key );

			if ( empty( $value ) ) {
				update_option( $key, gmdate( 'Y-m-d' ) );
				return false;
			}

			// if notice is done displaying forever?
			if ( 'done' === $value ) {
				return false;
			}

			// see if interval period passed.
			$difference  = date_diff( date_create( gmdate( 'Y-m-d' ) ), date_create( $value ) );
			$days_passed = (int) $difference->format( '%d' );

			return $days_passed < 15 ? false : true;
		}
    }
}

$loader_class = new SPTotalLoader();
$loader_class->init();
