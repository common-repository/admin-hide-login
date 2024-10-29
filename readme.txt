=== Admin Hide Login ===
Contributors: wphoundstore
Donate link: https://www.paypal.me/wphound/20
Tags: rename, login, wp-login, wp-login.php, login url ,hide backend ,login hide
Requires at least: 4.0
Tested up to: 4.8.1
Stable tag: 3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Change wp-login.php to anything you want.
Like This: -  https://wordpress.org/wp-admin/  =>  https://wordpress.org/anything/

== Description ==

*Admin Hide Login* is a very light plugin that lets you easily and safely change the url of the login form page to anything you want. 

It doesn't literally rename or change files in core, nor does it add rewrite rules.

It simply intercepts page requests and works on any WordPress website. 

The wp-admin directory and wp-login.php page become inaccessible, so you should bookmark or remember the url. 

Deactivating this plugin brings your site back exactly to the state it was before.

<strong>For Example:</strong> 
https://wordpress.org/wp-admin/  =>  https://wordpress.org/anything/

= Compatibility =

Requires WordPress 4.0 or higher. All login related things such as the registration form, lost password form, login widget and expired sessions just keep working.

It's also compatible with any plugin that hooks in the login form, including:

* BuddyPress,
* bbPress,
* Limit Login Attempts,
* and User Switching.

Obviously it doesn't work with plugins or themes that *hardcoded* wp-login.php.

Works with multisite, but not tested with subdomains. Activating it for a network allows you to set a networkwide default. Individual sites can still rename their login page to something else.

If you're using a **page caching plugin** other than WP Rocket, you should add the slug of the new login url to the list of pages not to cache. WP Rocket is already fully compatible with the plugin.

For W3 Total Cache and WP Super Cache this plugin will give you a message with a link to the field you should update.

== Installation ==

1. Go to Plugins > Add New.
2. Search for *Admin Hide Login*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Change your login url there.
5. You can change this option any time you want, just go back to Settings > General > Admin Hide Login.

== Frequently Asked Questions ==

If you have any question do let me know on care@wphound.com


== Changelog ==

= 1.0 =
Initial Release of the plugin