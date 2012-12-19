=== Plugin Name ===
Contributors: yoran
Donate link: http://yoranbrondsema.net
Tags: geolocation, travel, blog, google maps
Requires at least: 3.5
Tested up to: 3.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Keep a history of geographical locations for your travel blog.

== Description ==
This plugin allows the administrator to keep track of a history of geographical locations. It was developed for a travel blog and the geographical locations were drawn onto a Google Maps widget so that visitors could keep track of the blogger's traveling.

A live example can be seen on [the author's travel blog](http://yoranbrondsema.net "Yoran Brondsema's travel blog") (in Dutch).

Its main features are
* Add, edit and remove locations easily from the Admin panel
* Enter geographical coordinates manually or parse from a Google Maps URL
* Assign a date to each location for a true history of locations
* Assign a label to each location for easy identification
* Exposes a simple API to the front-end
* Dynamically request locations with AJAX

The plugin is administrated from the admin (addition and removal of geolocations) and it exposes an API to the front-end. Once the plugin is installed, a theme can call the following functions:
* <code>lochis_get_location_history()</code>: returns an array containing the whole history of geolocations, sorted by ascending date.
* <code>lochis_get_latest_location()</code>: returns only the most recent geolocation.

Each of these functions also has an AJAX-equivalent that returns a JSON-object instead of a PHP object. This way you can use the geolocations in Javascript, for instance to be shown in a Google Maps widget. The AJAX functions are:
* <code>lochis_ajax_get_location_history()</code>
* <code>lochis_ajax_get_latest_location()</code>

== Installation ==

To install the plugin, follow the next steps.

1. Upload the directory 'geolocation-history' to the '/wp-content/plugins' directory
2. Activate the plugin through the 'Plugins' menu in Wordpress
3. Administrate the plugin from the backend and use the API functions in your theme

== Frequently Asked Questions ==

= How do I use Google Maps URLs to insert a location? =

The plugin can parse geographical coordinates (latitude and longitude) from a Google Maps URL. To fetch the URL, go to the location in Google Maps and click on the "chain" next to the printing logo, on the left. There, copy the link and paste it in the appropriate input field in the plugin back-end. Click on the "Parse URL" button and the geographical coordinates appear magically in the appropriate input fields. 
== Screenshots ==

1. The administrator part of the plugin.
2. An example of how the plugin was used to keep track of the locations of the blog's author while he was traveling.

== Changelog ==

= 1.0 =
* First release.
