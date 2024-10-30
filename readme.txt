=== Math Quiz ===
Contributors: atitan
Donate link: 
Tags: comments, spam, captcha
Requires at least: 3.5
Tested up to: 5.2
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Math Quiz generates dynamic math problem in the comment form to beat spam robots.

== Description ==

Math Quiz generates dynamic math problem in the comment form to beat spam robots and supports flexible form style customization.

Demo website: https://atifans.net/ (with Supercache and AJAX comment form enabled)

For PayPal donation, please use this email address: info _a.t_ atifans.net

Features:

*   Dynamic problems prevent spam robots from posting unwanted content.
*   Quiz form inserted using AJAX for better compatibility with HTML cache plugins.
*   Form style and position can be customized in the control panel.
*   Trackbacks and Pingbacks are checked with DNS queries to avoid abuse.
*   Optional login page protection.

Translators:

* SLV-es (Spanish)
* Joao Paulo (Portuguese)

== Installation ==

Install this plugin is easy.

1. Upload whole directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done!

== Frequently Asked Questions ==

= Why it always tells me that I'm failed to answer the quiz? =

"Failed to answer" means the plugin encounters some problems to get needed information. If you think it's my fault, feel free to inform me. :)

= Why the plugin can't be removed? =

Freetype, a text renderer, keeps the font file handle open, causing Windows and other OS to lock the font file. Deactivate the plugin before you update it.

== Changelog ==

= 2.0.0 =
* Use database for problems storage instead of PHP session

= 1.9.4 =
* Fix undefined array index notice
* Fix session restore issue

= 1.9.3 =
* Automatically reload math problem on form submit

= 1.9.2 =
* Fix typo on text-domain

= 1.9.1 =
* Block IP-unmatched pingback request

= 1.9 =
* Add Portuguese translation. Thanks to Joao Paulo

= 1.8.2 =
* Fix plugin crash on checking answer

= 1.8.1 =
* Hotfix: missing phar signature

= 1.8 =
* Replace insecure random number generator
* Plugin can now acts as login captcha

= 1.7 =
* Add Spanish translation. Thanks to SLV-es
* Escape output to prevent code execution
* Fix missing option in admin panel

= 1.6 =
* Fix http header sent out too early
* WP 4.1 compatibility check

= 1.5 =
* Nothing new, just compatibility check

= 1.4 =
* Remove space that may cause error on activation

= 1.3 =
* Add legacy text mode for those don't like the picture.

= 1.2 =
* Fix sessionid check that cause visitors unable to answer.

= 1.1 =
* Add sessionid check to avoid php warning.

= 1.0 =
* Add DNS check for trackback spams.
* Fix admin panel JS error

= 0.9 =
* Enhance the problem complexity to avoid OCRs.

= 0.8 =
* Add support for cross-domain sites.

= 0.7 =
* Fix failure when answer is 0.

= 0.6 =
* Problems now come in pictured form.
* Only addition and subtraction are available.

= 0.5 =
* Fix compatibility with built-in theme, such as twentyeleven.

= 0.4 =
* Users can now refresh the quiz themselves.
* User defined quiz form was deprecated, use CSS for customization instead.
* Added new form insert method.

= 0.3 =
* More quiz types were added.
* The form could now choose to insert before or after the selected element.

= 0.2 =
* Admin panel, Custom quiz form, Problem choices were introduced.
* zh_TW translation is now available.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 2.0.0 =
Use database for problems storage

= 1.9.4 =
Fix undefined array index notice
Fix session restore issue

= 1.9.3 =
Automatically reload math problem on form submit

= 1.9.2 =
Fixed translation

= 1.9.1 =
IP check on pingback added

= 1.9 =
Portuguese translation added

= 1.8.2 =
Fix plugin crash on checking answer

= 1.8.1 =
Hotfix: missing phar signature

= 1.8 =
Replace insecure random number generator
Plugin can now acts as login captcha

= 1.7 =
Spanish translation now available with some bugs fixed

= 1.6 =
Fix http header sent out too early

= 1.5 =
Wordpress 4.0 compatibility check

= 1.4 =
Remove space that may cause error on activation

= 1.3 =
Add legacy text mode.

= 1.2 =
Fix sessionid check that cause visitors unable to answer.

= 1.1 =
Add sessionid check to avoid php warning.

= 1.0 =
Add DNS check for trackback spams.
Fix admin panel JS error.

= 0.9 =
Enhance the problem complexity to avoid OCRs.

= 0.8 =
Add support for cross-domain sites.

= 0.7 =
Fix failure when answer is 0.

= 0.6 =
Problems now come in pictured form.

= 0.5 =
Fix compatibility with built-in theme, such as twentyeleven.

= 0.4 =
User defined quiz form deprecated.

= 0.3 =
New quiz types are available.

= 0.2 =
Several new functions were introduced.

= 0.1 =
Initial release
