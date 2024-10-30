<?php

class Learnworlds_SSO_Widget extends WP_Widget {

    const WIDGET_NAME = 'learnworlds_sso_widget';

    // adds a widget that contains the shordcode from this plugin
    public function __construct() {
        parent::__construct(self::WIDGET_NAME, 'Learnworlds SSO');
        add_action('widgets_init', function () {
            register_widget('Learnworlds_SSO_Widget');
        });
    }

    public function form($instance) {
        $redirect_url = $instance['redirectUrl'];
        $link_text = $instance['linkText'];
        $logged_in_link_text = $instance['loggedInLinkText'];
        
        ?>
        <p>
            <label>Redirect URL</label> 
            <input class="widefat" name="<?= $this->get_field_name('redirectUrl') ?>" type="text" value="<?= $redirect_url ?>">
        </p>
        <p>
            <label>Text when logged out</label> 
            <input class="widefat" name="<?= $this->get_field_name('linkText') ?>" type="text" value="<?= $link_text ?>">
        </p>
        <p>
            <label>Text when logged in</label> 
            <input class="widefat" name="<?= $this->get_field_name('loggedInLinkText') ?>" type="text" value="<?= $logged_in_link_text ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['redirectUrl'] = isset($new_instance['redirectUrl']) ? $new_instance['redirectUrl'] : '';
        $instance['linkText'] = isset($new_instance['linkText']) ? wp_strip_all_tags($new_instance['linkText']) : '';
        $instance['loggedInLinkText'] = isset($new_instance['loggedInLinkText']) ? wp_strip_all_tags($new_instance['loggedInLinkText']) : '';

        return $instance;
    }

    public function widget($args, $instance) {
        extract($args);
        $redirect_url = $instance['redirectUrl'];
        $link_text = $instance['linkText'];
        $logged_in_link_text = $instance['loggedInLinkText'];

        echo $before_widget; // WordPress core before_widget hook (always include)
        echo do_shortcode("[learnworlds-sso-link url='{$redirect_url}' text='{$link_text}' logged-in-text='{$logged_in_link_text}']"); // use shortcode inside widget
        echo $after_widget; // WordPress core after_widget hook (always include)
    }

}
