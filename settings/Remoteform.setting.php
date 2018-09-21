<?php

/**
 * Settings used by remoteform.
 */

return array(
  'remoteform_cors_urls' => array(
    'group_name' => 'Remote Form',
    'group' => 'remoteform',
    'name' => 'remoteform_cors_urls',
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'textarea',
    'html_attributes' => array('rows' => 5, 'cols' => 50),
    'default' => array(),
    'add' => '5.3',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => "Allow forms to be submitted from the following locations. Please list full URL (including https://), one per line",
    'description' => 'Remote URLs that are allowed to submit data.',
    'help_text' => 'List the URLs of web sites that are allowed to submit data to CiviCRM via the Remote Form extension',
	),
  'remoteform_enabled_profiles' => array(
    'group_name' => 'Remote Form Enabled Entities',
    'group' => 'remoteform_enabled_entities',
    'name' => 'remoteform_enabled_profiles',
    'type' => 'Array',
    'default' => array(),
    'add' => '5.3',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => "An array of profile ids that are allowed to accept remote form submissions",
	)
);
