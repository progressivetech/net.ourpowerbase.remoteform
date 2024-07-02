
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

You can automatically add the required settings by using the Api4
call Remoteform.GenerateCorsServices via the command:

```
cv --user=admin api4 Remoteform.GenerateCorsServices path=/path/to/sites/default/services.yml
```

If you omit the path argument, the settings will be saved in a temp file which
will be provided as output to the cv command.

This API call respects existing settings in your `services.yml` file so you can
safely run it even if your services.yml file is populated.

Alternatively, you can modify the file by hand, ensuring it has the following
values:

```
  parameters:
    cors.config:
      enabled: true
      allowedOrigins: 
        - 'https://www.YOUR_WEBSITE_DOMAIN.org'
        - 'https://www.ANOTHER_WEBSITE_DOMAIN.org'
      allowedMethods: ['HEAD','GET','POST','PUT']
      allowedHeaders: ["content-type"]
```

## enable encryted communication between the host and client servers

Note, that both the client site and the server site must be run with encryption
on (e.g. https). 

## learn more about CORS

To learn more about CORS, and the other options in your services.yml file, 
try these links:

  [CORS documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
  [Opt-in CORS support](https://www.drupal.org/node/2715637)

