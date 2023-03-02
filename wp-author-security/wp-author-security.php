<?php
/**
 * Plugin Name: WP Author Security
 * Text Domain: wp-author-security
 * Domain Path: /languages
 * Description: Protect against user enumeration attacks on author pages and other places where valid user names can be obtained.
 * Author: mgmsp
 * Author URI: https://www.mgm-sp.com
 * Version: 1.5.0
 * License: GPLv3
 * Plugin URI: https://github.com/mgm-sp/wp-author-security
 */

use WP_Author_Security\WPASData;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once (dirname( __FILE__ ) . '/WPASData.php');
require_once (dirname( __FILE__ ) . '/options.php');

/**
 * initialize the plugin
 */
function wpas_init() {

    add_action( 'template_redirect', 'wpas_check_author_request', 1 );
    add_action( 'rest_api_init', 'wpas_check_rest_api', 10 );
    add_action( 'plugins_loaded', 'wpas_load_plugin_textdomain' );
    add_filter( 'login_errors', 'wpas_login_error_message', 1 );
    add_action( 'lost_password', 'wpas_check_lost_password_error' );
    add_filter( 'the_author', 'wpas_filter_feed', 1);
    add_filter( 'oembed_response_data', 'wpas_filter_oembed', 10, 4 );
    // since wp 5.5
    add_filter( 'wp_sitemaps_add_provider', 'wpas_filter_wp_sitemap_author', 10, 2 );

    register_activation_hook( __FILE__, ['WP_Author_Security\WPASData', 'createDB'] );
    register_uninstall_hook( __FILE__, ['WP_Author_Security\WPASData', 'uninstall'] );
    add_action( 'plugins_loaded', ['WP_Author_Security\WPASData', 'updateDbCheck'] );
}

/**
 * checks for author parameter in requests and decides whether to block request (404) 
 * or allow to display the requested author profile
 */
function wpas_check_author_request() {

    $field = '';
    $value = '';
    $author = get_query_var('author', false);           // when the username is passed, wp will return the existing user id here
    $authorName = get_query_var('author_name', false);

    // matches requests to "/author/<username>"
    if ( $authorName && get_option( 'protectAuthorName' ) != WPASAuthorSettingsEnum::DISABLED ) {
        $field = 'login';
        $value = trim($authorName);
        // matches requests to "?author=<id>"
    } else if ( $author && !$authorName && get_option( 'protectAuthor' ) != WPASAuthorSettingsEnum::DISABLED ) {
        $field = 'id';
        $value = intval($author);        
    } else {
        return;
    }
    
    if(!wpas_is_enabled_for_logged_in()) {
        return;
    }

    // load user and check if user exists
    $user = get_user_by( $field, $value );
    if( ! $user ) {
        return;
    }

    $disable = ( $field == 'id' ? wpas_isProtected( get_option( 'protectAuthor' ), $user ) : wpas_isProtected( get_option( 'protectAuthorName' ), $user ) );
    
    // when protection is enabled display 404
    if( $disable ) {
        wpas_incrementStatistics(WPASData::TYPE_AUTHOR_REQUEST);
        wpas_display_404();  
    }

    return;    
}

/**
 * disables user enumeration for the REST API endpoint wp-json/wp/v2/users
 */
