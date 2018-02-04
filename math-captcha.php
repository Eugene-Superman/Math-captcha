<?php
/*
Plugin Name: Math Captcha
Plugin URI: https://bestwebsoft.com/products/
Description: Math captcha plugin.
Author: BestWebSoft
Text Domain: math-captcha
Domain Path: /languages
Version: 1.0.0
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2017  BestWebSoft  ( https://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Add BWS menu */
if ( ! function_exists( 'mthcptch_add_pages' ) ) {
	function mthcptch_add_pages() {
		$settings = add_menu_page( __( 'Math Captcha Settings', 'math-captcha' ), 'Math Captcha', 'manage_options', 'math-captcha.php', 'mthcptch_settings_page', 'dashicons-lightbulb' );
	}
}

/* Load plugin textdomain */
if ( ! function_exists( 'mthcptch_plugins_loaded' ) ) {
	function mthcptch_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'math-captcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/* Initialization */
if ( ! function_exists( 'mthcptch_init' ) ) {
	function mthcptch_init() {
		global $mthcptch_plugin_info, $mthcptch_options;

		if ( empty( $mthcptch_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$mthcptch_plugin_info = get_plugin_data( __FILE__ );
		}
		/* add general functions */
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		/* check compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $mthcptch_plugin_info, '3.9' );
		/* Get/Register and check settings for plugin */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && 'math-captcha.php' == $_GET['page'] ) ) {
			mthcptch_settings();
		}
		if ( $mthcptch_options['authorization'] == 1 ) {
			add_action ( 'login_form', 'display_captcha' );
			add_filter( 'authenticate', 'mthcptch_login_form_check', 31, 3 );
		}
		if ( $mthcptch_options['registration'] == 1 ) {
			add_filter( 'registration_errors', 'mthcptch_registr_form_check' );
			add_action ( 'register_form', 'display_captcha' );
			add_action ( 'signup_extra_fields', 'display_captcha' );
			add_filter ( 'wpmu_validate_user_signup', 'mthcptch_signup_check' );
		}
		if ( $mthcptch_options['lost_password'] == 1 ) {
			add_action ( 'lostpassword_form', 'display_captcha' );
			add_filter ( 'allow_password_reset', 'mthcptch_lost_check' );
		}
		if ( $mthcptch_options['comment_form'] == 1 ) {
			add_action ( 'comment_form', 'display_captcha' );
			add_filter ( 'preprocess_comment', 'mthcptch_comment_check' );
		}
	}
}

/* Function for admin_init */
if ( ! function_exists( 'mthcptch_admin_init' ) ) {
	function mthcptch_admin_init() {
		/* Add variable for bws_menu */
		global $bws_plugin_info, $mthcptch_plugin_info, $bws_shortcode_list;
		/* Function for bws menu */
		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '***', 'version' => $mthcptch_plugin_info['Version'] );
		}
		/* add Plugin to global $bws_shortcode_list */
		$bws_shortcode_list['mthcptch'] = array( 'name' => 'Math Captcha' );
	}
}

/* Gets settings */ 
if ( ! function_exists( 'mthcptch_settings' ) ) {
	function mthcptch_settings() {
		global $mthcptch_options, $mthcptch_plugin_info;
		/* Install the option defaults */
		if ( ! get_option( 'mthcptch_options' ) ) {
			$options_default = mthcptch_get_options_default();
			add_option( 'mthcptch_options', $options_default );
		}
		/* Get options from the database */
		$mthcptch_options = get_option( 'mthcptch_options' );
		if ( ! isset( $mthcptch_options['plugin_option_version'] ) || $mthcptch_options['plugin_option_version'] != $mthcptch_plugin_info['Version'] ) {
			$options_default = mthcptch_get_options_default();
			$mthcptch_options = array_merge( $options_default, $mthcptch_options );
			$mthcptch_options['plugin_option_version'] = $mthcptch_plugin_info['Version'];
			$update_option = true;
		}
		if ( isset( $update_option ) )
			update_option( 'mthcptch_options', $mthcptch_options );
	}
}

/* Get default options */
if ( ! function_exists( 'mthcptch_get_options_default' ) ) {
	function mthcptch_get_options_default() {
		global $mthcptch_plugin_info;
		$default_options = array(
			'plugin_option_version'		=> $mthcptch_plugin_info['Version'],
			'display_settings_notice'	=> 1,
			'suggest_feature_banner'	=> 1,
			'plus'						=> 1,
			'minus'						=> 1,
			'multiply'					=> 1,
			'divide'					=> 1,
			'authorization' 			=> 1,
			'registration' 				=> 1,
			'lost_password' 			=> 1,
			'comment_form' 				=> 1
		);
		return $default_options;
	}
}

