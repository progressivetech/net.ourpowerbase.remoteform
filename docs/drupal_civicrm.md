
# Hosting your CiviCRM RemoteForms on a Drupal site

The CiviCRM installation where you install the RemoteForms extension, 
and in which you collect the data collected by the forms exposed by it 
must also be configured to allow connections from the client website.  

## configuring your (server) CiviCRM installation to support CORS

The browser user interface provided by the RemoteForms extension 
(at the /civicrm/admin/remoteform path) is known to work for 
drupal7 installations.  

If you have installed the extension on a drupal 8/9 platform, 
to enable access from your client website(s) will require instead 
that you configure the cors.config stanza in the configuration file 
at: web/sites/default/services.yml .  

In your intial tests, try setting:

	enabled: true
	allowedOrigins: ['*']
	allowedMethods: ['*']
	allowedHeaders: ['*']

## tightening security

Once you have a working interaction between your (server) civicrm installation 
and your (client) website, you can begin to dial back the Headers and Methods 
allowed settings to determine what the minimum privileges required to make 
your form work might be.  

Start by limiting interactions to ONLY your intended (client) website(s):

	allowedOrigins: ['https://www.YOUR_CLIENT_WEBSITE_DOMAIN.org']

Next try limiting the allowed Methods, deleting or restoring one at a time, 
to determine which are required for successful interactions by your form:

	allowedMethods: ['HEAD','GET','POST','PUT']

A custom profile has been found to work between two drupal sites with 
['GET','POST'] enabled.  As users successfully test contribution forms 
and other civicrm entities as they may be enabled by future versions 
of this extension, pull requests are welcome to hone this documentation 
to reflect that experience.  

Similar experimentation may reveal the minimum set of headers required 
for a working form.  Again, please consider offering a pull request 
to enhance this documentation to reflect your experience successfully 
configuring this extension to work in your environment.  

## enable encryted communication between the host and client servers

Note, that both the client site and the server site must be run with encryption on.  
Otherwise the connection will be rejected.  

## learn more about CORS

To learn more about CORS, and the other options in your services.yml file, 
try these links:

  [CORS documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
  [Opt-in CORS support](https://www.drupal.org/node/2715637)

