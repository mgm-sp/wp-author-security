<?php

use WP_Author_Security\WPASData;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WPASAuthorSettingsEnum {
    const DISABLED = 0;
    const COMPLETE = 1;
    const ONLY_FOR_USERS_WITHOUT_POSTS = 2;
}

if( is_admin() ) {
    /* Add Custom Admin Menu */
    add_action( 'admin_menu', 'wp_author_security_menu' );
    add_action( 'admin_init', 'register_wp_author_security_settings');
}

function register_wp_author_security_settings() {
    $argsBase = array(
        'type' => 'integer',                                      
        'sanitize_callback' => 'wpas_sanitize_int',                                           
        'show_in_rest' => false                                                                                     
    );
    $argsAuthor = array(                                     
        'description' => 'Whether to protect the ?author=<id> endpoint.', 
        'default' => WPASAuthorSettingsEnum::COMPLETE                                           
    );
    $argsAuthorName = array(                                     
        'description' => 'Whether to protect the /author/<name> endpoint.', 
        'default' => WPASAuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS                                                                                    
    );
    $argsLoggedIn = array(                                     
        'description' => 'Whether to enable protection for logged in user.',                                          
        'type' => 'booelan',                                      
        'sanitize_callback' => 'wpas_sanitize_checkbox',                                                                                    
        'default' => false                                             
    );
    $argsRestUser = array(                                     
        'description' => 'Whether to protect REST API endpoint wp-json/wp/v2/users.',                                          
        'type' => 'booelan',                                      
        'sanitize_callback' => 'wpas_sanitize_checkbox',                                                                                    
        'default' => true                                             
    );
    $argsLoginError = array(                                     
        'description' => 'Display a neutral message on login failures.',                                          
        'type' => 'booelan',                                      
        'sanitize_callback' => 'wpas_sanitize_checkbox',                                                                                    
        'default' => true                                             
    );
    $argsFilterFeed = array(                                     
        'description' => 'Remove the author name in feeds',                                          
        'type' => 'booelan',                                      
        'sanitize_callback' => 'wpas_sanitize_checkbox',                                                                                    
        'default' => true                                             
    );
    $argsFilterEmbed = array(                                     
        'description' => 'Remove the author name in embeds',
        'type' => 'booelan',                                                                             
        'sanitize_callback' => 'wpas_sanitize_checkbox',                                                                                    
        'default' => true                                             
    );
    $argsFilterAuthorSitemap = array(                                     
        'description' => 'Remove the author sitemap',
        'type' => 'booelan',                                                                             
        'sanitize_callback' => 'wpas_sanitize_checkbox',                                                                                    
        'default' => true                                             
    );
    
    add_option( 'protectAuthor',  $argsAuthor['default']); 
    add_option( 'protectAuthorName',  $argsAuthorName['default']);
    add_option( 'disableLoggedIn',  $argsLoggedIn['default']);
    add_option( 'disableRestUser',  $argsRestUser['default']);
    add_option( 'customLoginError',  $argsLoginError['default']);
    add_option( 'wpas_filterFeed',  $argsFilterFeed['default']);
    add_option( 'wpas_filterEmbed',  $argsFilterEmbed['default']);
    add_option( 'wpas_filterAuthorSitemap',  $argsFilterAuthorSitemap['default']);

    register_setting( 'wp-author-security-group', 'protectAuthor', array_merge($argsBase, $argsAuthor) );
    register_setting( 'wp-author-security-group', 'protectAuthorName', array_merge($argsBase, $argsAuthorName) );
    register_setting( 'wp-author-security-group', 'disableLoggedIn', array_merge($argsBase, $argsLoggedIn) );
    register_setting( 'wp-author-security-group', 'disableRestUser', array_merge($argsBase, $argsRestUser) );
    register_setting( 'wp-author-security-group', 'customLoginError', array_merge($argsBase, $argsLoginError) );
    register_setting( 'wp-author-security-group', 'wpas_filterFeed', array_merge($argsBase, $argsFilterFeed) );
    register_setting( 'wp-author-security-group', 'wpas_filterEmbed', array_merge($argsBase, $argsFilterEmbed) );
    register_setting( 'wp-author-security-group', 'wpas_filterAuthorSitemap', array_merge($argsBase, $argsFilterAuthorSitemap) );
};

function wp_author_security_menu() {
    add_options_page( 
        'WP Author Security Settings', 
        'WP Author Security', 
        'manage_options', 
        'wp-author-security-options', 
        'wp_author_security_options_page' );
}

function wpas_sanitize_checkbox ( $input ) {
    if($input === 'on') {
        return true;
    }
    return false;
}

function wpas_sanitize_int ( $input ) {
    return intval( trim( $input ) );
}

/**
 * display Option's page
 */
