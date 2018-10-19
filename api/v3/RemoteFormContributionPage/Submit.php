<?php
use CRM_Remoteform_ExtensionUtil as E;

/**
 * RemoteFormContributionPage.Submit API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_remote_form_contribution_page_Submit_spec(&$params, $apirequest) {
  if (isset($apirequest['params']['contribution_page_id'])) {
    $contribution_page_id = $apirequest['params']['contribution_page_id'];
    _rf_add_page_details($contribution_page_id, $params);
    _rf_add_profile_fields($contribution_page_id, $params);
    _rf_add_price_fields($contribution_page_id, $params, $params['control']['currency']);
    // Omit credit card fields for Stripe (and any other javascript based payment processor).
    if (_rf_include_credit_card_fields($contribution_page_id)) {
      _rf_add_credit_card_fields($params);     
    }
  }
  $params['contribution_page_id']['api.required'] = TRUE;
  $params['contribution_page_id']['title'] = 'Contribution Page ID';
}

/**
 * Check if we should include the cc fields in our form. 
 *
 * If the contribution page uses a javascript based payment processor, in
 * other words, one that submits the credit card directly to the payment
 * processor via their own javascript, then we may not want to send our own
 * credit card fields. If you want remoteform to work with such a payment
 * processor, just create an extension which defines the function:
 * remoteformNAME_include_cc_fields_in_form() and have the function
 * return FALSE. Replace NAME with the type of payment processor.
 */
function _rf_include_credit_card_fields($id) {
  $type = strtolower(remoteform_get_payment_processor_type($id));

  $func = 'remoteform' . $type . '_include_cc_fields_in_form';
  CRM_Core_Error::debug_log_message("Trying: $func");
  if (function_exists($func)) {
    CRM_Core_Error::debug_log_message("found: $func");
    return $func();
  }
  return TRUE;
}

function _rf_add_page_details($id, &$params) {
  $values = remoteform_get_contribution_page_details($id);

  // We send three kinds of information out:
  // 1. Fields that should be rendered for input
  // 2. Fields that should be rendered read-only
  // 3. Control information.
  $params['readonly'] = array(
   'title' => $values['title'],
   'intro_text' => $values['intro_text'],
   'thankyou_text' => $values['thankyou_text'],
  );
  $params['control'] = array(
   'is_active' => $values['is_active'],
   'start_date' => $values['start_date'],
   'currency' => $values['currency'],
   'min_amount' => $values['min_amount'],
   'payment_processor' =>  $values['payment_processor'],
  );
}

function _rf_add_profile_fields($id, &$params) {
  // Now get profile fields.
  $result = civicrm_api3('UFJoin', 'get', array(
    'module' => 'CiviContribute',
    'entity_table' => 'civicrm_contribution_page',
    'entity_id' => $id,
    'return' => array('uf_group_id')
  ));
  foreach($result['values'] as $value) {
    $uf_group_id = $value['uf_group_id'];
    $uf_result = civicrm_api3('Profile', 'getfields', array(
      'api_action' => 'submit',
      'profile_id' => $uf_group_id,
      'get_options' => 'all'
    ));
    foreach($uf_result['values'] as $field_name => $field) {
      $params[$field_name] = $field;
    }
  }
}

