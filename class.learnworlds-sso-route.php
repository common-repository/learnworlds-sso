<?php

class Learnworlds_SSO_Route {

    const ROUTE_NAMESPACE = 'lw/v1';
    const ROUTE_URL = '/sso';
    const GRAVATAR_SERVER = 'https://www.gravatar.com/avatar/';

    // REST API global route (permalink non-specific) -> http://domain/?rest_route=/namespace/route

    public static $current_user = null;
    public static $client_id = null;
    public static $client_secret = null;
    public static $api_url = null;

    public function __construct() {
        add_action('init', function() {
            if (is_user_logged_in()) {
                self::$current_user = wp_get_current_user();
            }

            // override WooCommerce account pages in favor of vanilla WordPress
            if (self::use_woocommerce_account_urls() && isset($_GET['redirect_to'])) {
                add_filter('woocommerce_login_redirect', function ($redirect, $user) {
                    return $_GET['redirect_to'];
                }, 10, 2);

                add_filter('woocommerce_get_endpoint_url', function ($url, $endpoint, $value, $permalink) {
                    return add_query_arg(array('redirect_to' => urlencode($_GET['redirect_to'])), $url);
                }, 10, 4);
            }
            if (self::use_woocommerce_account_urls()){
                add_filter('woocommerce_registration_redirect', function ($var) {
                    $startUrlQuery = strpos($var,'?');
                    if($startUrlQuery === false){
                        return $var;
                    }
                    $startUrlQuery +=1; //Remove the ?                     
                    $urlQuery = substr($var,$startUrlQuery,strlen($var) - $startUrlQuery);
                    $fragments=[];
                    parse_str($urlQuery,$fragments);
                    if(isset($fragments['redirect_to']) && !empty($fragments['redirect_to']) ){
                        return $fragments['redirect_to'];
                    }
                    
                    return $var;
                }, 20, 1);    
            }
            if (class_exists('WooCommerce') && get_option('learnworlds_sso_use_woocommerce_account_urls') === '0') {
                remove_filter('lostpassword_url', 'wc_lostpassword_url', 10);
            }
        });
        
        // add action to REST API
        add_action('rest_api_init', function () {
            register_rest_route(self::ROUTE_NAMESPACE, self::ROUTE_URL, array(
                'methods'  => 'GET',
                'callback' => array($this, 'route_callback'),
            ));
    
            add_option('learnworlds_sso_access_token', '');
            add_option('learnworlds_sso_access_token_expires_on', '');
        });

        // add update user data on Learnworlds server
        add_action('profile_update', function ($user_id, $old_user_data) {
            $new_user_data = get_userdata($old_user_data->ID);
            $user_learnworlds_id = get_user_meta($old_user_data->ID, 'learnworlds_user_id', true);
            if ($new_user_data->user_email === $old_user_data->user_email) {
                return;
            }
            if (!$user_learnworlds_id) {
                return;
            }
            $access_token = $this->get_access_token();
            if (!$access_token) {
                return;
            }

            $this->update_user_mail($user_learnworlds_id, $new_user_data->user_email, $access_token);
        }, 10, 2);

        // add redirect_to params to password reset link
        add_action('woocommerce_customer_reset_password', function ($user) {
            if (self::use_woocommerce_account_urls() && isset($_GET['redirect_to'])) {
                wp_redirect(add_query_arg(
                    array('password-reset' => 'true',
                    'redirect_to' => $_GET['redirect_to']),
                    wc_get_page_permalink('myaccount')
                ));
                exit;
            }
        });
        
        self::$client_id = get_option('learnworlds_sso_client_id');
        self::$client_secret = get_option('learnworlds_sso_client_secret');
        self::$api_url = get_option('learnworlds_sso_api_server_url');
    }

    // whether or not to override WooCommerce pages
    public static function use_woocommerce_account_urls() {
        return class_exists('WooCommerce') && get_option('learnworlds_sso_use_woocommerce_account_urls') === '1';
    }

    public function get_gravatar_url($email) {
        return self::GRAVATAR_SERVER . md5(strtolower(trim($email))); // based on gravatar documentation
    }

