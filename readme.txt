=== Rapid Twitter Widget ===
Contributors: peterwilsoncc, floatedesign
Tags: twitter, widget, tweets
Stable tag: 1.0
Requires at least: 3.4.2
Tested up to: 3.5.1
License: GPLv2

Display tweets from one or more Twitter accounts using a WordPress widget.

== Description ==

Display your latest tweets in a WordPress widget without slowing your
website.

The Rapid Twitter Widget doesn't apply any styling to your website, giving
you complete control over the look.

Tweets and your content are loaded separately to ensure a delayed response
from Twitter doesn't slow down your website.

Based upon Wickett Twitter Widget by Automattic (now part of Jetpack).


== Development on GitHub ==

Development of this plugin is taking place in a 
[GitHub repository](https://github.com/peterwilsoncc/rapid-twitter-widget).

Only tagged releases will be added to the WordPress.org svn repository.


== Frequently Asked Questions ==

= Can I customise the HTML output? =

Yes, you can create the JavaScript function `RapidTwitter.generate_html` and
the Rapid Twitter Widget will defer to your custom script.

Your custom function will need to be defined prior to the Rapid Twitter 
Widget JavaScript file loading.

Start your file:

`if(typeof(RapidTwitter)=='undefined'){RapidTwitter={};}

RapidTwitter.generate_html = function (screen_name, tweets) {
	return '<li>Hello</li>';
}`

You can convert Twitter entities by calling the 
function `RapidTwitter.process_entities( tweet )` and obtain the relative time
with the function `RapidTwitter.relative_time(time)`.

= Why have you re-written the Wickett Twitter Widget plugin? =

The Wickett Twitter Widget has been grandfathered by Automattic and moved 
into their mega-plugin Jetpack. I like the simplicity of the Twitter widget 
but do not wish to use other Jetpack features.

I switched to using JavaScript to load tweets as the original widget could 
slow down page load if Twitter's API was taking too long to respond. 

= Can multiple instances of the widget be used? =

Yes.

= Can private Twitter accounts be used? =

No. The widget does not support authenticated requests for private data.

= I see less than the requested number of Tweets displayed =

Twitter may return less than the requested number of Tweets if the 
requested account has a high number of @replies in its user timeline.

= What's with the strange class names like .tweet__mention and .tweet__mention--reply? = 

The widget uses the BEM naming convention for class names, which has been 
nicely [summarised by Nicolas Gallagher](https://gist.github.com/1309546).

They're a little strange at first but I find them surprisingly useful. 

== Changelog ==

= 0.3.3 =

* Store the widget HTML element on page load. Earlier version presumed
  class names that may not exist.

= 0.3.2 =

* Suppress http error code and check myself

= 0.3.1 =

* Use timesince class on datestamp for backward compatibility

= 0.3 =

* Add trimmed user flag to API call. 
* Add version constant

= 0.2 =

* Add tweet entities

= 0.1 =

* Initial version duplicating Automattic's original.