function _rf_add_price_fields($id, &$params, $currency = 'USD') {
  $sql = "SELECT fv.id, fv.name, fv.label, fv.help_pre, fv.help_post, fv.amount, 
   fv.is_default, pse.price_set_id, pf.id AS price_field_id
   FROM civicrm_price_field_value fv
     JOIN civicrm_price_field pf ON fv.price_field_id = pf.id
     JOIN civicrm_price_set ps ON ps.id = pf.price_set_id
     JOIN civicrm_price_set_entity pse ON pse.price_set_id = pf.price_set_id
   WHERE pse.entity_table = 'civicrm_contribution_page' AND pse.entity_id = %0
     AND fv.is_active = 1 AND pf.is_active = 1 AND ps.is_active = 1";
  $dao = CRM_Core_DAO::executeQuery($sql, array(0 => array($id, 'Integer')));
  $options = array();
  $default_value = NULL;
  $i = 0;
  while($dao->fetch()) {
    $options[$dao->id] = array(
      'amount' => $dao->amount,
      'label' => $dao->label,
      'name' => $dao->name,
      'currency' => $currency
     );
    if ($dao->is_default) {
      $default_value = $dao->id;
    }
  }
  $key = 'price_' . $dao->price_field_id;
  $params[$key] = array(
    'title' => 'Choose Amount',
    'default_value' => $default_value,
    'entity' => 'contribution',
    'options' => $options,
    'html' => array(
      'type' => 'Radio'
    ),
  );
  $params['price_set_id'] = array(
    'title' => ts("Price Set ID"),
    'default_value' => $dao->price_set_id,
    'entity' => 'contribution',
    'html' => array('type' => 'hidden'),
  );
  $params['payment_instrument_id'] = array(
    'title' => ts("Payment Instrument ID"),
    'default_value' =>  CRM_Core_OptionGroup::getValue('payment_instrument', 'Credit Card', 'name'),
    'entity' => 'contribution',
    'html' => array('type' => 'hidden'),
  );
}

function _rf_add_credit_card_fields(&$params) {
  $params['credit_card_number'] = array(
    'title' => 'Credit Card',
    'default_value' => '',
    'entity' => 'contribution',
    'api.required' => 1,
    'html' => array(
      'type' => 'Text'
    ),
  );

  $params['cvv2'] = array(
    'title' => 'CVV',
    'default_value' => '',
    'entity' => 'contribution',
    'api.required' => 1,
    'html' => array(
      'type' => 'Text'
    ),
  );

  $params['credit_card_exp_date_M'] = array(
    'title' => 'Exp Month',
    'default_value' => '',
    'entity' => 'contribution',
    'api.required' => 1,
    'html' => array(
      'type' => 'select'
    ),
    'options' => array(
      '1' => '01 - ' . ts('Jan'),
      '2' => '02 - ' . ts('Feb'),
      '3' => '03 - ' . ts('Mar'),
      '4' => '04 - ' . ts('Apr'),
      '5' => '05 - ' . ts('May'),
      '6' => '06 - ' . ts('Jun'),
      '7' => '07 - ' . ts('Jul'),
      '8' => '08 - ' . ts('Aug'),
      '9' => '09 - ' . ts('Sep'),
      '10' => '10 - ' . ts('Oct'),
      '11' => '11 - ' . ts('Nov'),
      '12' => '12 - ' . ts('Dec'),
    ),
  );
  $params['credit_card_exp_date_Y'] = array(
    'title' => 'Exp Year',
    'default_value' => '',
    'entity' => 'contribution',
    'api.required' => 1,
    'html' => array(
      'type' => 'select'
    ),
    'options' => array(),
  );

  $start_year = date('Y');
  $end_year = $start_year + 30;
  $year = $start_year;
  while($year < $end_year) {
    $params['credit_card_exp_date_Y']['options'][$year] = $year;
    $year++;
  }
}
/**
 * RemoteFormContributionPage.Submit API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_remote_form_contribution_page_Submit($params) {
  // We translate a few fields to ensure compatability with all payment
  // processors.
  $params['id'] = $params['contribution_page_id'];
  $params['month'] = CRM_Core_Payment_Form::getCreditCardExpirationMonth($params);
  $params['year'] = CRM_Core_Payment_Form::getCreditCardExpirationYear($params);

  // Now, submit.
  $result = CRM_Contribute_Form_Contribution_RemoteformConfirm::submit($params);

  // First check for payment failure.
  if ($result['is_payment_failure']) {
    $msg = $result['error']->getMessage();
    return civicrm_api3_create_error($msg);
  }

  // $result gives us a contribution object, which will cause 
  // civicrm_api3_create_success to fail. We have to convert it to
  // an array and remove some of the db object garbage that we don't need.
  $contribution = (Array)$result['contribution'];
  $result['contribution'] = array();
  foreach($contribution as $key => $value) {
    $first_character = substr($key, 0, 1);
    if ($first_character == '_' || $key == 'N' ) {
      continue;
    }
    $result['contribution'][$key] = $value;
  }
  return civicrm_api3_create_success(array($result), $params, 'RemoteFormContributionPage', 'submit');
}