/* Function formed content of the plugin's admin page. */
if ( ! function_exists( 'mthcptch_settings_page' ) ) {
	function mthcptch_settings_page() {
		require_once( dirname( __FILE__ ) . '/includes/class-mthcptch-settings.php' );
		$page = new mthcptch_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>
		<div class="wrap">
			<h1>Math captcha <?php _e( 'Settings', 'math-captcha' ); ?></h1>
			<?php $page->display_content(); ?>
		</div>
	<?php }
}

/* Add shortcode content  */
if ( ! function_exists( 'mthcptch_shortcode_button_content' ) ) {
	function mthcptch_shortcode_button_content( $content ) { ?>
		<div id="mthcptch" style="display:none;">
			<fieldset>
				<?php _e( 'Add buttons to your page or post', 'math-captcha' ); ?>
			</fieldset>
			<input class="bws_default_shortcode" type="hidden" name="default" value="[math_captcha_form]" />
			<div class="clear"></div>
		</div>
	<?php }
}

/* Scripts and style connection */
if ( ! function_exists( 'mthcptch_admin_head' ) ) {
	function mthcptch_admin_head() {
		wp_enqueue_script( 'mthcptch_script', plugin_dir_url( __FILE__ ) . 'js/script.js', array ( 'jquery' ), 1, true);
		wp_localize_script( 'mthcptch_script', 'ajaxObject',
		 	array(
				'url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'ajaxNonce' )
			)
		);
		wp_enqueue_style( 'mthcptch_stylesheet', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		if ( isset( $_GET['page'] ) && 'math-captcha.php' == $_GET['page'] ) {
			bws_enqueue_settings_scripts();
			bws_plugins_include_codemirror();
		}
	}
}

/* Functions creates other links on plugins page. */
if ( ! function_exists( 'mthcptch_action_links' ) ) {
	function mthcptch_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=math-captcha.php">' . __( 'Settings', 'math-captcha' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/* Return links */
if ( ! function_exists ( 'mthcptch_links' ) ) {
	function mthcptch_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=math-captcha.php">' . __( 'Settings', 'math-captcha' ) . '</a>';
			$links[]	=	'<a href="https://wordpress.org/plugins/math-captcha/faq/" target="_blank">' . __( 'FAQ', 'math-captcha' ) . '</a>';
			$links[]	=	'<a href="https://support.bestwebsoft.com">' . __( 'Support', 'math-captcha' ) . '</a>';
		}
		return $links;
	}
}

/* Add help tab  */
if ( ! function_exists( 'mthcptch_add_tabs' ) ) {
	function mthcptch_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id' 			=> 'mthcptch',
			'section' 		=> ''
		);
		bws_help_tab( $screen, $args );
	}
}

/* Display banner */
if ( ! function_exists ( 'mthcptch_plugin_banner' ) ) {
	function mthcptch_plugin_banner() {
		global $hook_suffix, $mthcptch_plugin_info;
		if ( 'plugins.php' == $hook_suffix ) {
			if ( ! is_network_admin() )
				bws_plugin_banner_to_settings( $mthcptch_plugin_info, 'mthcptch_options', 'math-captcha', 'admin.php?page=math-captcha.php' );
		}
		if ( isset( $_REQUEST['page'] ) && 'math-captcha.php' == $_REQUEST['page'] ) {
			bws_plugin_suggest_feature_banner( $mthcptch_plugin_info, 'mthcptch_options', 'math-captcha' );
		}
	}
}

