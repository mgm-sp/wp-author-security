
# WP Author Security

This Wordpress plugin protects against user enumeration attacks on author pages.

## Description

By default, Wordpress will display some sensitive information on author pages. The author page is typically called by requesting the URI:

     https://yourdomain.com/?author=id

 with permalinks 

     https://yourdomain.com/author/username

or using REST API

     https://yourdomain.com/wp-json/wp/v2/users

The page will include the full name (first and last name) as well as the username of the author which is used to login to Wordpress. 

In some cases, it is not wanted to expose this information to the public. An attacker is able to brute-force valid IDs or valid usernames. This information might be used for further attacks like social-engineering attacks or login brute-force attacks with gathered usernames. 

By using the extension, you are able to disable the author pages either completely or only for users that do not have any published posts yet. When the page is disabled the default 404 page not found is displayed.

In addition, the plugin will also protect other locations which are commonly used by attackers to gather valid user names. These are:

* The REST API for users which will list all users with published posts by default.
  `https://yourdomain.tld/wp-json/wp/v2/users`
* The log in page where different error messages will indicate whether an entered user name or mail address exists or not. The plugin will display a neutral error message independently whether the user exists or not.
* The password forgotten function will also allow an attacker to check for the existence of a user. As for the log in page the plugin will display a neutral message even when the user does not exists.
* Requesting the feed endpoint /feed of your blog will also allow others to see the username or display name of the author. The plugin will remove the name from the result list.
* Wordpress supports so-called oEmbeds. This is a technique where you can embed a reference to a post into another post. However, this reference will also contain the author name and a direct link to the profile page. The plugin will also remove the name and link here.
* Since Wordpress 5.5 a default sitemap can be reached via /wp-sitemap.xml. This sitemap will disclose the usernames of all authors. If this should not be disclosed you are able to disable this feature of Wordpress.



## Installation

The plugin is available as official Wordpress Plugin [WP Author Security](https://wordpress.org/plugins/wp-author-security/).
1. Install the plugin via the Dashboard `Plugins -> Add new` and search for "WP Author Security" or upload the plugin's folder 'wp-author-security' from the zip into your Wordpress plugin folder `wp-content/plugins/` (e.g. via ftp)
2. Activate the plugin in the Wordpress backend
3. Customize the settings by navigating to `Settings -> WP Author Security`

## Author

 Alexander Elchlepp