function wpas_check_rest_api()
{
    if(!wpas_is_enabled_for_logged_in()) {
        return;
    }
    $pattern = '/wp\/v2\/users/i';
    $restRouteMatch = (isset($_REQUEST['rest_route']) && preg_match($pattern, $_REQUEST['rest_route']));
    $requestUriMatch = (isset($_SERVER['REQUEST_URI']) && preg_match($pattern, $_SERVER['REQUEST_URI']));
    if( $restRouteMatch || $requestUriMatch ) {
        if(get_option( 'disableRestUser' )) {
            wpas_incrementStatistics(WPASData::TYPE_REST_API_USER);
            wpas_display_404();
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
function wpas_isProtected($option, $user) {
    // if option is set to block only users without any posts
    if ( $option ==  WPASAuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS ) {
        if ( count_user_posts( $user->ID )  == 0  ) {
            return true;
        }
        // or if all users shall be blocked
    } else if ( $option ==  WPASAuthorSettingsEnum::COMPLETE ){
        return true;
    }
    return false;
}

/**
 * Display the 404 page not found site
 */
function wpas_display_404() {
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
function wpas_login_error_message($error){
    global $errors;

    //check if protection is enabled
    if( !get_option( 'customLoginError') || !wpas_is_enabled_for_logged_in() ) {
        return $error;
    }

	if(is_wp_error($errors)) {
		$err_codes = $errors->get_error_codes();
		// check if this is the error we are looking for
		if ( in_array( 'invalid_username', $err_codes ) ||
		     in_array( 'invalid_email', $err_codes ) ||
		     in_array( 'incorrect_password', $err_codes ) ) {
			//its the right error so we can overwrite it
			$error = sprintf( __( 'The entered username or password is not correct. <a href=%s>Lost your password</a>?', 'wp-author-security' ), wp_lostpassword_url() );
            wpas_incrementStatistics(WPASData::TYPE_LOGIN_PWRESET);
		}
	}
    
    return $error;
}

/**
 * Always redirect the user to the confirm page when an invalid username or mail was entered during password reset process
 * @param WP_Error $errors
 * @return void
 */
function wpas_check_lost_password_error($errors) {

    //check if protection is enabled
    if( !get_option( 'customLoginError') || !wpas_is_enabled_for_logged_in() ) {
        return;
    }
    
    if( is_wp_error( $errors ) ) {
        if( $errors->get_error_code() === 'invalidcombo' || $errors->get_error_code() === 'invalid_email' ) {
            wpas_incrementStatistics(WPASData::TYPE_LOGIN_PWRESET);
            $redirect = 'wp-login.php?checkemail=confirm';
            wp_safe_redirect($redirect);
            exit();
        }
    }
    return;
}

/**
 * Filter feeds and remove the author name
 * @param string $displayName The display name of the author
 * @return string
 */
function wpas_filter_feed($displayName) {
    
	//check if protection is enabled
    if( !get_option( 'wpas_filterFeed') || !wpas_is_enabled_for_logged_in() ) {
        return $displayName;
    }
    
    if ( is_feed() ) {
        wpas_incrementStatistics(WPASData::TYPE_FEED);
        return '';
	} 

    // leave other occurrences untouched
    return $displayName;
}
/**
 * Filter oembed and remove the author name and link
 * @param array $data
 * @param WP_Post $post
 * @param int $width
 * @param int $height
 * @return array
 */
function wpas_filter_oembed( $data, $post, $width, $height ) {

    //check if protection is enabled
    // note: user is always unauthenticated when this function is reached, therefore it can not be disabled for logged in users
    if( !get_option( 'wpas_filterEmbed') || !wpas_is_enabled_for_logged_in() ) {
        return $data;
    }

    unset($data['author_name']);
    unset($data['author_url']);
    wpas_incrementStatistics(WPASData::TYPE_OEMBED);
    
    return $data;
};

/**
 * Disables the author site map which is enabled since WP 5.5 by default
 * @param WP_Sitemaps_Provider $provide
 * @param string $name
 * @return WP_Sitemaps_Provider|bool
 */
function wpas_filter_wp_sitemap_author( $provider, $name ) {
    //check if protection is enabled
    if( !get_option( 'wpas_filterAuthorSitemap') || !wpas_is_enabled_for_logged_in() ) {
        return $provider;
    }

    if ( 'users' === $name ) {
        // currently disabled as this hook fires almost on every request
        // wpas_incrementStatistics(WPASData::TYPE_SITEMAP_AUTHOR);
        return false;
    }

    return $provider;
}

/**
 * Checks whether plugin is enabled for logged in users or not
 * @return boolean
 */
function wpas_is_enabled_for_logged_in() {
    // check if protection is disabled for logged in user
    if( is_user_logged_in() && get_option('disableLoggedIn')) {
        return false;
    }
    return true;
}

function wpas_load_plugin_textdomain() {
    load_plugin_textdomain( 'wp-author-security', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

function wpas_incrementStatistics($type) {
    $wpasMeta = new WPASData();
    $wpasMeta->cleanUp();
    $wpasMeta->addOrUpdate(WPASData::TYPE_GENERAL, WPASData::KEY_ALL_COUNT);

    $lastAction = $wpasMeta->getMeta(WPASData::TYPE_GENERAL, WPASData::KEY_LAST_ACTION);
    $time = date("Y-m-d H:i:s");
    $currentWeekDay = date("N");
    if(empty($lastAction)) {
        $wpasMeta->addMeta(WPASData::TYPE_GENERAL, WPASData::KEY_LAST_ACTION, $time);
        $lastAction = $time;
    } else {
        $wpasMeta->updateMeta(WPASData::TYPE_GENERAL, WPASData::KEY_LAST_ACTION, $time);
    }

    $oldDate = new \DateTime($lastAction);
    if($oldDate->format("N") !== $currentWeekDay) {
        // reset count because we have a new day
        $wpasMeta->addOrUpdate(WPASData::TYPE_GENERAL, 'weekday_' . $currentWeekDay, 1);
    } else {
        // otherwise increment count for day
        $wpasMeta->addOrUpdate(WPASData::TYPE_GENERAL, 'weekday_' . $currentWeekDay);
    }
}

wpas_init();
