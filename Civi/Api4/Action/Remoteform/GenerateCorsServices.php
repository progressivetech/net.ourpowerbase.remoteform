<?php

namespace Civi\Api4\Action\Remoteform;
use CRM_Remoteform_ExtensionUtil as E;
use Symfony\Component\Yaml\Yaml;

 /*
  * Generate the Drupal 8+ services.yml file with the proper cors settings
  * based on the values in the database. Be careful not to clobber any
  * existing services.
  *
 */
class GenerateCorsServices extends \Civi\Api4\Generic\AbstractAction {
  /*
   * path
   *
   * The path to save the new services.yml file. If left blank, save to
   * tmp file.
   *
   * @str
   */
  protected $path = NULL;

  public function _run(\Civi\Api4\Generic\Result $result) {

    if (!$this->getPath()) {
      $this->setPath(tempnam('/tmp', 'remoteform-services'));
    }

    $wantCorsConfig = [
      'enabled' => TRUE,
      'allowedOrigins' => explode("\r\n", \Civi::Settings()->get('remoteform_cors_urls', [])),
      'allowMethods' => ['HEAD', 'GET', 'POST', 'PUT'],
      'allowedHeaders' => ['content-type'],
    ];

    $servicesPath = "sites/default/services.yml";
    if (file_exists($servicesPath)) {
      $services = Yaml::parseFile($servicesPath);
    }
    else {
      $services = [];
    }

    $parameters = $services['parameters'] || NULL;
    if (!$parameters) {
      // If we have no parameters key, fill it all and we are done.
      $services['parameters'] = [
        'cors.config' => $corsConfig,
      ];
    }
    else {
      $corsConfig = $services['parameters']['cors.config'] || NULL;
      if (!$corsConfig) {
        // If we have no cors.config key, fill it all and we are done.
        $services['parameters']['cors.config'] = $$corsConfig;
      }
      else {
        // Otherwise, we have to carefully integrate our values into the
        // existing values.
        foreach ($wantCorsConfig as $key => $wantCorsConfigValues) {
          echo "Key: $key\n";
          if ($key == 'enabled') {
            // This is always going to be TRUE, overwrite if necessary.
            $services['parameters']['cors.config']['enable'] = TRUE;
            continue;
          }
          // Otherwise, iterate over our desired values and append if necessary.
          foreach ($wantCorsConfigValues as $wantValue) {
            $existingValue = [];
            if (array_key_exists($key, $services['parameters']['cors.config'])) {
              $existingValue = $services['parameters']['cors.config'][$key];
            }
            if (!in_array($wantValue, $existingValue)) {
              $services['parameters']['cors.config'][$key][] = $wantValue;
            }
          }
        }
      }
    }
    $yaml = Yaml::dump($services, 4);
    file_put_contents($this->getPath(), $yaml);
    $result[] = [ 'file' => $this->getPath() ];

  }
}






?>
