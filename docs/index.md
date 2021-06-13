# Remoteform

Remoteform is intended for CiviCRM users who maintain their web site on a
separate server from their CiviCRM installation (e.g. for
[Powerbase](https://ourpowerbase.net/) users).

With remoteform you can add a fully functional CiviCRM form to a remote web
site via a few lines of javascript code. Your site visitors will no longer need
to be re-directed to your CiviCRM installation to fill out a profile or make a
contribution. There is no need to match your CiviCRM theme with your web site
look and feel. *The entire interaction takes place on your own web site.*

Furthermore, with the help of a fully documented [api](api.md) users with
advanced javascript and CSS skills can make the form look exactly how you want
it, so it completely blends in with your web site's look and feel.

To use Remoteform, your web site must run via https.

Using Remoteform is a two step process.

1. Set things up in CiviCRM.
2. Set things up on your web site

Before you begin, be sure you are logged into both CiviCRM and your web site.

## In CiviCRM

First, install and enable the Remoteform extension.

Second, click `Adminstration -> Customize data and screens -> Remote Forms.`

Enter your web site's address. Only the addresses listed here will be able to
submit forms to your CiviCRM instance.

![Choose URLs to allow](/images/cors-configuration.png)

If this configuration is not sufficient to enable access to the CiviCRM server 
from your intended client site, try the guidance provided at this link:

 * [Enable CORS access on a Drupal site hosting CiviCRM RemoteForms as a service](drupal_civicrm.md)
 * [Enable CORS access on a Wordpress site hosting CiviCRM RemoteForms as a service](wordpress_civicrm.md)

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
javascript code on Drupal and Wordpress client sites, two of the most 
popular tools for building web sites.

 * [Add remoteform to a (client) website hosted on Drupal](drupal_website.md)
 * [Add remoteform to a (client) website hosted on Wordpress](wordpress_website.md)

Most web site editing tools do not let you paste in javascript code without
making some kind of adjustment.

## Debugging

When you add your javascript code, you may get an error telling you to check the console log.

![Check console log](/images/check-console-log.png)

You can do that in either Firefox or Chrome by right clicking on the page and choosing the Inspector. Below is what it looks like in Firefox.

![Right click to inspect](/images/right-click-inspect.png)

Click the Console tab, and check for messages.

![Check console log](/images/debug-console.png)

In this case, the error message is telling us that CORS is not set correctly.
That means that the web site you're pasting the javascript code into has not been
properly configured in CiviCRM on the Remote Forms page.

## Debugging Undisplayed Fields and Unstored Data

When building your profile (at /civicrm/admin/uf/group?reset=1), do not use 'Primary' 
for any of your Contact fields.  For whatever reason, although the fields are exhibited 
on the (client) website, the data does not get stored on the (server) CiviCRM site.  

It appears that if you omit the country selector field on your form, that the state 
selector will be built on a default country matching that of the 'Default Organization 
Address' configured at: /civicrm/admin/domain.  

If this guidance is not enough to ensure that your profile fields configured 
on the (server) CiviCRM installation collect data on the (client) website, 
and store that data in the database hosted on the (server) CiviCRM installation, 
try running this API call via the command line or the API explorer on the 
(server) CiviCRM installation: 

	vendor/bin/cv api Profile.getfields api_action=submit profile_id=YOURPROFILEID

If your missing field shows up in response to this query, 
then this is probably a remoteform bug. 

 * [Report bugs in the RemoteForm CiviCRM extension](https://github.com/progressivetech/net.ourpowerbase.remoteform/issues)

If it does not show up, then it's a core api v3 bug.

 * [Report bugs in the CiviCRM API for v3](this-link-is-broken-and-must-be-researched)

Sadly, api v3 is a bit rickety and not very well supported now that api v4 is on the 
scene. In the long run, this extension will either need to switch to api v4, or 
somehow work with afform.

