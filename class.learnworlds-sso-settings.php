<?php

class Learnworlds_SSO_Settings {

    const OPTION_GROUP = 'learnworlds-sso-group';
    const SETTINGS_PAGE_SLUG = 'options-learnworlds-sso';

    public function __construct() {
        // add options to DB and init settings
        add_action('admin_init', function () {
            add_option('learnworlds_sso_client_id', '');
            add_option('learnworlds_sso_client_secret', '');
            add_option('learnworlds_sso_api_server_url', '');
            register_setting(self::OPTION_GROUP, 'learnworlds_sso_client_id');
            register_setting(self::OPTION_GROUP, 'learnworlds_sso_client_secret');
            register_setting(self::OPTION_GROUP, 'learnworlds_sso_api_server_url');
            if (class_exists('WooCommerce')) {
                add_option('learnworlds_sso_use_woocommerce_account_urls', '0');
                register_setting(self::OPTION_GROUP, 'learnworlds_sso_use_woocommerce_account_urls');
            }
        });

        // add plugin page link in settings
        add_action('admin_menu', function () {
            add_options_page('Learnworlds SSO', 'Learnworlds SSO', 'activate_plugins', self::SETTINGS_PAGE_SLUG, array($this, 'settings_page_view'));
        });

        // add "Settings" link in plugins list
        add_action('plugin_action_links_learnworlds-sso/learnworlds-sso.php', function ($links) {
            $settings_link = '<a href="options-general.php?page=' . self::SETTINGS_PAGE_SLUG . '">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        });

        // show Learnworlds user ID in profile page (only if current user is admin)
        add_action('show_user_profile', array($this, 'has_learnworlds_account'));
        add_action('edit_user_profile', array($this, 'has_learnworlds_account'));

        //Reset LW ID
        add_action('edit_user_profile_update', array($this, 'reset_lw_id'));
        
    }

    // check if plugin settings have values
    public static function are_valid() {
        $client_id = get_option('learnworlds_sso_client_id');
        $client_secret = get_option('learnworlds_sso_client_secret');
        $api_url = get_option('learnworlds_sso_api_server_url');

        if (!$client_id || !$client_secret || !$api_url) {
            return false;
        }
        return true;
    }

    function reset_lw_id( $user_id )
    {
        // check that the current user have the capability to edit the $user_id
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }
        if (!current_user_can('activate_plugins')){
            return;
        }
        
        if(!isset($_POST['reset_lw_id']) || $_POST['reset_lw_id'] !== 'on' ){
            return;
        }
       
        // create/update user meta for the $user_id
        return delete_user_meta(
            $user_id,
            'learnworlds_user_id'            
        );
    }

   

    public function has_learnworlds_account(WP_User $user) {
        if (!current_user_can('activate_plugins')){
            return;
        }
        ?>
        <h3>Learnworlds Info</h3>
        <table class="form-table">
        <tr>
            <th>User ID</th>
            <td>
                <?= ($learnworlds_user_id = get_user_meta($user->ID, 'learnworlds_user_id', true)) ? $learnworlds_user_id : '-' ?>
            </td>
            
        </tr>
        <tr>
        <th>Reset User ID <br />
            <i class="description" style="font-weight: normal">
                    Caution: This should only be used in the extreme case that a user can not successfuly login.
            </i>
        </th>
        <td>
                <label>
                    <input type="checkbox"
                       class="regular-text ltr"
                       id="reset_lw_id"
                       name="reset_lw_id"
                       >
                        
                </label>           
            </td>
        </tr>
        </table>
        <?php
    }
 
    // plugin settings page content
    public function settings_page_view() { ?>
        <div class="wrap">
            <h1>Learnworlds SSO</h1>
            <form method="post" action="options.php">
                <?php settings_fields(self::OPTION_GROUP); ?>
                <?php do_settings_sections(self::OPTION_GROUP); ?>
                <h2>Required Settings</h2>
                <p>Please fill in the form below with your LearnWorlds API keys, which you can find by navigating to <strong>Settings &rarr; Developers &rarr; API</strong> in your school. Then navigate to <strong>Site Builder &rarr; Sign in/up &rarr; Custom SSO URL</strong> and paste this URL:<br/>
                <code><?php echo  rest_url(Learnworlds_SSO_Route::ROUTE_NAMESPACE . Learnworlds_SSO_Route::ROUTE_URL) ?></code></p>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="learnworlds_sso_client_id">Client ID</label>
                        </th>
                        <td>
                            <input id="learnworlds_sso_client_id" class="regular-text code" type="text" name="learnworlds_sso_client_id" value="<?= esc_attr(get_option('learnworlds_sso_client_id')) ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="learnworlds_sso_client_secret">Client Secret</label>
                        </th>
                        <td>
                            <input id="learnworlds_sso_client_secret" class="regular-text code" type="text" name="learnworlds_sso_client_secret" value="<?= esc_attr(get_option('learnworlds_sso_client_secret')) ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                        <label for="learnworlds_sso_api_server_url">API Server URL</label>
                        </th>
                        <td>
                            <input id="learnworlds_sso_api_server_url" class="regular-text code" type="text" name="learnworlds_sso_api_server_url" value="<?= esc_attr(get_option('learnworlds_sso_api_server_url')) ?>" />
                        </td>
                    </tr>
                </table>
                <?php if (function_exists('wc')) { ?>
                <h2>Optional Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                        <label for="learnworlds_sso_use_woocommerce_account_urls">Use WooCommerce Account URLs</label>
                        </th>
                        <td>
                            <input type="hidden" value="0" name="learnworlds_sso_use_woocommerce_account_urls">
                            <input id="learnworlds_sso_use_woocommerce_account_urls" class="regular-text code" type="checkbox" name="learnworlds_sso_use_woocommerce_account_urls" value="1" <?= checked(get_option('learnworlds_sso_use_woocommerce_account_urls'), '1', true) ?> />
                        </td>
                    </tr>
                </table>
                <?php } ?>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php }

}
