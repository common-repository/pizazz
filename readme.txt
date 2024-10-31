=== Pizazz ===
Contributors: Luiji
Tags: zazzle, ads, monetize, revenue, store, widget, mula
Requires at least: 3.4.2
Tested up to: 3.5.1
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A widget that pulls a Zazzle feed and displays products based on a query

== Description ==

This widget provides a nice, optionally thumbnailed list of Zazzle products
based on query parameters you configure it with, including the store, search
terms and sorting mechanism. Unlike other Zazzle widgets, this one only uses
the referral identifier that you give it, or none at all if you do not
specify one (you know who you are -- *wink*).

Additionally, this widget can be embedded into your posts and pages with a
shortcode. These shortcodes look something like `[pizazz query=asparagus rows=8
columns=3]`.

I use PCRE to parse the RSS feeds. If Pizazz does not work, make sure that PCRE
is available in your PHP installation. It is available on most PHP servers.

*Pizazz is multisite-compatible!*

**NOTICE**: I don't have a testing staff, and my ability to think of every
potential problem is limited. If you have problems make sure to report them
to me or I won't know they exist for a long time!

== Installation ==

1. Upload files to `wp-content/plugins/pizazz`
2. Log in as an adminstrator and enter the control panel
3. Activate the plugin through the 'Plugins' menu
4. Add and configure the widget through the 'Widgets' menu
5. Bon appétit!

If it doesn't work, check to see that PHP's PCRE system is working.

== Frequently Asked Questions ==

= What's a Zazzle? =

Zazzle is a website that allows users to buy, sell and design custom shirts,
mugs, speakers and much more. You can make a lot of money by advertising
Zazzle products on your website using the referral ID system on top of money
you earn from your own product sales. For more information visit
<http://zazzle.com/>.

= How do I embed Pizazz in posts and pages? =

With the shortcode. See `Settings->Pizazz` for the documentation. They look
something like `[pizazz query=asparagus rows=8 columns=3]`.

= How do I set my associate ID for referrals? =

This setting used to be on a widget-by-widget basis, but upon the addition of
shortcodes it was moved to `Settings->Pizazz`.

= Why is it saying that the "query" returned no products? =

Generally for the same reason that putting your search parameters into Zazzle
doesn't return anything. None of your products have the keyword you specified
in it. You will rarely get anything if you classify your store name as a
keyword. Playing the the search parameters should help.

The other possibility is that there's a bug. If Zazzle returns proper results
given your parameters but Pizazz does not, contact me!

= Why are you not answering my support request(s)? =

WordPress.org doesn't e-mail me when anyone files support requests, so I have
to remember to check back regularly. I am terrible at remembering to "do <X>
regularly."

You can also comment at http://entertainingsoftware.com/wordpress/pizazz/,
where I /do/ get notifications.

== Screenshots ==

1. Default style of the widget
2. Customized widget testing out all of the style options
3. Configuration of the customized widget as seen in the Control Panel

== Changelog ==

= 1.3.1 =
* Bug where saving widget settings would hang

= 1.3.0 =
* Added ability to hide "See More" link in widgets/shortcodes
* Added ability to change country for prices in main settings zone

= 1.2.0 =
* Moved settings area to `Settings->Pizazz`
* Moved associate ID field to `Settings->Pizazz`
* Added `[pizazz]` shortcode to embed Pizazz to pages and posts
* New widget/shortcode option to configure alignment of text or hide it
* New widget/shortcode option to display the price of products
* New `Settings->Pizazz` option to open product links in new tabs/windows

= 1.1.1 =
* Fixed thumbnail resizer and made it so that it shouldn't break during future
  changes in the Zazzle RSS feed.
* Fixed a bug that broke the network adminstrative panel in some multisites.
* Fixed a bug that broke the "Network Activate" feature on multisites.
* Moved settings out of General and into their own panel, Settings->Pizazz.
* Switched from strict SimpleXML-based parser to a PCRE-based parser that will
  be much more accepting of the invalid RSS feeds that Zazzle often outputs.
  (There will be much less parsing errors.)

= 1.1.0 =
* Implemented configurable feed caching, which speeds up sites by up to 0.75
  seconds
* Fixed bug introduced in 1.0.1 where "See Also" link was broken
* Factored in popularity into feed requests to make results more randomized

= 1.0.1 =
* Fixed some bugs with 1x1 product tables and invalid column/row counts
* Stopped using GOTOs, which should make the widget run on more servers

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.3.1 =
Fixed bug where saving widget settings would hang.

= 1.3.0 =
Some new features: hide the "See More" button and switch the country used in to
query price information.

= 1.2.0 =
Added new shortcode to embed Pizazz in posts and pages, more customizations and
made it easier to use.

= 1.1.1 =
Switched from SimpleXML-based parser to a less strict PCRE-based parser, thus
compensating for Zazzle's often-broken RSS feeds. Pizazz is now multisite-
compatible.

= 1.1.0 =
Caching makes Pizazz noticably faster, "See More" link is fixed, less time
out errors, better randomization.

= 1.0.1 =
Fixed bug where 1x1 product tables resulted in errors. More PHP versions are
supported.
