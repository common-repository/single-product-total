;(function($, window, document, undefined) {
    /**
     * ProductTotal class which handles custom form interactions and attributes.
    */
   var ProductTotal = function() {
        var self = this;

        // Properties
        self.$quantity   = {};
        self.$totalPrice = {};
        self.$priceWrap  = {};
        
        // Methods
        self.onQuantityChange  = self.onQuantityChange.bind( self );
        self.onVariationChange = self.onVariationChange.bind( self );
        self.onDocumentReady   = self.onDocumentReady.bind( self );
        
        self.initParams        = self.initParams.bind( self );
        self.sptotalInit       = self.sptotalInit.bind( self );

        self.setTotal        = self.setTotal.bind( self );
        self.simpleTotal     = self.simpleTotal.bind( self );
        self.groupTotal      = self.groupTotal.bind( self );
        self.getPrice        = self.getPrice.bind( self );
        self.parsePrice      = self.parsePrice.bind( self );
        self.update_total    = self.update_total.bind( self );
        self.missedVariation = self.missedVariation.bind( self );
        
        // Events
        this.initParams();
        $(document).ready( self.onDocumentReady );
        self.$quantity.on( 'change input', self.onQuantityChange );
        $(document).on( 'change', '.variations select', self.onVariationChange );
    };

    /**
     * Handles quantity change and input events.
     */
    ProductTotal.prototype.onQuantityChange = function(e) {
        this.setTotal();
    };

    /**
     * Handles variation change events.
     */
    ProductTotal.prototype.onVariationChange = function() {
        this.setTotal();
    };

    /**
     * Find appropriate HTML wrappers as they depends on themes
     */
    ProductTotal.prototype.initParams = function(){
        this.$quantity   = $(document).find( 'form.cart .quantity .qty' );
        this.$priceWrap  = $( '.wp-block-columns .wp-block-woocommerce-product-price' );
        this.$totalPrice = $(document).find( '.sptotal' );

        // Special cases like different themes and plugins that may change default HTML.
        if( 0 === this.$priceWrap.length ){
            this.$priceWrap = $(document).find( 'p.price' );
        }

        // if no wrap found, fix it
        if ( 0 === this.$totalPrice.length && this.$priceWrap.length > 0 ) {
            if( 'before_price' === sptotal_data['settings']['position'] ){
                this.$priceWrap.before( sptotal_data['html'] );
            }else if( 'after_price' === sptotal_data['settings']['position'] ){
                this.$priceWrap.after( sptotal_data['html'] );
            }

            this.$totalPrice = $(document).find( '.sptotal' );
        }
    }

    /**
     * Handles document ready event.
     */
    ProductTotal.prototype.onDocumentReady = function() {
        this.setTotal();
    };
    
    /**
     * Initialize total price update functionality
    */
   ProductTotal.prototype.setTotal = function() {
       setTimeout( () => {
           this.sptotalInit();
        //    this.initParams();

            if ( 'grouped' === sptotal_data.type ) {
                this.groupTotal();
            } else {
                this.simpleTotal();
            }
        }, 500 );
    };

    /**
     * Calculate single product price total.
     */
    ProductTotal.prototype.simpleTotal = function() {
        var quantity = parseInt( $( 'form.cart .quantity .qty' ).val() );
        if ( ! quantity ) {
            quantity = 0;
        }
        
        var price = this.getPrice( this.$priceWrap );
        var total = parseFloat( price * quantity );

        // why is this necessary? I forgot.
        if ( this.missedVariation() ) {
            total = 0.0;
        }

        this.update_total( total );
    };

    /**
     * Calculate grouped product total.
     */
    ProductTotal.prototype.groupTotal = function() {
        var total = 0.0;
        var self  = this;

        $( '.woocommerce-grouped-product-list.group_table tr' ).each( function() {
            var qty = $(this).find( 'input[type="number"]' ).val();
            if ( qty ) {
                qty = parseInt( qty, 10 );
            } else {
                qty = 0;
            }

            var price = self.getPrice( $(this) );

            if ( qty && price ) {
                total += ( qty * price );
            }
        });

        this.update_total( total );
    };

    
    /**
     * Re evaluate correctt price wrapper
     */
    ProductTotal.prototype.sptotalInit = function(){
        var variations = $(document).find( '.single_variation_wrap' );
        
        if ( variations.length ) {
            this.$priceWrap = variations.find( '.woocommerce-variation-price' );
        }
        
        if ( this.$priceWrap.text().length === 0 || 0 === this.$priceWrap.length ) {
            this.$priceWrap = $(document).find( 'p.price' );
        }
    }

    /**
     * Get product price
     */
    ProductTotal.prototype.getPrice = function( wrap ) {
        var price = 0.0;
        if( 0 === wrap.length ){
            return price;
        }
        
        var text = '';
        if( wrap.find( 'ins .woocommerce-Price-amount' ).length ){
            text = wrap.find( 'ins .woocommerce-Price-amount' ).last().text();
        }else if( wrap.find( '.woocommerce-Price-amount' ).not( 'del .woocommerce-Price-amount' ).length ){
            text = wrap.find( '.woocommerce-Price-amount' ).not( 'del .woocommerce-Price-amount' ).last().text();
        }else{
            text = wrap.find( '.woocommerce-Price-amount' ).last().text();
        }
        
        return this.parsePrice( text );
    }

    /**
     * Convert string price to float
     *
     * @param {string} text given price.
     */
    ProductTotal.prototype.parsePrice = function( text ){
        var price = 0.00;

        // parse text to find price.
        text = text.replace( /[^\d.,]/g, '' ); // filter out number parts from string.
        text = text.replace( sptotal_data['ts'], '' ); // thousand separator.
        text = text.replace( sptotal_data['ds'], '.' ); // decimal separator.

        if( text ){
            price = parseFloat( text );
        }

        return price;
    }

    /**
     * Update total price html
     */
    ProductTotal.prototype.update_total = function( total ){
        let price = parseFloat( total ).toFixed( sptotal_data['dp'] );
        price = price.replace( '.', sptotal_data['ds'] );
        price = price.replace( /\B(?=(\d{3})+(?!\d))/g, sptotal_data['ts'] );

        this.$totalPrice.find( '.total-price' ).text( price );
    }

    /**
     * Check if all variation attributes are selcted
     */
    ProductTotal.prototype.missedVariation = function(){
        var missed = false;

        var wrap = $(document).find( 'table.variations' );
        if( ! wrap.length ){
            return missed;
        }

        var total = 0, selected = 0;
        wrap.find( 'tr' ).each( function(){
            var select = $(this).find( 'select' );
            if( select.length ){
                total++;
                if( select.find( 'option:selected' ).val().length ){
                    selected++;
                }
            }
        });

        if( total === selected ){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Initialize product total class
     */
    $(function() {
        new ProductTotal();
	});

})(jQuery, window, document);
