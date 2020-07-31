=== WP Author Security ===
Contributors: mgm-sp
Tags: security, user enumeration
Requires at least: 4.7
Tested up to: 5.4
Requires PHP: 5.6
Stable tag: 1.2.0
License: GPLv3

Protect against user enumeration attacks on author pages.

== Description ==
Protects against user enumeration attacks for author pages. By default Wordpress will display some sensitive information on author pages. The author page is typically called by requesting the URI https://yourdomain.com/?author=&lt;id&gt; or with permalinks https://yourdomain.com/author/&lt;username&gt;. The page will include the full name (first and last name) as well as the username of the author which is used to login to Wordpress. 
In some cases, it is not wanted to expose this information to the public. An attacker is able to brute-force valid IDs or valid username. This information might be used for further attacks like social-engineering attacks or login brute-force attacks with gathered usernames. By using the extension, you are able to disable the author pages either completely or only for users that do not have any published posts yet. When the page is disabled the default 404 page not found is displayed.