    // callback of plugin route in the REST API
    // redirects to login/registration/password reset or to Learnworlds school page
    public function route_callback(WP_REST_Request $wp_rest_request) {
        $action = $wp_rest_request->get_param('action');
        $redirect_url = $wp_rest_request->get_param('redirectUrl');
        if (!$action) {
            $action = 'login';
        }
        // increase cURL timeout
        add_filter('http_request_args', function ($r) {
            $r['timeout'] = 15;
            return $r;
        }, 100, 1);
        add_action('http_api_curl', function ($handle) {
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($handle, CURLOPT_TIMEOUT, 15);
        }, 100, 1);

        if (!Learnworlds_SSO_Settings::are_valid()) {
            $this->redirect_after_error($redirect_url);
        }

        if (self::$current_user) {
            $this->sign_in_user_to_learnworlds($redirect_url);
        }

        $action_redirect_url = add_query_arg(array('redirectUrl' => urlencode($redirect_url)), rest_url(self::ROUTE_NAMESPACE . self::ROUTE_URL));;
        if ($action === 'login') {
            if (self::use_woocommerce_account_urls()) {
                $url = add_query_arg(array('redirect_to' => urlencode($action_redirect_url)), wc_get_page_permalink('myaccount'));
            } else {
                $url = wp_login_url($action_redirect_url);
            }
        } else if ($action === 'signup') {
            if (self::use_woocommerce_account_urls()) {
                $url = wc_get_page_permalink('myaccount');
            } else {
                $url = wp_registration_url();
            }
        } else if ($action === 'passwordreset') {
            if (self::use_woocommerce_account_urls()) {
                $url = add_query_arg(array('redirect_to' => urlencode($action_redirect_url)), wc_lostpassword_url());
            } else {
                $url = wp_lostpassword_url($action_redirect_url);
            }
        } else {
            if (self::use_woocommerce_account_urls()) {
                $url = add_query_arg(array('redirect_to' => urlencode($action_redirect_url)), wc_get_page_permalink('myaccount'));
             } else {
                $url = wp_login_url($action_redirect_url);
             }
        }
        
        wp_redirect($url);
        exit;
    }

    // signs a WordPress user into the Learnworlds school via SSO; if user does
    // not exist, registers one redirects to URL after login/registration
    public function sign_in_user_to_learnworlds($redirect_url) {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            $this->redirect_after_error($redirect_url);
        }
      
        $user_learnworlds_id = get_user_meta(self::$current_user->ID, 'learnworlds_user_id', true);
        if ($user_learnworlds_id) {
            $user_profile = $this->get_user_profile($user_learnworlds_id, $access_token);
        } else {
            $user_profile = $this->get_user_profile(self::$current_user->user_email, $access_token);
        }
        if ($user_profile) { // user profile found on LW server
            if (!isset($user_profile['id'])) {
                $this->redirect_after_error($redirect_url);
            }
            if (!$user_learnworlds_id) {
                add_user_meta(self::$current_user->ID, 'learnworlds_user_id', $user_profile['id'], true);
            }
            $sso_url = $this->get_sso_url($user_profile['id'], $redirect_url, $access_token);
        } else { // user profile not found on LW server
            if ($user_learnworlds_id) {
                $this->redirect_after_error($redirect_url);
            }
            $sso_url = $this->register_and_get_sso_url($redirect_url, $access_token);
            $user_profile = $this->get_user_profile(self::$current_user->user_email, $access_token);
            if (!$user_profile || !isset($user_profile['id'])) {
                $this->redirect_after_error($redirect_url);
            }
            add_user_meta(self::$current_user->ID, 'learnworlds_user_id', $user_profile['id'], true);
        }

        if (!$sso_url || !$user_profile) {
            $this->redirect_after_error($redirect_url);
        }

