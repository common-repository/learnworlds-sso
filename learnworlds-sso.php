<?php
/*
Plugin Name: Learnworlds SSO
Plugin URI: https://learnworlds.com
Description: Give your students access to their school account right from your WordPress page.
Version: 1.8
Author: Learnworlds
Author URI: https://www.learnworlds.com/
License: GPLv2 or later
*/

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'class.learnworlds-sso-widget.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'class.learnworlds-sso-settings.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'class.learnworlds-sso-shortcode.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'class.learnworlds-sso-route.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'class.learnworlds-sso-menu.php');

$learnworlds_sso_route = new Learnworlds_SSO_Route();
$learnworlds_sso_settings = new Learnworlds_SSO_Settings();
$learnworlds_sso_shortcode = new Learnworlds_SSO_Shortcode();
$learnworlds_sso_widget = new Learnworlds_SSO_Widget();
$learnworlds_sso_menu = new Learnworlds_SSO_Menu();
