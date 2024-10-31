<?php
/**
 * Plugin Name: Restrict Content Pro - Enforce Strong Passwords
 * Plugin URI: https://restrictcontentpro.com/downloads/enforce-strong-passwords/
 * Description: Forces users to register with strong passwords
 * Author: iThemes, LLC
 * Author URI: https://ithemes.com
 * Contributors: jthillithemes, layotte, ithemes
 * Version: 1.1.3
 * Text Domain: rcp-strong-passwords
 * iThemes Package: rcp-strong-passwords
 */

class RCP_Strong_Passwords {

	/**
	 * Get things going
	 *
	 * @since	1.0
	 * @return	void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_footer', array( $this, 'load_scripts' ) );
		add_action( 'rcp_form_errors', array( $this, 'check_password' ) );
		add_action( 'rcp_edit_profile_form_errors', array( $this, 'check_edit_profile_password' ), 10, 2 );
		add_action( 'rcp_password_form_errors', array( $this, 'check_password_reset' ) );

	}

	/**
	 * Load plugin text domain for translations
	 *
	 * @access public
	 * @since 1.0.4
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'rcp_strong_passwords_languages_directory', $lang_dir );

		// Load the translations
		load_plugin_textdomain( 'rcp-strong-passwords', false, $lang_dir );

	}

	/**
	 * Register scripts and styles for later use
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function register_scripts() {

		wp_register_style( 'rcp-strong-passwords', plugin_dir_url( __FILE__ ) . 'assets/css/password-strength.css', array(), '1.1' );

		wp_register_script( 'rcp-strong-passwords', plugin_dir_url( __FILE__ ) . 'assets/js/password-strength.js', array( 'jquery' ), '1.1.1', true );

	}

	/**
	 * Load scripts and styles on registration page
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function load_scripts() {

		global $rcp_load_css, $rcp_load_scripts, $rcp_options;

		if ( $rcp_load_css && empty( $rcp_options['disable_css'] ) ) {
			wp_print_styles( 'rcp-strong-passwords' );
		}

		if ( $rcp_load_scripts || get_the_ID() == $rcp_options['edit_profile'] ) {
			$current_user = wp_get_current_user();

			wp_localize_script( 'rcp-strong-passwords', 'rcp_strong_passwords', array(
				1              => __( 'Very Weak', 'rcp-strong-passwords' ),
				2              => __( 'Weak', 'rcp-strong-passwords' ),
				3              => __( 'Medium', 'rcp-strong-passwords' ),
				4              => __( 'Strong', 'rcp-strong-passwords' ),
				'username'     => $current_user->user_login,
				'requirements' => __( 'Your password must be at least 9 characters long and include letters, numbers, and symbols.', 'rcp-strong-passwords' )
			) );

			wp_print_scripts( 'rcp-strong-passwords' );
		}

	}

	/**
	 * Checks for a strong password during registration
	 *
	 * @since	1.0
	 * @param	$data Data sent from the registration form
	 * @return	void
	 */
	public function check_password( $data ) {

		if( is_user_logged_in() ) {
			return;
		}

		if ( $this->password_strength( $data['rcp_user_pass'], $data['rcp_user_login'] ) != 4 ) {
			rcp_errors()->add( 'weak_password', __( 'Your password must be at least 9 characters long and include letters, numbers, and symbols.', 'rcp-strong-passwords' ), 'register' );
		}
	}

	/**
	 * Checks for a strong password during profile edit
	 *
	 * @param array $data    Posted data.
	 * @param int   $user_id ID of the user editing their profile.
	 *
	 * @since 1.0.4
	 * @return void
	 */
	public function check_edit_profile_password( $data, $user_id ) {

		// Bail if they're not changing their password.
		if ( empty( $data['rcp_new_user_pass1'] ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		if ( $this->password_strength( $data['rcp_new_user_pass1'], $user->user_login ) != 4 ) {
			rcp_errors()->add( 'weak_password', __( 'Your password must be at least 9 characters long and include letters, numbers, and symbols.', 'rcp-strong-passwords' ) );
		}

	}

	/**
	 * Checks for a strong password during password reset
	 *
	 * @param array $data Posted data.
	 *
	 * @since 1.0.4
	 * @return void
	 */
	public function check_password_reset( $data ) {

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
		} else {
			$rp_cookie = apply_filters( 'rcp_resetpass_cookie_name', 'rcp-resetpass-' . COOKIEHASH );
			$user      = rcp_get_user_resetting_password( $rp_cookie );
		}

		$username = is_object( $user ) ? $user->user_login : '';

		if ( $this->password_strength( $data['rcp_user_pass'], $username ) != 4 ) {
			rcp_errors()->add( 'weak_password', __( 'Please use a strong password', 'rcp-strong-passwords' ), 'password' );
		}

	}

	/**
	 * Check for password strength
	 *
	 * @since	1.0
	 * @param	$pass     string The password
	 * @param	$username string The user's username
	 * @return	integer	1 = very weak; 2 = weak; 3 = medium; 4 = strong
	 */
	function password_strength( $pass, $username ) {
		$h = 1; $e = 2; $b = 3; $a = 4; $d = 0; $g = null; $c = null;
		if ( strlen( $pass ) < 4 )
			return $h;
		if ( strtolower( $pass ) == strtolower( $username ) )
			return $e;
		if ( preg_match( "/[0-9]/", $pass ) )
			$d += 10;
		if ( preg_match( "/[a-z]/", $pass ) )
			$d += 26;
		if ( preg_match( "/[A-Z]/", $pass ) )
			$d += 26;
		if ( preg_match( "/[^a-zA-Z0-9]/", $pass ) )
			$d += 31;
		$g = log( pow( $d, strlen( $pass ) ) );
		$c = $g / log( 2 );
		if ( $c < 40 )
			return $e;
		if ( $c < 56 )
			return $b;
		return $a;
	}

}
$rcp_strong_passwords = new RCP_Strong_Passwords;

if ( ! function_exists( 'ithemes_rcp_strong_passwords_updater_register' ) ) {
	function ithemes_rcp_strong_passwords_updater_register( $updater ) {
		$updater->register( 'REPO', __FILE__ );
	}
	add_action( 'ithemes_updater_register', 'ithemes_rcp_strong_passwords_updater_register' );

	require( __DIR__ . '/lib/updater/load.php' );
}