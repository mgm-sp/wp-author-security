<?php
/**
 * Plugin Name: WP Author Security
 * Text Domain: wp-author-security
 * Domain Path: /languages
 * Description: Protect against user enumeration attacks on author pages and other places where valid user names can be obtained.
 * Author: mgmsp
 * Author URI: https://www.mgm-sp.com
 * Version: 1.2.1
 * License: GPLv3
 * Plugin URI: https://github.com/mgm-sp/wp-author-security
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once (dirname( __FILE__ ) . '/options.php');

/**
 * initialize the plugin
 */
function init() {
    add_action( 'template_redirect', 'check_author_request', 1 );
    add_action( 'rest_api_init', 'check_rest_api', 10 );
    add_action( 'plugins_loaded', 'wp_author_security_load_plugin_textdomain' );
    add_filter( 'login_errors', 'login_error_message', 1 );
    add_action( 'lost_password', 'check_lost_password_error' );
}

/**
 * checks for author parameter in requests and decides whether to block request (404) 
 * or allow to display the requested author profile
 */
function check_author_request() {

    $field = '';
    $value = '';
    $author = get_query_var('author', false);           // when the username is passed, wp will return the existing user id here
    $authorName = get_query_var('author_name', false);

    // matches requests to "/author/<username>"
    if ( $authorName && get_option( 'protectAuthorName' ) != AuthorSettingsEnum::DISABLED ) {
        $field = 'login';
        $value = trim($authorName);
        // matches requests to "?author=<id>"
    } else if ( $author && !$authorName && get_option( 'protectAuthor' ) != AuthorSettingsEnum::DISABLED ) {
        $field = 'id';
        $value = intval($author);        
    } else {
        return;
    }
    
    if(!is_enabled_for_logged_in()) {
        return;
    }

    // load user and check if user exists
    $user = get_user_by( $field, $value );
    if( ! $user ) {
        return;
    }

    $disable = ( $field == 'id' ? isProtected( get_option( 'protectAuthor' ), $user ) : isProtected( get_option( 'protectAuthorName' ), $user ) );
    
    // when protection is enabled display 404
    if( $disable ) {
        display_404();  
    }

    return;    
}

/**
 * disables user enumeration for the REST API endpoint wp-json/wp/v2/users
 */
function check_rest_api()
{
    if(!is_enabled_for_logged_in()) {
        return;
    }
    $pattern = '/wp\/v2\/users/i';
    $restRouteMatch = (isset($_REQUEST['rest_route']) && preg_match($pattern, $_REQUEST['rest_route']));
    $requestUriMatch = (isset($_SERVER['REQUEST_URI']) && preg_match($pattern, $_SERVER['REQUEST_URI']));
    if( $restRouteMatch || $requestUriMatch ) {
        if(get_option( 'disableRestUser' )) {
            display_404();
        }        
    }    
    return;
} 

/**
 * Checks if requested user should be blocked or not
 * @param int $option the WP option
 * @param WP_User $user The user object
 * @return boolean
 */
function isProtected($option, $user) {
    // if option is set to block only users without any posts
    if ( $option ==  AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS ) {
        if ( count_user_posts( $user->ID )  == 0  ) {
            return true;
        }
        // or if all users shall be blocked
    } else if ( $option ==  AuthorSettingsEnum::COMPLETE ){
        return true;
    }
    return false;
}

/**
 * Display the 404 page not found site
 */
function display_404() {
    global $wp_query;
    $template = null;

    status_header( 404 );
    if ( isset( $wp_query ) && is_object( $wp_query ) ) {
        $wp_query->set_404();
    }
    // display default 404 page
    $template = get_404_template();
    if ( $template && @file_exists( $template ) ) {
        include( $template );
        exit;
    }
    // fallback when no 404 page was found
    header( 'HTTP/1.0 404 Not Found', true, 404 );
    echo '<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>';
    exit;
}

/**
 * Overrides the default error message on login errors
 * @global WP_Error $errors
 * @param string $error
 * @return string
 */
function login_error_message($error){
    global $errors;
    $err_codes = $errors->get_error_codes();

    //check if protection is enabled
    if( !get_option( 'customLoginError') || !is_enabled_for_logged_in() ) {
        return $error;
    }

    // check if this is the error we are looking for
    if (    in_array( 'invalid_username', $err_codes ) || 
            in_array( 'invalid_email', $err_codes ) ||
            in_array( 'incorrect_password', $err_codes )) {
        //its the right error so we can overwrite it
        $error = sprintf( __('The entered username or password is not correct. <a href=%s>Lost your password</a>?', 'wp-author-security'), wp_lostpassword_url());
    }
    
    return $error;
}

/**
 * Always redirect the user to the confirm page when an invalid username or mail was entered during password reset process
 * @param WP_Error $errors
 * @return void
 */
function check_lost_password_error($errors) {

    //check if protection is enabled
    if( !get_option( 'customLoginError') || !is_enabled_for_logged_in() ) {
        return;
    }
    
    if( is_wp_error( $errors ) ) {
        if( $errors->get_error_code() === 'invalidcombo' || $errors->get_error_code() === 'invalid_email' ) {
            $redirect = 'wp-login.php?checkemail=confirm';
            wp_safe_redirect($redirect);
        }
    }
    return;
}

/**
 * Checks whether plugin is enabled for logged in users or not
 * @return boolean
 */
function is_enabled_for_logged_in() {
    // check if protection is disabled for logged in user
    if( is_user_logged_in() && get_option('disableLoggedIn')) {
        return false;
    }
    return true;
}

function wp_author_security_load_plugin_textdomain() {
    load_plugin_textdomain( 'wp-author-security', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

init();
