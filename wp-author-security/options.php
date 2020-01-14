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

    register_setting( 'wp-author-security-group', 'protectAuthor', array_merge($argsBase, $argsAuthor) );
    register_setting( 'wp-author-security-group', 'protectAuthorName', array_merge($argsBase, $argsAuthorName) );
    register_setting( 'wp-author-security-group', 'disableLoggedIn', array_merge($argsBase, $argsLoggedIn) );
    register_setting( 'wp-author-security-group', 'disableRestUser', array_merge($argsBase, $argsRestUser) );

    add_option( 'protectAuthor',  $argsAuthor['default']); 
    add_option( 'protectAuthorName',  $argsAuthorName['default']);
    add_option( 'disableLoggedIn',  $argsLoggedIn['default']);
    add_option( 'disableRestUser',  $argsLoggedIn['default']);
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
        <h2>WP Author Security Settings</h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'wp-author-security-group' ); ?>
    <?php do_settings_sections( 'wp-author-security-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Stop author ID user enumeration</th>
        <td>
            <select name="protectAuthor">
                <option value="<?php echo AuthorSettingsEnum::COMPLETE; ?>"
                    <?php if ( get_option('protectAuthor') == AuthorSettingsEnum::COMPLETE )  echo ' selected="selected"'; ?>>
                    for all users
                </option>
                <option value="<?php echo AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS; ?>"
                    <?php if ( get_option('protectAuthor') == AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS )  echo ' selected="selected"'; ?>>
                    for users without posts
                </option>            
                <option value="<?php echo AuthorSettingsEnum::DISABLED; ?>"
                    <?php if ( get_option('protectAuthor') == AuthorSettingsEnum::DISABLED )  echo ' selected="selected"'; ?>>
                    deactivate protection
                </option>
            </select>
            <p>Disable the /?author=&lt;id&gt; endpoint.</p>
        </td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Stop author NAME user enumeration</th>
        <td>
            <select name="protectAuthorName">
                <option value="<?php echo AuthorSettingsEnum::COMPLETE; ?>"
                    <?php if ( get_option('protectAuthorName') == AuthorSettingsEnum::COMPLETE )  echo ' selected="selected"'; ?>>
                    for all users
                </option>
                <option value="<?php echo AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS; ?>"
                    <?php if ( get_option('protectAuthorName') == AuthorSettingsEnum::ONLY_FOR_USERS_WITHOUT_POSTS )  echo ' selected="selected"'; ?>>
                    for users without posts
                </option>
                <option value="<?php echo AuthorSettingsEnum::DISABLED; ?>"
                    <?php if ( get_option('protectAuthorName') == AuthorSettingsEnum::DISABLED )  echo ' selected="selected"'; ?>>
                    deactivate protection
                </option>
            </select>
            <p>Disable the /author/&lt;name&gt; and /?author_name=&lt;name&gt; endpoints.</p>
        </td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Disable for logged in users</th>
        <td>
            <input type="checkbox" name="disableLoggedIn"<?php if ( get_option('disableLoggedIn') )  echo ' checked="checked"'; ?> />
            <p>Disable protection for logged in users.</p>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Protect REST API user enumeration</th>
        <td>
            <input type="checkbox" name="disableRestUser"<?php if ( get_option('disableRestUser') )  echo ' checked="checked"'; ?> />
            <p>Disable REST API endpoint wp-json/wp/v2/users.</p>
        </td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php
}
?>
