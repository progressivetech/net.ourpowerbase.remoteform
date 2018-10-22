# Remoteform 

Remoteform allows you to add a CiviCRM form to a remote web site via a few lines of javascript code.

Currently, only profiles and contribution pages are supported (events and petitions are in the works).

## How does it work?

First, click `Adminstration -> Customize data and screens -> Remote Forms.`

Enter your web site's address. Only the addresses listed here will be able to submit forms to your CiviCRM instance.

![Choose URLs to allow](/images/cors-configuration.png)

Second, edit the profile or contribution page to enable remoteform. Here's an example of a profile page (look in Profile Settings -> Advanced Settings):

![Enable remoteform for a contribution](/images/profile-enable.png)

Third, copy and paste the provided javascript code to your remote web site and you are done.

![Profile shown on remote site](/images/profile-on-remote-site.png)

## Can I configure how the fields are displayed. 

Yes, the javascript api is [fully documented](docs/api.md). You can change just about everything.

## Is this secure?

This extension does open a tiny hole in your CiviCRM armour. Specifically, it allows the sites you specify to by-pass the normal [CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing) restrictions.

CORS prevents one web site from getting your web browser to post data to another web site, unless the website you are posting to specifically allows it. 

There is a good reason for CORS! The main reason is to prevent one malicious web site from taking over your browser and posting information to another web site without your knowledge (for example, a web site could secretly get your browser to change your password in your CiviCRM installation and then take over your account).

Remoteform mitigates against this danger in two ways:

 * You specify the sites to allow. If you specify your organization's web site, then a malicious user would have to take over your web site first
 * Remoteform refuses to operate if your browser is logged into your CiviCRM installation. Even if a malicious user could take over your site, they would not be able to do any damage to your site because all operations are performed as an anonymous user.

## License

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM (5.0)

