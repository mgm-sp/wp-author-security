=== WP Author Security ===
Contributors: mgmsp
Tags: security, user-enumeration, privacy, author, wpscan
Requires at least: 4.7
Tested up to: 6.1
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv3

Protect against user enumeration attacks on author pages and other places where valid user names can be obtained.

== Description ==
WP Author Security is a lightweight but powerful plugin to protect against user enumeration attacks on author pages and other places where valid user names can be obtained. 

By default, Wordpress will display some sensitive information on author pages. 
The author page is typically called by requesting the URI `https://yourdomain.tld/?author=<id>` or with permalinks `https://yourdomain.tld/author/<username>`. 
The page will include (depending on your theme) the full name (first and last name) as well as the username of the author which is used to log in to Wordpress. 

In some cases, it is not wanted to expose this information to the public. An attacker is able to brute force valid IDs or valid usernames. This information might be used for further attacks like social engineering attacks or log in brute force attacks with gathered usernames. 
*However, when using the plugin and you disable author pages completely it must be noted that you need to take care that your active theme will not display the author name itself on posts like "Posted by admin" or something like that. This is something the plugin will not handle (at the moment).*

By using the extension, you are able to disable the author pages either completely or display them only when the author has at least one published post. When the page is disabled the default 404 error page of the active theme is displayed.

In addition, the plugin will also protect other locations which are commonly used by attackers to gather valid user names. These are:

* The REST API for users which will list all users with published posts by default.
  `https://yourdomain.tld/wp-json/wp/v2/users`
* The log in page where different error messages will indicate whether an entered user name or mail address exists or not. The plugin will display a neutral error message independently whether the user exists or not.
* The password forgotten function will also allow an attacker to check for the existence of a user. As for the log in page the plugin will display a neutral message even when the user does not exists.
* Requesting the feed endpoint /feed of your blog will also allow others to see the username or display name of the author. The plugin will remove the name from the result list.
* Wordpress supports so-called oEmbeds. This is a technique to embed a reference to a post into another post. However, this reference will also contain the author name and a direct link to the profile page. The plugin will also remove the name and link here.
* Since Wordpress 5.5 a default sitemap can be reached via /wp-sitemap.xml. This sitemap will disclose the usernames of all authors. If this should not be disclosed you are able to disable this feature of Wordpress.

== Screenshots ==
1. Admin settings
2. 404 page when requesting author page by user ID.
3. Log in error message when the user name exists but a wrong password is entered.

== Changelog ==

= 1.5.0 =
* added basic statistics to the settings page
* bugfix password forgotten protection

= 1.4.1 =
* Bugfix error on login check

= 1.4.0 =
* added protection for the wp-sitemap.xml author disclosure

= 1.3.0 =
* added protection for the /feed endpoint
* added protection for the oEmbed endpoint

= 1.2.1 =
* updated documentation
* bugfix wrong mail detection

= 1.2.0 =
* added protection for log in and password forgotten page
* added language support for de/en

= 1.1.0 =
* added protection for REST API

= 1.0.0 = 
* initial release

== Installation ==

1. Install the plugin via the Dashboard `Plugins -> Add new` or upload the plugin's folder 'wp-author-security' from the zip into your Wordpress plugin folder `wp-content/plugins/` (e.g. via ftp)
2. Activate the plugin in the Wordpress backend
3. Customize the settings by navigating to `Settings -> WP Author Security`

== Upgrade Notice ==
No special actions required. Simply update the plugin and adjust settings for new configuration options.