/* Function for delete options */
if ( ! function_exists( 'mthcptch_delete_options' ) ) {
	function mthcptch_delete_options() {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			global $wpdb;
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'mthcptch_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'mthcptch_options' );
		}
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

/* Plus operation  */
if ( ! function_exists( 'mthcptch_plus' ) ) {
	function mthcptch_plus() {
		do {
			$numbers[0] = rand( 0, 10 );
			$numbers[1] = rand( 0, 10 );
		} while ( $numbers[0] + $numbers[1] > 10 );
		$numbers[2] = $numbers[0] + $numbers[1];
		return $numbers;
	}
}

/* Minus operation  */
if ( ! function_exists( 'mthcptch_minus' ) ) {
	function mthcptch_minus() {
		do {
			$numbers[0] = rand( 1, 10 );
			$numbers[1] = rand( 0, 10 );
		} while ( $numbers[0] - $numbers[1] < 0 );
		$numbers[2] = $numbers[0] - $numbers[1];
		return $numbers;
	}
}

/* Multiply operation  */
if ( ! function_exists( 'mthcptch_multiply' ) ) {
	function mthcptch_multiply() {
		do {
			$numbers[0] = rand( 1, 10 );
			$numbers[1] = rand( 1, 10 );
		} while ( $numbers[0] * $numbers[1] > 10 );
		$numbers[2] = $numbers[0] * $numbers[1];
		return $numbers;
	}
}

/* Divide operation */ 
if ( ! function_exists( 'mthcptch_divide' ) ) {
	function mthcptch_divide() {
		do {
			$numbers[0] = rand( 1, 10 );
			$numbers[1] = rand( 1, 10 );
		} while ( $numbers[0] / $numbers[1] < 0  || is_float($numbers[0] / $numbers[1]) || $numbers[1] == 0 );
		$numbers[2] = $numbers[0] / $numbers[1];
		return $numbers;
	}
}

/* Selection of math operations */
if ( ! function_exists( 'mthcptch_action' ) ) {
	function mthcptch_action() {
		$mthcptch_options = get_option( 'mthcptch_options' );
		$char_count = array();
		$i = -1;
		$answer_count = array();
		if ( $mthcptch_options['plus'] == 1 ) {
			$i++;
			$plus_action = apply_filters( 'plus', true );
			$char_count[$i] = ' + ';
			$answer_count[$i] = $plus_action;
		} ;
		if ( $mthcptch_options['minus'] == 1 ) {
			$i++;
			$minus_action = apply_filters( 'minus', true );
			$char_count[$i] = ' - ';
			$answer_count[$i] = $minus_action;
		} ;
		if ( $mthcptch_options['multiply'] == 1 ) {
			$i++;
			$multiply_action = apply_filters( 'multiply', true );
			$char_count[$i] = ' * ';
			$answer_count[$i] = $multiply_action;
		} ;
		if ( $mthcptch_options['divide'] == 1 ) {
			$i++;
			$divide_action = apply_filters( 'divide', true );
			$char_count[$i] = ' / ';
			$answer_count[$i] = $divide_action;
		} ;
		$rnd= rand( 0, $i );
		$numbers = $answer_count[$rnd];
		$char= $char_count[$rnd];
		$action_array =  array(
			'0' => $numbers,
			'1' => $char
		);
		return $action_array;
	}
}

/* Display form */
if ( ! function_exists( 'display_form' ) ) {
	function display_form() { ?>
		<div class="mthcptch_content">
			<form class="mthcptch" method="post">
				<p class="notification"> <?php _e( 'Write right number', 'math-captcha' ) ?> </p>
				<?php do_action( 'before_form_submit' );
				wp_nonce_field( 'mthcptch_nonce_action', 'mthcptch_nonce_field', false ); ?>
				<input type="submit" name="mthcptch_check_button" class="mthcptch_check" value="<?php _e( 'check', 'math-captcha' ) ?>"/>
			</form>
		</div>
	<?php }
}

/* Display captcha */
if ( ! function_exists( 'display_captcha' ) ) {
	function display_captcha() {
		$mthcptch_options = get_option( 'mthcptch_options' );
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			check_ajax_referer( 'ajaxNonce', 'security' );
			$is_ajax = true;
		}
		$action = apply_filters( 'math_operation', true );
		$rand_index = rand( 0, 2 );
		$numbers = $action[0];
		$char= $action[1];
		$saved_number = $numbers[$rand_index];
		$numbers[$rand_index] = '<input name="mthcptch_input_data" class="mthcptch_input_data" type="text" maxlength="2"/>';
		if ( isset($_POST['mthcptch_check_button']) ) {
			if ( ! wp_verify_nonce( $_POST['mthcptch_nonce_field'], 'mthcptch_nonce_action' ) ) {
				_e( 'Sorry, your nonce did not verify.', 'math-captcha' );
				exit;
			} else {
				$check = apply_filters( 'check_answer', true );
			}
		}
		$captcha =
			'<div class="mthcptch_block">
				<p>'. $numbers[0] . $char . $numbers[1].' = ' . $numbers[2] . '</p>'.
				wp_nonce_field( 'mthcptch_check_action' . $saved_number,
				'mthcptch_check_field', false, false ).
				'<input type="button" class="mthcptch_reload" name="mthcptch_reload" value="' . __( 'reload captcha', 'math-captcha' ) . '"/><br />' .
			'</div>';
		echo $captcha;
		if ( isset( $is_ajax ) ) exit;
	}
}

/* Answer verification */
if ( ! function_exists( 'mthcptch_check' ) ) {
	function mthcptch_check( $check = true ) {
		$mthcptch_options = get_option( 'mthcptch_options' );
		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if (
			empty( $_POST['mthcptch_check_field'] ) || ! isset( $_POST['mthcptch_input_data'] ) ||
			! wp_verify_nonce( $_POST['mthcptch_check_field'], 'mthcptch_check_action' . absint( $_POST['mthcptch_input_data']) )
		) {
			$check = false;
			if ( $is_ajax ) {
				_e( 'False', 'math-captcha' );
				exit;
			}
		}
		if ( $is_ajax ) {
			_e( 'True', 'math-captcha' );
			exit;
		}
		return $check;
	}
}

