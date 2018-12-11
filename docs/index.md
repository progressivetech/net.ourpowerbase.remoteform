# Remoteform

Remoteform allows you to add a CiviCRM form to a remote web site via a few
lines of javascript code.

Using Remoteform is a two step process.

1. Set things up in CiviCRM.
2. Set things up on your web site

Before you begin, be sure you are logged into both CiviCRM and also the part of
your web site that allows you to add content.

## In CiviCRM

First, install and enable the Remoteform extension.

Second, click `Adminstration -> Customize data and screens -> Remote Forms.`

Enter your web site's address. Only the addresses listed here will be able to
submit forms to your CiviCRM instance.

![Choose URLs to allow](/images/cors-configuration.png)

Third, edit the profile or contribution page to enable remoteform. Here's an
example of a profile page (look in `Profile Settings -> Advanced Settings`):

![Enable remoteform for a contribution](/images/profile-enable.png)

If you want to place a contribution page on your web site, you will see the
same field option on the main Title configuration tab of all your contribution
pages.

**Important**: Be sure to click save after you click the checkbox! If you don't
save the profile or contribution page, then Remoteform will not work.

Once saved, then go back in and copy the javascript code.

## In your web site

Everyone's web site is different. Here are examples for how to paste in
javascript code on Drupal and Wordpress, two of the most popular tools for
building web sites.

 * [Add remoteform to Drupal](drupal.md)
 * [Add remoteform to Wordpress](wordpress.md)

