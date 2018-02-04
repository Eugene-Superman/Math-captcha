(function($) {
	$(document).ready( function() {
		$('body').on( 'click', '.mthcptch_reload', function(){
				var data = {
					action: 'display_captcha',
					security : ajaxObject.nonce
				};
			$.post( ajaxObject.url, data, function( response ) {
				$( ".mthcptch_block" ).replaceWith( response );
			});
		});

		$('.mthcptch_check').click( function( event ) {
			event.preventDefault();
			var data = {
				action: 'mthcptch_check',
				mthcptch_check_field : $( '[name=mthcptch_check_field]' ).val(),
				mthcptch_input_data : $( '[name=mthcptch_input_data]' ).val(),
				security : ajaxObject.nonce
			};
			$.post(ajaxObject.url, data, function( response ) {
				if(response == 'True'){
					$('.notification').text(response);
				} else {
					$('.notification').text(response);
				}
				$('.mthcptch_reload' ).trigger( 'click' );
			});
		});
	});
})(jQuery);