/* Answer verification for login form */
if ( ! function_exists( 'mthcptch_login_form_check' ) ) {
	function mthcptch_login_form_check( $user ,$username, $password ) {
		$login_check = apply_filters( 'check_answer', true );
		if ( empty($username) && empty($password) )
		return $user;
		if ( ! $login_check ) {
			$user = new WP_Error( 'captcha_fail', __( 'Failed!!!', 'math-captcha' ) );
		}
		return $user;
	}
}

/* Answer verification for registration form */
if ( ! function_exists( 'mthcptch_registr_form_check' ) ) {
	function mthcptch_registr_form_check ( $errors ) {
		$registr_check = apply_filters( 'check_answer', true );
		if ( ! $registr_check ) {
			$errors = new WP_Error( 'captcha_fail', __( 'Failed!!!', 'math-captcha' ) );
		}
		return $errors;
	}
}

/* Answer verification for lost password form */
if ( ! function_exists( 'mthcptch_lost_check' ) ) {
	function mthcptch_lost_check ( $allow ) {
		$lost_password_check = apply_filters( 'check_answer', true );
		if ( ! $lost_password_check ) {
			$allow = new WP_Error( 'captcha_fail', __( 'Failed!!!', 'math-captcha' ) );
		}
		return $allow;

	}
}

/* Answer verification for comment form */
if ( ! function_exists( 'mthcptch_comment_check' ) ) {
	function mthcptch_comment_check ( $commentdata ) {
		$password_check = apply_filters( 'check_answer', true );
		if ( ! $password_check ) {
			$error = new WP_Error( 'captcha_fail', __( 'Failed!!!', 'math-captcha' ) );
			wp_die( '<p>' . $error->get_error_message() . '</p>', __( 'Comment Submission Failure' ), array( 'back_link' => true ) );
		} else
		return $commentdata;
	}
}

/* Answer verification for multisite */
if ( ! function_exists( 'mthcptch_signup_check' ) ) {
	function mthcptch_signup_check ( $result) {
		$signup_check = apply_filters( 'check_answer', true );
		if ( ! $signup_check ) {
			$result['errors']->add( 'signup_error', 'signup was failed' );
			add_action ( 'signup_extra_fields', 'mthcptch_display_error' );
		}
		return $result;
	}
}

/* Display error */
if ( ! function_exists( 'mthcptch_display_error' ) ) {
	function mthcptch_display_error ( $errors) {
		$error_message = $errors->get_error_message( 'signup_error' );
		if ($error_message != '' ) {
			echo '<p class="error">' . $error_message . '</p>';
		}
	}
}

/* Calling a function add administrative menu. */
add_action( 'admin_menu', 'mthcptch_add_pages' );
add_action( 'plugins_loaded', 'mthcptch_plugins_loaded' );
add_action( 'init', 'mthcptch_init' );
add_action( 'admin_init', 'mthcptch_admin_init' );
/* Adding stylesheets */
add_action( 'wp_enqueue_scripts', 'mthcptch_admin_head' );
add_action( 'admin_enqueue_scripts', 'mthcptch_admin_head' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'mthcptch_shortcode_button_content' );
/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'mthcptch_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'mthcptch_links', 10, 2 );
/* Adding banner */
add_action( 'admin_notices', 'mthcptch_plugin_banner' );
/* Plugin uninstall function */
register_uninstall_hook( __FILE__, 'mthcptch_delete_options' );
add_shortcode( 'math_captcha_form', 'display_form' );
add_shortcode( 'math_captcha', 'display_captcha' );
/* Math operations*/
add_filter( 'plus', 'mthcptch_plus' );
add_filter( 'minus', 'mthcptch_minus' );
add_filter( 'multiply', 'mthcptch_multiply' );
add_filter( 'divide', 'mthcptch_divide' );
add_filter( 'math_operation', 'mthcptch_action' );
add_filter( 'check_answer', 'mthcptch_check' );
add_action( 'before_form_submit', 'display_captcha' );
add_action( 'wp_ajax_display_captcha', 'display_captcha' );
add_action( 'wp_ajax_nopriv_display_captcha', 'display_captcha' );
add_action( 'wp_ajax_mthcptch_check', 'mthcptch_check' );
add_action( 'wp_ajax_nopriv_mthcptch_check', 'mthcptch_check' );
add_action( 'login_enqueue_scripts', 'mthcptch_admin_head' );
