
# Hosting your CiviCRM RemoteForms on a Wordpress site

The CiviCRM installation where you install the RemoteForms extension, 
and in which you collect the data collected by the forms exposed by it 
must also be configured to allow connections from the client website.  

## enabling CORS on a Wordpress site

Developers with more experience with Wordpress are encouraged to 
form this project at:

  * [Fork the project and edit this page](https://github.com/progressivetech/net.ourpowerbase.remoteform)

In your intial tests, try setting:

	enabled: true
	allowedOrigins: ['*']
	allowedMethods: ['*']
	allowedHeaders: ['*']

## tightening security

Once you have a working interaction between your (server) civicrm installation and 
your (client) website, you can begin to dial back the Headers and Methods allowed 
settings to determine what the minimum privileges required to make your form work 
might be.  

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

