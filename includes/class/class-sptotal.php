<?php
/**
 * Single product total frontend class
 *
 * @package    WordPress
 * @subpackage Multiple Products to Cart for WooCommerce
 * @since      2.0
 */

if ( ! class_exists( 'SPTotal' ) ) {
	/**
	 * Plugin frotend class
	 */
	class SPTotal {



        /**
         * Single product total settings data
         *
         * @var array.
         */
        private $settings;



        /**
         * Frontend total price constructor
         */
        public function __construct() {
            $this->settings = array();
            
            // get the position of the total price.
            $this->settings['position'] = get_option( 'sptotal_total_position' );
            if( empty( $this->settings['position'] ) ){
                $this->settings['position'] = 'before_cart_btn';
            }

            // total text.
            $this->settings['label'] = get_option( 'sptotal_total_text' );

            // if show total inline.
            $this->settings['if_inline'] = get_option( 'spline_if_inline' );
            
        }

		/**
		 * Init hook
		 */
		public function init() {
            if( 'before_price' === $this->settings['position'] ){
                add_action( 'woocommerce_single_product_summary', array( $this, 'display_total' ), 9 );
            }elseif( 'after_price' === $this->settings['position'] ){
                add_action( 'woocommerce_single_product_summary', array( $this, 'display_total' ), 11 );
            }elseif( 'after_cart_btn' === $this->settings['position'] ){
                add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'display_total' ) );
            }else{
                add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_total' ) );
            }
        }


        
        /**
         * Display total price
         */
        public function display_total(){
            global $post;
            global $product;

            if( empty( $product ) || empty( $post ) || 'product' !== $post->post_type ){
                return;
            }

            if( 'object' !== $product ){
                $product = wc_get_product( $post->ID );
            }

            // skip from external/affiliate products.
            if( 'external' === $product->get_type() ){
                return;
            }

            $c = $this->get_color();

            if( false !== strpos( $this->settings['position'], 'fixed' ) ){
                $this->settings['position'] .= ' fixed';
            }

            $html = wp_kses_post(
                sprintf(
                    '<div class="sptotal %s %s" style="%s">',
                    esc_attr( $this->settings['position'] ),
                    esc_attr( $c['class'] ),
                    isset( $c['background'] ) && ! empty( $c['background'] ) ? esc_html( $c['background'] ) : ''
                )
            );
                
            if( ! empty( $this->settings['label'] ) ){
                $html .= wp_kses_post(
                    sprintf(
                        '<label style="%s">%s</label>',
                        isset( $c['label'] ) && ! empty( $c['label'] ) ? esc_html( $c['label'] ) : '',
                        esc_html( $this->settings['label'] )
                    )
                );
            }

            $html .= wp_kses_post(
                sprintf(
                    '<div class="sptotal-price" style="%s">%s</div>',
                    isset( $c['price'] ) && ! empty( $c['price'] ) ? esc_html( $c['price'] ) : '',
                    $this->total_price( $product->get_price() )
                )
            );

            $html .= '</div>';

            echo wp_kses_post( $html );

        }

        /**
         * Return total price html
         *
         * @param string $price product price.
         */
        public function total_price( $price ){
            $price = number_format(
                $price,
                wc_get_price_decimals(),
                wc_get_price_decimal_separator(),
                wc_get_price_thousand_separator()
            );

            $pos  = get_option( 'woocommerce_currency_pos' ) ?? 'left_space';
            $html = sprintf( '<span class="total-price">%s</span>', esc_attr( $price ) );

            $currency = 'right_space' ? '&nbsp;%s' : '%s';
            $currency = 'left_space' ? '%s&nbsp;' : $currency;
            $currency = '<span class="currency">' . $currency . '</span>';

            if( 'right' === $pos || 'right_space' === $pos ){
                $html = $html . $currency;
            }else{
                $html = $currency . $html;
            }

            $html = '<bdi>' . $html . '</bdi>';

            return wp_kses_post( sprintf(
                $html,
                get_woocommerce_currency_symbol()
            ) );
        }

        /**
         * Get color settings
         */
        public function get_color(){
            $w = '#ffffff'; // white color.

            $lc = get_option( 'sptotal_label_color' );
            $pc = get_option( 'sptotal_price_color' );
            $bg = get_option( 'sptotal_background' );
            $ta = get_option( 'sptotal_text_align' );

            if( ( ! empty( $lc ) && $lc === $bg ) || ( empty( $bg ) && $lc === $w ) ){
                $lc = '';
            }
            if( ( ! empty( $pc ) && $pc === $bg ) || ( empty( $bg ) && $pc === $w ) ){
                $pc = '';
            }

            $d = array(
                'label'      => ! empty( $lc ) ? 'color: ' . $lc . ';' : '',
                'price'      => ! empty( $pc ) ? 'color: ' . $pc . ';' : '',
                'background' => ! empty( $bg ) ? 'background: ' . $bg . ';' : '',
                'class'      => 'on' === $this->settings['if_inline'] ? 'sptotal-block' : ''
            );

            $d['class'] .= ! empty( $bg ) ? ' has-color' : '';
            $d['background'] .= ! empty( $ta ) ? 'text-align: ' . esc_attr( $ta ) . ';' : '';

            return $d;
        }
    }    
}

$front_class = new SPTotal();
$front_class->init();
