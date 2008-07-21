=== Contribution ===
Contributors: kriskelly
Tags: contribution, collection, form
Requires at least: 1.0.0 RC3
Tested up to: 1.0.0 RC3
Stable tag: 0.1

Creates public interface for users to contribute stories and files to your archive.

== Description ==

The contribution creates a public interface for users to contribute stories and files to your archive.  You will be able to access this at http://www.youromekainstall.com/contribute/.

== Configuration & Tips ==

1. If you want the application to send confirmation emails to the people who contribute to the archive, configure that email address by clicking the 'Configure' link on the admin/plugins page.

2. Your public theme does not link to the Contribution page by default, so you will need to add a link somewhere on your public theme.  You can either add another entry to the nav() function call in the common/header.php file, which will put the link in your theme's upper navigation, or you can put the php function contribution_link_to_contribute('Text of My Link') somewhere in your theme.

3. First name, last name & email address are required fields for contributors to the site.  Pretty much all of the rest of the fields are optional, including the race, gender, occupation, etc. fields at the bottom of the form.  

4. If you feel the urge to mess with the HTML/source code of the Contribution plugin, the plugin is located in the plugins/Contribution folder of your Omeka installation.  Inside of that, the contribution form is located at theme/contribution/add.php.  All of the optional fields can be removed from the form, but removing any of the required fields  may cause unknown behavior.

