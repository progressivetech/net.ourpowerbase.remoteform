<?php
use CRM_Remoteform_ExtensionUtil as E;

function civicrm_api3_remote_form_Countries(&$params) {
  $values = CRM_Core_PseudoConstant::country($country_id);
  return civicrm_api3_create_success($values);
}

