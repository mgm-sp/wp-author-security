=== WP Author Security ===
Contributors: mgm-sp
Tags: security, user-enumeration, privacy, author, wpscan
Requires at least: 4.7
Tested up to: 5.4
Requires PHP: 5.6
Stable tag: 1.2.1
License: GPLv3

Protect against user enumeration attacks on author pages and other places where valid user names can be obtained.

== Description ==
WP Author Security is a lightweight but powerful plugin to protect against user enumeration attacks on author pages and other places where valid user names can be obtained. 

By default Wordpress will display some sensitive information on author pages. 
The author page is typically called by requesting the URI `https://yourdomain.tld/?author=<id>` or with permalinks `https://yourdomain.tld/author/<username>`. 
The page will include (depending on your theme) the full name (first and last name) as well as the username of the author which is used to login to Wordpress. 

In some cases, it is not wanted to expose this information to the public. An attacker is able to brute-force valid IDs or valid usernames. This information might be used for further attacks like social-engineering attacks or login brute-force attacks with gathered usernames. 
*However, when using the plugin and you disable author pages completely it must be noted that you need to take care that your active theme will not display the author name itself on posts like "Posted by admin" or something like that. This is something the plugin will not handle (at the moment).*

By using the extension, you are able to disable the author pages either completely or display them only when the author has at least one published post. When the page is disabled the default 404 error page of the active theme is displayed.

In addition, the plugin will also protect other locations which are commonly used by attackers to gather valid user names. These are:

* The REST API for users which will list all users with published posts by default.
  `https://yourdomain.tld/wp-json/wp/v2/users`
* The login page where different error messages will indicate whether an entered user name or mail address exists or not. The plugin will display a neutral error messages indipendently whether the user exists or not.
* The password forgotten function will also allow an attacker to check for the existence of an user. As for the login page the plugin will display a neutral message even when the user does not exists.

== Screenshots ==
1. Admin settings
2. Display 404 page when requesting author page by user ID.
2. Login error message when the user name exists but a wrong password is entered.

== Changelog ==
	
= 1.2.1 =
* updated documentation

= 1.2.0 =
* added protection for login and password forgotten page
* added language support for de/en

= 1.1.0 =
* added protection for REST API

= 1.0.0 = 
* initial release

== Installation ==

1. Install the plugin via the Dashboard `Plugins -> Add new` or upload the plugin's folder 'wp-author-security' from the zip into your Wordpress plugin folder `wp-content/plugins/` (e.g. via ftp)
2. Activate the plugin in the Wordpress backend
3. Customize the settings by navigation to `Settings -> WP Author Security`

== Upgrade Notice ==
No special actions required. Simply update the plugin and adjust settings for new configuration options.