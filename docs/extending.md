# Remoteform: Extending
## Hooks

Remoteform makes the following hooks available, to allow other extensions to alter
some of its native behaviors.

### hook_civicrm_remoteform_extraJsParams
Alter the "extra JavaScript parameters" that Remoteform includes in the copy/paste 
embedded HTML code for each form.

#### Definition
```
hook_civicrm_remoteform_extraJsParams($id, &$params);
```
#### Parameters
* Int $id - system ID of the Contribution Page or Event entity
* String $params - reference to the string of "extra JavaScript parameters" that  will be embedded in the code.


#### Returns
Void.

#### Example
```
function example_civicrm_remoteform_extraJsParams($id, &$params) {
  $params .= htmlentities("createFieldDivFunc: exampleCreateFieldDiv,") . '<br />';
}
```


# hook_civicrm_remoteform_extraJsUrls
Alter the array of "extra JavaScript files URLs" that Remoteform includes in the copy/paste 
embedded HTML code for each form.

#### Definition
```
hook_civicrm_remoteform_extraJsUrls($id, &$urls);
```
#### Parameters
* Int $id - system ID of the Contribution Page or Event entity
* Array $urls - reference to the array of "extra JavaScript files URLs" that Remoteform will include.


#### Returns
Void.

#### Example
```
function example_civicrm_remoteform_extraJsUrls($id, &$urls) {
  $urls[] = CRM_Core_Resources::singleton()->getUrl('example', 'exampleRemoteformExtra.js');
}
```