        wp_redirect($sso_url);
        exit;
    }

    
    public function get_access_token() {
        $access_token = get_option('learnworlds_sso_access_token');
        $expires_on = get_option('learnworlds_sso_access_token_expires_on');

        if ($access_token && $expires_on && $expires_on - time() > 3600) {
            return $access_token;
        }

        $access_token = $this->request_access_token();

        return $access_token;
    }

    public function request_access_token() {
        $http_client_request_body = $this->make_http_request(
            self::$api_url . '/oauth2/access_token',
            'POST',
            array('Lw-Client' => self::$client_id),
            array('data' => '{"client_id":"' . self::$client_id . '","client_secret":"' . self::$client_secret . '","grant_type":"client_credentials"}'),
            false // never retry this request automatically
        );
        if (!$http_client_request_body) {
            return null;
        }
        $access_token = isset($http_client_request_body['tokenData']['access_token']) ? $http_client_request_body['tokenData']['access_token'] : null;
        $expires_in = isset($http_client_request_body['tokenData']['expires_in']) ? $http_client_request_body['tokenData']['expires_in'] : null;

        if (!$access_token || !$expires_in) { // just in case $access_token has expired but server/client clocks are out of sync
           return null;
        }

        update_option('learnworlds_sso_access_token', $access_token);
        update_option('learnworlds_sso_access_token_expires_on', time() + $expires_in);
        
        return $access_token;
    }

    public function get_user_profile($user_id_or_email, $access_token) {
        $user_id_or_email = urlencode($user_id_or_email);       
        $http_client_request_body = $this->make_http_request(
            self::$api_url . '/user/' . $user_id_or_email . '/profile',
            'GET',
            array(
                'Lw-Client' => self::$client_id,
                'Authorization' => 'Bearer ' . $access_token,
            )
        );
        if (!$http_client_request_body) {
            return null;
        }
        $user_profile = isset($http_client_request_body['user']) ? $http_client_request_body['user'] : null;
        
        return $user_profile;
    }

    public function register_and_get_sso_url($redirect_url, $access_token) {
        $http_client_request_body = $this->make_http_request(
            self::$api_url . '/sso',
            'POST',
            array(
                'Lw-Client' => self::$client_id,
                'Authorization' => 'Bearer ' . $access_token,
            ),
            array('data' => '{"email":"' . self::$current_user->user_email . '","username":"' . urlencode(self::$current_user->display_name) . '","avatar":"' . $this->get_gravatar_url(self::$current_user->user_email) . '","redirectUrl":"' . $redirect_url . '"}')
        );
        if (!$http_client_request_body) {
            return null;
        }
        $sso_url = isset($http_client_request_body['url']) ? $http_client_request_body['url'] : null;
        
        return $sso_url;
    }

    public function get_sso_url($user_id, $redirect_url, $access_token) {
        $http_client_request_body = $this->make_http_request(
            self::$api_url . '/sso',
            'POST',
            array(
                'Lw-Client' => self::$client_id,
                'Authorization' => 'Bearer ' . $access_token,
            ),
            array('data' => '{"user_id":"' . $user_id . '","redirectUrl":"' . $redirect_url . '"}')
        );
        if (!$http_client_request_body) {
            return null;
        }
        $sso_url = isset($http_client_request_body['url']) ? $http_client_request_body['url'] : null;
        
        return $sso_url;
    }

    public function update_user_mail($user_learnworlds_id, $new_email, $access_token) {
        $http_client_request_body = $this->make_http_request(
            self::$api_url . '/user/' . $user_learnworlds_id,
            'PUT',
            array(
                'Lw-Client' => self::$client_id,
                'Authorization' => 'Bearer ' . $access_token,
            ),
            array('data' => '{"email":"' . $new_email . '","avatar":"' . $this->get_gravatar_url($new_email) . '"}')
        );
        if (!$http_client_request_body) {
            return false;
        }

        return true;
    }

    public function make_http_request($url, $method = 'GET', $headers = array(), $body = array(), $retry = true) {
        // $retry means you have to issue another access token automatically
        $args = array(
            'method' => $method,
        );
        if ($headers) {
            $args['headers'] = $headers;
        }
        if ($body) {
            $args['body'] = $body;
        }
        $http_client = new WP_Http();
        $http_client_request = $http_client->request($url, $args);
        if ($http_client_request->errors) {
            return null;
        }
        $http_client_response_code = isset($http_client_request['response']['code']) ? $http_client_request['response']['code'] : null;
        if ($http_client_response_code === 400 && $retry) {
            // retry request with a fresh access token
            $access_token = $this->request_access_token();
            if (!$access_token) {
                return null;
            }
            $args['headers']['Authorization'] = 'Bearer ' . $access_token;
            $http_client = new WP_Http();
            $http_client_request = $http_client->request($url, $args);
            if ($http_client_request->errors) {
                return null;
            }
            $http_client_response_code = isset($http_client_request['response']['code']) ? $http_client_request['response']['code'] : null;
        }
        if ($http_client_response_code !== 200) {
            return null;
        }
        $http_client_request_body = isset($http_client_request['body']) ? $http_client_request['body'] : null;
        if (!$http_client_request_body) {
            return null;
        }
        $http_client_request_body_parsed = json_decode($http_client_request_body, true);
        if (!$http_client_request_body_parsed) {
            return null;
        }
        $success = isset($http_client_request_body_parsed['success']) ? $http_client_request_body_parsed['success'] : false;
        if (!$success) {
            return null;
        }

        return $http_client_request_body_parsed;
    }
 
    // fallback redirect method for any errors that might occur
    public function redirect_after_error($url = null) {
        if ($url) {
            wp_redirect($url);
        } else {
            wp_redirect(home_url());
        }
        exit;
    }

}
