<?php
use CRM_Remoteform_ExtensionUtil as E;

/**
 * RemoteForm.StateProvincesForCountry API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_remote_form_Stateprovincesforcountry_spec(&$params) {
  $params['country_id']['title'] = 'Country ID';
}

function civicrm_api3_remote_form_Stateprovincesforcountry(&$params) {
  $country_id = NULL;
  $values = array();
  if (array_key_exists('country_id', $params)) {
    $country_id = $params['country_id'];
  }

  if (empty($country_id)) {
    $country_id = Civi::settings()->get('defaultContactCountry');
  }
  $values = CRM_Core_PseudoConstant::stateProvinceForCountry($country_id);
  return civicrm_api3_create_success($values);
}

