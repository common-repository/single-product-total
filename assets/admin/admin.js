(function($) {

	/**
	 * using global variable
	 * @param sptotal_admin_data
	 */

	// sticky header/menu
    $(window).on( 'scroll', function(){
        if( $(window).scrollTop() > 40 ){
            $( '.sptotal-wrap' ).addClass( 'sptotal-sticky-top' );
        }else{
            if( $( '.sptotal-wrap' ).hasClass( 'sptotal-sticky-top' ) ){
                $( '.sptotal-wrap' ).removeClass( 'sptotal-sticky-top' );
            }
        }
    });
    
    $( document ).ready( function(){
        $( '.sptotal-colorpicker' ).wpColorPicker();
    });

})(jQuery);