function wp_author_security_options_page() {
    $wpasMeta = new WPASData();
?>

<div class="wrap">
    <h2><?php echo __('WP Author Security Settings', 'wp-author-security'); ?></h2>
    <p style="font-style: italic;">
        <?php echo sprintf( __( 'Blocked %d malicious requests since activation and %d requests in the past 7 days.', 'wp-author-security' ),
            $wpasMeta->getCountAll(),
            $wpasMeta->getCountLastDays()
        );
        ?>
    </p>
    <form method="post" action="options.php">
    <?php settings_fields( 'wp-author-security-group' ); ?>
    <?php do_settings_sections( 'wp-author-security-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php echo __('Stop author ID user enumeration', 'wp-author-security'); ?></th>
        <td>
            <select name="protectAuthor">
                <option value="<?php echo WPASAuthorSettingsEnum::COMPLETE; ?>"
                    <?php if ( get_option('protectAuthor') == WPASAuthorSettingsEnum::COMPLETE )  echo ' selected="selected"'; ?>>
                    <?php echo __("don't show any users", 'wp-author-security'); ?>
                </option>
                <option value="<?php echo WPASAuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS; ?>"
                    <?php if ( get_option('protectAuthor') == WPASAuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS )  echo ' selected="selected"'; ?>>
                    <?php echo __('show only users with posts', 'wp-author-security'); ?>
                </option>            
                <option value="<?php echo WPASAuthorSettingsEnum::DISABLED; ?>"
                    <?php if ( get_option('protectAuthor') == WPASAuthorSettingsEnum::DISABLED )  echo ' selected="selected"'; ?>>
                    <?php echo __('deactivate protection', 'wp-author-security'); ?>
                </option>
            </select>
            <p class="description"><?php echo __('Disable the /?author=&lt;id&gt; endpoint.', 'wp-author-security'); ?></p>
        </td>
        </tr>
         
        <tr valign="top">
        <th scope="row"><?php echo __('Stop author NAME user enumeration', 'wp-author-security'); ?></th>
        <td>
            <select name="protectAuthorName">
                <option value="<?php echo WPASAuthorSettingsEnum::COMPLETE; ?>"
                    <?php if ( get_option('protectAuthorName') == WPASAuthorSettingsEnum::COMPLETE )  echo ' selected="selected"'; ?>>
                    <?php echo __("don't show any users", 'wp-author-security'); ?>
                </option>
                <option value="<?php echo WPASAuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS; ?>"
                    <?php if ( get_option('protectAuthorName') == WPASAuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS )  echo ' selected="selected"'; ?>>
                    <?php echo __('show only users with posts', 'wp-author-security'); ?>
                </option>
                <option value="<?php echo WPASAuthorSettingsEnum::DISABLED; ?>"
                    <?php if ( get_option('protectAuthorName') == WPASAuthorSettingsEnum::DISABLED )  echo ' selected="selected"'; ?>>
                    <?php echo __('deactivate protection', 'wp-author-security'); ?>
                </option>
            </select>
            <p class="description"><?php echo __('Disable the /author/&lt;name&gt; and /?author_name=&lt;name&gt; endpoints.', 'wp-author-security'); ?></p>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php echo __('Protect REST API user enumeration', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="disableRestUser"<?php if ( get_option('disableRestUser') )  echo ' checked="checked"'; ?> />
            <p class="description"><?php echo __('Disable REST API endpoint wp-json/wp/v2/users.', 'wp-author-security'); ?></p>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php echo __('Stop user enumeration on login/reset password form', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="customLoginError"<?php if ( get_option('customLoginError') )  echo ' checked="checked"'; ?> />
            <p class="description"><?php echo __('Displays a neutral message when either the username or password is incorrect.', 'wp-author-security'); ?></p>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php echo __('Remove author name in feeds', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="wpas_filterFeed"<?php if ( get_option('wpas_filterFeed') )  echo ' checked="checked"'; ?> />
            <p class="description"><?php echo __('Setting this option will remove the author name in the /feed endpoint.', 'wp-author-security'); ?></p>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php echo __('Remove author name in embeds', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="wpas_filterEmbed"<?php if ( get_option('wpas_filterEmbed') )  echo ' checked="checked"'; ?> />
            <p class="description"><?php echo __('Setting this option will remove the author name and link in the oEmbed API endpoint e.g.: /wp-json/oembed/1.0/embed?url=https://&lt;yourdomain&gt;.', 'wp-author-security'); ?></p>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php echo __('Disable the sitemap for authors', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="wpas_filterAuthorSitemap"<?php if ( get_option('wpas_filterAuthorSitemap') )  echo ' checked="checked"'; ?> />
            <p class="description"><?php echo __('Since Wordpress 5.5 a default sitemap is via URL reachable (/wp-sitemap.xml). Enabling this option will remove information of authors from the sitemap.', 'wp-author-security'); ?></p>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php echo __('Disable for logged in users', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="disableLoggedIn"<?php if ( get_option('disableLoggedIn') )  echo ' checked="checked"'; ?> />
            <p class="description"><?php echo __('Disable protection for logged in users.', 'wp-author-security'); ?></p>
        </td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

    </form>
</div>
<?php
}
?>
