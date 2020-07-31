<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AuthorSettingsEnum {
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
        'sanitize_callback' => 'sanitize_int',                                           
        'show_in_rest' => false                                                                                     
    );
    $argsAuthor = array(                                     
        'description' => 'Whether to protect the ?author=<id> endpoint.', 
        'default' => AuthorSettingsEnum::COMPLETE                                           
    );
    $argsAuthorName = array(                                     
        'description' => 'Whether to protect the /author/<name> endpoint.', 
        'default' => AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS                                                                                    
    );
    $argsLoggedIn = array(                                     
        'description' => 'Whether to enable protection for logged in user.',                                          
        'type' => 'booelan',                                      
        'sanitize_callback' => 'sanitize_checkbox',                                                                                    
        'default' => false                                             
    );
    $argsRestUser = array(                                     
        'description' => 'Whether to protect REST API endpoint wp-json/wp/v2/users.',                                          
        'type' => 'booelan',                                      
        'sanitize_callback' => 'sanitize_checkbox',                                                                                    
        'default' => true                                             
    );
    $argsLoginError = array(                                     
        'description' => 'Display a neutral message on login failures.',                                          
        'type' => 'booelan',                                      
        'sanitize_callback' => 'sanitize_checkbox',                                                                                    
        'default' => true                                             
    );

    register_setting( 'wp-author-security-group', 'protectAuthor', array_merge($argsBase, $argsAuthor) );
    register_setting( 'wp-author-security-group', 'protectAuthorName', array_merge($argsBase, $argsAuthorName) );
    register_setting( 'wp-author-security-group', 'disableLoggedIn', array_merge($argsBase, $argsLoggedIn) );
    register_setting( 'wp-author-security-group', 'disableRestUser', array_merge($argsBase, $argsRestUser) );
    register_setting( 'wp-author-security-group', 'customLoginError', array_merge($argsBase, $argsLoginError) );

    add_option( 'protectAuthor',  $argsAuthor['default']); 
    add_option( 'protectAuthorName',  $argsAuthorName['default']);
    add_option( 'disableLoggedIn',  $argsLoggedIn['default']);
    add_option( 'disableRestUser',  $argsRestUser['default']);
    add_option( 'customLoginError',  $argsLoginError['default']);
};

function wp_author_security_menu() {
    add_options_page( 
        'WP Author Security Settings', 
        'WP Author Security', 
        'manage_options', 
        'wp-author-security-options', 
        'wp_author_security_options_page' );
}

function sanitize_checkbox ( $input ) {
    if($input === 'on') {
        return true;
    }
    return false;
}

function sanitize_int ( $input ) {
    return intval( trim( $input ) );
}

/**
 * display Option's page
 */
function wp_author_security_options_page() {
?>

    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php echo __('WP Author Security Settings', 'wp-author-security'); ?></h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'wp-author-security-group' ); ?>
    <?php do_settings_sections( 'wp-author-security-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php echo __('Stop author ID user enumeration', 'wp-author-security'); ?></th>
        <td>
            <select name="protectAuthor">
                <option value="<?php echo AuthorSettingsEnum::COMPLETE; ?>"
                    <?php if ( get_option('protectAuthor') == AuthorSettingsEnum::COMPLETE )  echo ' selected="selected"'; ?>>
                    <?php echo __("don't show any users", 'wp-author-security'); ?>
                </option>
                <option value="<?php echo AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS; ?>"
                    <?php if ( get_option('protectAuthor') == AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS )  echo ' selected="selected"'; ?>>
                    <?php echo __('show only users with posts', 'wp-author-security'); ?>
                </option>            
                <option value="<?php echo AuthorSettingsEnum::DISABLED; ?>"
                    <?php if ( get_option('protectAuthor') == AuthorSettingsEnum::DISABLED )  echo ' selected="selected"'; ?>>
                    <?php echo __('deactivate protection', 'wp-author-security'); ?>
                </option>
            </select>
            <p><?php echo __('Disable the /?author=&lt;id&gt; endpoint.', 'wp-author-security'); ?></p>
        </td>
        </tr>
         
        <tr valign="top">
        <th scope="row"><?php echo __('Stop author NAME user enumeration', 'wp-author-security'); ?></th>
        <td>
            <select name="protectAuthorName">
                <option value="<?php echo AuthorSettingsEnum::COMPLETE; ?>"
                    <?php if ( get_option('protectAuthorName') == AuthorSettingsEnum::COMPLETE )  echo ' selected="selected"'; ?>>
                    <?php echo __("don't show any users", 'wp-author-security'); ?>
                </option>
                <option value="<?php echo AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS; ?>"
                    <?php if ( get_option('protectAuthorName') == AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS )  echo ' selected="selected"'; ?>>
                    <?php echo __('show only users with posts', 'wp-author-security'); ?>
                </option>
                <option value="<?php echo AuthorSettingsEnum::DISABLED; ?>"
                    <?php if ( get_option('protectAuthorName') == AuthorSettingsEnum::DISABLED )  echo ' selected="selected"'; ?>>
                    <?php echo __('deactivate protection', 'wp-author-security'); ?>
                </option>
            </select>
            <p><?php echo __('Disable the /author/&lt;name&gt; and /?author_name=&lt;name&gt; endpoints.', 'wp-author-security'); ?></p>
        </td>
        </tr>
        
        <tr valign="top">
        <th scope="row"><?php echo __('Disable for logged in users', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="disableLoggedIn"<?php if ( get_option('disableLoggedIn') )  echo ' checked="checked"'; ?> />
            <p><?php echo __('Disable protection for logged in users.', 'wp-author-security'); ?></p>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php echo __('Protect REST API user enumeration', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="disableRestUser"<?php if ( get_option('disableRestUser') )  echo ' checked="checked"'; ?> />
            <p><?php echo __('Disable REST API endpoint wp-json/wp/v2/users.', 'wp-author-security'); ?></p>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php echo __('Stop user enumeration on login/reset password form', 'wp-author-security'); ?></th>
        <td>
            <input type="checkbox" name="customLoginError"<?php if ( get_option('customLoginError') )  echo ' checked="checked"'; ?> />
            <p><?php echo __('Displays a neutral message when either the username or password is incorrect.', 'wp-author-security'); ?></p>
        </td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php
}
?>
