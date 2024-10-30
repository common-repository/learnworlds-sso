<?php

class Learnworlds_SSO_Shortcode {

    const DEFAULT_LINK_TEXT = 'Login to my school';
    const DEFAULT_LOGGED_IN_LINK_TEXT = 'Navigate to my school';

    public function __construct() {
        add_shortcode('learnworlds-sso-link', array($this, 'render_sso_link'));
    }

    // add a schortcode with a link to redirect to Learnworlds school page
    public static function render_sso_link($atts) {
        $atts = shortcode_atts(array(
            'url' => '',
            'text' => self::DEFAULT_LINK_TEXT,
            'logged-in-text' => self::DEFAULT_LOGGED_IN_LINK_TEXT,
        ), $atts, 'learnworlds-sso-link');

        $url = $atts['url'];
        $text = $atts['text'];

        if (!$text) {
            $text = self::DEFAULT_LINK_TEXT;
        }

        if(is_user_logged_in()){
            $text = ($atts['logged-in-text'])? $atts['logged-in-text'] : $text;
        }

        $serverUrl = rest_url(Learnworlds_SSO_Route::ROUTE_NAMESPACE . Learnworlds_SSO_Route::ROUTE_URL);
        if ($url) {            
            $url = add_query_arg(['redirectUrl' => urlencode($url)], $serverUrl);
        }
        else {
            $url = $serverUrl;
        }

        if (!Learnworlds_SSO_Settings::are_valid()) {
            return '<a href="javascript:void(0)">This plugin has no been setup yet.</a>';
        }
        
        return "<a class='lw-sso-link' href='{$url}' target='_blank'>{$text}</a>";
    }

}
