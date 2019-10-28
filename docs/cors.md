# Allowing your web site to submit request to your CiviCRM database 

The remoteform extension submits requests from your web site (e.g.
www.mysite.org) to your CiviCRM database running on a different web site (e.g.
database.mysite.org).

By default, your CiviCRM database site will reject those requests.

This restriction is due to a mechanism called
[CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing).

In order to allow these requests, you will need to configure you CiviCRM site
to emit special headers designating your web site as a legitimate site for this
behavior. 

See below for how to do this via Drupal 7, Drupal 8, WordPress and manually.

In each case, you have to enter the full URL of your web site to indicate that
your site may make these cors requests.

## Drupal 7

If you are using Drupal 7, install the [cors
module](https://www.drupal.org/project/cors) and add your web site URLs on the
configuration page:

![](images/drupal7-cors.png)

## Drupal 8

Drupal 8 has built-in cors support. However, there is no graphical interface.
Instead you have to make changes to the `sservices.yaml` file. It appears to be
a bit tricky, however, something like this should work:

    cors.config:
    enabled: true
    # Specify allowed headers, like 'x-allowed-header'.
    allowedHeaders: ['content-type', 'authorization']
    # Specify allowed request methods, specify ['*'] to allow all possible ones.
    allowedMethods: ['GET', 'POST']
    # Configure requests allowed from specific origins.
    allowedOrigins: ['http://WWW.CHANGE-THIS-TO-YOUR-ACTUAL-WEBSITE.ORG']
    # Sets the Access-Control-Expose-Headers header.
    exposedHeaders: false
    # Sets the Access-Control-Max-Age header.
    maxAge: false
    # Sets the Access-Control-Allow-Credentials header.
    supportsCredentials: false

See the [Drupal cors issue](https://www.drupal.org/node/2715637) for more details.

## WordPress

WordPress also has a [cors plugins](https://wordpress.org/plugins/wp-cors/). 

However, support seems spotty. Please post tickets to report how well this works (or doesn't work).

## Manually

To manually emit headers, you can copy and paste the following code in your settings file (settings.php for Drupal, wp-config.php for WordPress, configuration.php for Joomla):

    if (isset($_SERVER['HTTP_ORIGIN'])) {
      $urls = [ "https://CHANGE-THIS-TO-YOUR-ACTUAL-WEBSITE.ORG" ]; 
      foreach($urls as $url) {
        if ($_SERVER['HTTP_ORIGIN'] == $url) {
          header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
          header('Access-Control-Allow-Credentials: true');
          header('Access-Control-Max-Age: 86400');    // cache for 1 day
          continue;
        }
      }
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
      }
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
      }
      CRM_Utils_System::civiExit();
    }
