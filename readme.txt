=== Contribution ===
Tags: contribution, collection, form
Requires at least: 0.10
Tested up to: 0.10

Creates public interface for users to contribute stories and files to your archive.

== Description ==

The contribution creates a public interface for users to contribute stories and files to your archive.  By default you can access this at http://www.youromekainstall.com/contribution/.  The 'contribution' part of the URL can be configured through the plugin if you would like the URL to appear differently.

== Configuration & Tips ==

1. Your public theme does not link to the Contribution page by default, so you will need to add a link somewhere on your public theme.  You can either add another entry to the nav() function call in the common/header.php file, which will put the link in your theme's upper navigation, or you can put the php function contribution_link_to_contribute('Text of My Link') somewhere in your theme.
