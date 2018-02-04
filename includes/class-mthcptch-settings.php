<?php
/**
 * Displays the content on the plugin settings page
 */

require_once( dirname( dirname( __FILE__ ) ) . '/bws_menu/class-bws-settings.php' );


if ( ! class_exists( 'mthcptch_Settings_Tabs' ) ) {
	class mthcptch_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $mthcptch_options, $mthcptch_plugin_info;
			$tabs = array(
				'settings' 		=> array( 'label' => __( 'Settings', 'math-captcha' ) ),
				'misc' 			=> array( 'label' => __( 'Misc', 'math-captcha' ) ),
				'custom_code' 	=> array( 'label' => __( 'Custom Code', 'math-captcha' ) )
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $mthcptch_plugin_info,
				'prefix' 			 => 'mthcptch',
				'default_options' 	 => mthcptch_get_options_default(),
				'options' 			 => $mthcptch_options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'math-captcha'
			) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			/* Takes all the changed settings on the plugin's admin page and saves them in array 'mthcptch_options'. */
			global $mthcptch_options, $mthcptch_plugin_info;
			$mthcptch_options['plus']			= isset( $_POST['mthcptch_plus'] ) ? 1 : 0;
			$mthcptch_options['minus']			= isset( $_POST['mthcptch_minus'] ) ? 1 : 0;
			$mthcptch_options['multiply']		= isset( $_POST['mthcptch_multiply'] ) ? 1 : 0;
			$mthcptch_options['divide']			= isset( $_POST['mthcptch_divide'] ) ? 1 : 0;
			$mthcptch_options['authorization']	= isset( $_POST['mthcptch_authorization'] ) ? 1 : 0;
			$mthcptch_options['registration']	= isset( $_POST['mthcptch_registration'] ) ? 1 : 0;
			$mthcptch_options['lost_password']	= isset( $_POST['mthcptch_lost_password'] ) ? 1 : 0;
			$mthcptch_options['comment_form']	= isset( $_POST['mthcptch_comment_form'] ) ? 1 : 0;
			update_option( 'mthcptch_options', $mthcptch_options );

			if ( $mthcptch_options['plus'] == 0 && $mthcptch_options['minus'] == 0 && $mthcptch_options['multiply'] == 0 && $mthcptch_options['divide'] == 0 ) {
				$mthcptch_options['plus'] = 1;
				$notice = __( 'At least one math operation must be selected!', 'math-captcha' );
			}
			$message = __( 'Settings saved.', 'math-captcha' );
			update_option( 'mthcptch_options', $mthcptch_options );
			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Tab settings
		 */
		public function tab_settings() {
			global $mthcptch_options;
			$mthcptch_options = get_option( 'mthcptch_options' ); ?>
			<h3 class="bws_tab_label"><?php _e( 'Math Captcha Settings', 'math-captcha' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<form class="mthcptch_option_form" method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php _e( 'Select math action', 'math-captcha' ) ?>
							</th>
							<td>
								<fieldset>
									<label><input name="mthcptch_plus" type="checkbox" class="plus_checkbox" <?php checked ( 1 == $mthcptch_options['plus']); ?>/> <?php _e( 'Plus', 'math-captcha' ) ?></label><br>
									<label><input name="mthcptch_minus" type="checkbox" class="minus_checkbox" <?php checked ( 1 == $mthcptch_options['minus']); ?>/> <?php _e( 'Minus', 'math-captcha' ) ?></label><br>
									<label><input name="mthcptch_multiply" type="checkbox" class="multiply_checkbox" <?php checked ( 1 == $mthcptch_options['multiply']); ?>/><?php _e( 'Multiply', 'math-captcha' )  ?></label><br>
									<label><input name="mthcptch_divide" type="checkbox" class="divide_checkbox" <?php checked ( 1 == $mthcptch_options['divide']); ?>/><?php _e( 'Divide', 'math-captcha' )  ?></label><br>
								</fieldset>
							</td>
						<tr/>
						<br/>
						<tr>
							<th>
								<?php _e( 'Insert captcha to:', 'math-captcha' ) ?>
							</th>
							<td>
								<fieldset>
									<label><input name="mthcptch_authorization" type="checkbox" class="authorization_checkbox" <?php checked ( 1 == $mthcptch_options['authorization']); ?>/> <?php _e( 'Authorization page', 'math-captcha' ) ?></label><br>
									<label><input name="mthcptch_registration" type="checkbox" class="registration_checkbox" <?php checked ( 1 == $mthcptch_options['registration']); ?>/> <?php _e( 'Registration page', 'math-captcha' ) ?></label><br>
									<label><input name="mthcptch_lost_password" type="checkbox" class="lost_password_checkbox" <?php checked ( 1 == $mthcptch_options['lost_password']); ?>/><?php _e( 'Lost password page', 'math-captcha' )  ?></label><br>
									<label><input name="mthcptch_comment_form" type="checkbox" class="comment_form_checkbox" <?php checked ( 1 == $mthcptch_options['comment_form']); ?>/><?php _e( 'Comment form', 'math-captcha' )  ?></label><br>
								</fieldset>
							</td>
						</tr>
					<tbody/>
				</table>
			</form>
		<?php }
	}
}
