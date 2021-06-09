
# Hosting your CiviCRM RemoteForms on a Wordpress site

The CiviCRM installation where you install the RemoteForms extension, 
and in which you collect the data collected by the forms exposed by it 
must also be configured to allow connections from your client site.  

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

Once you have a working interaction between your server site and your client site, 
you can begin to dial back the Headers and Methods allowed to determine what the 
minimum privileges required to make your form work might be.  

Start by limiting interactions to ONLY your intended client site:

	allowedOrigins: ['https://www.YOUR_CLIENT_SITE_DOMAIN.org']

Next try limiting the allowed Methods, deleting or restoring one at a time, 
to determine which are required for successful interactions by your form:

	allowedMethods: ['HEAD','GET','POST','PUT']

Similar experimentation may reveal the minimum set of headers required 
for a working form.  

## enable encryted communication between the host and client servers

Note, that both the client site and the server site must be run with encryption on.  
Otherwise the connection will be rejected.  

## learn more about CORS

To learn more about CORS, and the other options in your services.yml file, 
try these links:

	https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS 

