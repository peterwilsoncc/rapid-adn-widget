=== Rapid App.Net Widget ===
Contributors: peterwilsoncc
Tags: adn, app.net, widget
Stable tag: 1.2
Requires at least: 3.4.2
Tested up to: 4.3
License: GPLv2

Display posts from one or more App.net accounts using a WordPress widget.

== Description ==

Display your latest posts in a WordPress widget without slowing your
website.

The Rapid App.net Widget doesn't apply any styling to your website, giving
you complete control over the look.

Posts and your content are loaded separately to ensure a delayed response
from App.net doesn't slow down your website.

Based upon [Rapid Twitter Widget](http://wordpress.org/extend/plugins/rapid-twitter-widget/).


== Development on GitHub ==

Development of this plugin is taking place in a 
[GitHub repository](https://github.com/peterwilsoncc/rapid-adn-widget).

Only tagged releases will be added to the WordPress.org svn repository.


== Frequently Asked Questions ==

= Can I customise the HTML output? =

Yes, you can create the JavaScript function `RapidADN.generate_html` and
the Rapid App.net Widget will defer to your custom script.

Your custom function will need to be defined prior to the Rapid App.net 
Widget JavaScript file loading.

Start your file:

`if(typeof(RapidADN)=='undefined'){RapidADN={};}

RapidADN.generate_html = function (screen_name, posts) {
	return '<li>Hello</li>';
}`

You can convert App.net entities by calling the 
function `RapidADN.process_entities( post )` and obtain the relative time
with the function `RapidADN.relative_time(time)`.

= Can multiple instances of the widget be used? =

Yes.

= I see less than the requested number of posts displayed =
 
The Rapid App.net widget may return less than the requested number of 
posts if the requested account has a high number of reposts in its timeline.


= What's with the strange class names like .adn__mention and .adn__mention--reply? = 

The widget uses the BEM naming convention for class names, which has been 
nicely [summarised by Nicolas Gallagher](https://gist.github.com/1309546).

They're a little strange at first but I find them surprisingly useful. 

== Changelog ==

= 1.2 =

* Compatibility with WordPress 4.3

= 1.1 =

* Fixes bug preventing multiple widgets appearing on a single page.

= 1.0 =

* Version number skipped.

= 0.9 =

* Initial version duplicating Rapid Twitter Widget.