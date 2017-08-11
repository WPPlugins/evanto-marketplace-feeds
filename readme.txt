=== Envato Marketplace Feeds ===
Contributors: Gilbert Pellegrom
Donate link: http://www.gilbertpellegrom.co.uk/projects/envato-marketplace-feeds/
Tags: envato, marketplace, feeds, sidebar, api, page
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 0.3

Lets you add feeds from the Envato Marketplaces to your blog. Requires PHP 5+ to run.

== Description ==

This plugin will let you easily add feeds from the Envato Marketplaces to your blog using the new 
Marketplace API. There is a full settings page which lets you dynamically create a list of feeds with 
different information. Then in your blog all you have to do is call the function: emf_getfeed(feedID); 
and input you feed ID. You can create as many different feeds as you want.

**Version History**

* v0.1 - Release Version
* v0.2 - Bug fix: Activation not creating table.
* v0.3 - Added support for thumbnails and images.

== Installation ==

After you've downloaded and extracted the files:

1. Check you have PHP 5+ running on your server. Otherwise this plugin won't work.
2. Upload the complete 'envato-marketplace-feeds' folder to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. You're done.

You can access the EMF Settings via the "settings" menu to add your feeds.

== Frequently Asked Questions ==

= Why will this plugin only work with PHP 5+? =

This plugin uses PHP's SimpleXMLElement class to handle the XML data is recieves from the Envato Marketplace API.
This functionality is only avialable in the later versions of PHP (ie. v5+);

== Usage ==

There are two ways to use this plugin. To get an already fomratted version of a feed:

1. Create your feeds in the EMF Settings page and remeber the ID for that feed.
2. Add this function where you want your feed to appear in your Wordpress theme `<?php if(function_exists('emf_getfeed')) emf_getfeed($feedID); ?>`
3. Replace the $feedID with the ID from your feed.

You can also get the XML element so you can format your own feed:

1. Create your feeds in the EMF Settings page and remeber the ID for that feed.
2. Add this function where you want your feed to appear in your Wordpress theme `<?php if(function_exists('emf_getfeed')) $xml = emf_getfeed($feedID, true); ?>`
3. Replace the $feedID with the ID from your feed.
4. You can now use $xml to output the feed in your own format.