<?php
use CRM_Remoteform_ExtensionUtil as E;

/**
 * RemoteForm.Countyforstateprovince API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_remote_form_Countiesforstateprovince_spec(&$params) {
  $params['county_id']['title'] = 'State Province ID';
}

function civicrm_api3_remote_form_Countiesforstateprovince(&$params) {
  $state_province_id = NULL;
  $values = array();
  if (array_key_exists('state_province_id', $params)) {
    $state_province_id = $params['state_province_id'];
  }
  if (empty($state_province_id)) {
    $state_province_id = Civi::settings()->get('defaultContactStateProvince');
    if (empty($state_province_id)) {
      // Rather then return all counties in the world, return nothing if no defaults.
      return civicrm_api3_create_success([]);
    }
  }
  $values = CRM_Core_PseudoConstant::countyForState($state_province_id);
  return civicrm_api3_create_success($values);
}

