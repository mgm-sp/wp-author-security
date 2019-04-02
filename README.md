
# WP Author Security

This Wordpress plugin protects against user enumeration attacks on author pages.

## Description

By default, Wordpress will display some sensitive information on author pages. The author page is typically called by requesting the URI:

     https://yourdomain.com/?author=id

 or with permalinks 

     https://yourdomain.com/author/username

The page will include the full name (first and last name) as well as the username of the author which is used to login to Wordpress. 

In some cases, it is not wanted to expose this information to the public. An attacker is able to brute-force valid IDs or valid usernames. This information might be used for further attacks like social-engineering attacks or login brute-force attacks with gathered usernames. 

By using the extension, you are able to disable the author pages either completely or only for users that do not have any published posts yet. When the page is disabled the default 404 page not found is displayed.

## Installation

 - put the plugin's folder 'wp-author-security' into the Wordpress plugin folder 'wp-content/plugins/' (e.g. via ftp)
 - activate the plugin in the Wordpress backend
 - customize the settings by navigation to Settings -> WP Author Security

## Author

 Alexander Elchlepp
