=== Learnworlds-SSO ===
Contributors: learnworlds
Tags: Learnworlds, SSO, eLearning, WooCommerce, Login, Reset, Register
Requires at least: 4.8
Tested up to: 6.6.2
Stable tag: 1.8
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Give your students access to their school account right from your WordPress page.

== Description ==

**The LearnWorlds SSO plugin connects a WordPress site with your LearnWorlds School for seamless browsing between them. It creates accounts, signs users in and keeps user login in both sites.**

*This plugin requires a plan that includes SSO/API integration with LearnWorlds. You will need to contact the [LearnWorlds support](mailto:support@learnworlds.com) to receive the API credentials.*

The LearnWorlds SSO plugin Improves the browsing experience of your students, by taking away the need to create new accounts or re-enter an email/password.

**Easy Setup**

You can now connect your website's login with LearnWorlds for a seamless browsing experience without the need to have a technical team at your side. The plugin provides a widget and also a shortcode that you may use to create SSO links from your WordPress site to your LearnWorlds School.
An example of a shortcode might be: 
```
[learnworlds-sso-link url="http://myschool.learnworlds.com" text="Login to school" logged-in-text="Go to school"]
```

**WooCommerce Compatible**

This plugin also supports the WooCommerce plugin and allows your website to use the default account management pages that are installed by the WooCommerce plugin.

**Automatically Updates Email**

Every time a registered Learnworlds users changes their e-mail in their WordPress profile, the plugin updates that e-mail to the Learnworlds database as well.

**Support & Installation Help**

LearnWorlds' Customer Success is always here to help with the integration, questions and issues you might encounter along the way. Contact us at [LearnWorlds support](mailto:support@learnworlds.com).


== Frequently Asked Questions ==

= In which LearnWorlds plan is SSO feature available  =

This plugin [requires a plan](http://www.learnworlds.com/pricing) -**Learning Center Plan** and above- that includes [SSO/API](https://docs.learnworlds.com/) integration with LearnWorlds.

= Can I add links to my WordPress site pointing to the school or any page of the school  =

Yes, the plugin comes with a Widget that accepts a URL (to the school) and a text to display on your site.
Similarly, there is a special Menu item and finally you can place a link practically anywhere in your site by using the special shortcode provided.

== Screenshots ==

1. LearnWorlds School Sign in Sign up
2. Add LearnWorlds SSO plugin 
3. LearnWorlds SSO Plugin Setup page
4. WordPress Login form 
5. WooCommerce Login Form

== Installation ==

1. Download the plugin using the Plugins`->`Add New page.
1. Activate the plugin by going to Plugins`->`Installed Plugins`->`Activate.
1. Contact LearnWorlds' [Customer Success team](https://support.learnworlds.com) to get the credentials you need and configure your school for SSO.
1. Go to Settings`->`LearnWorlds SSO to enter the credentials and set up the plugin.
1. Use the shortcode and/or the widget where you need it to allow SSO to your Wordpress users.

== Changelog ==
= 1.8 =
* Improve compatibility with other plugins

= 1.7 =
* Fix SSO menu links and shortcode

= 1.6 =
* Update instructions for Custom URL SSO setup in the connected LearnWorlds school

= 1.5 =
* Fix issue with emails containing plus sign

= 1.4 =
* Add Settings shortcut on plugins list
* Add the ability to reset LearnWorlds User ID

= 1.1 =
* Add LW SSO Menu Link for WP
* When register with WooCommerce the user would be redirected to School
* Add logged in and logout text for Shortcode and widget
* Add fix css class on sso links  (widget and shortcode) to style the elements

= 1.0 =
* Initial release
