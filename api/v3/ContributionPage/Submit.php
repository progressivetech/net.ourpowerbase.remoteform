<?php
use CRM_Remoteform_ExtensionUtil as E;

/**
 * ContributionPage.Submit API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */

if (!function_exists('_civicrm_api3_contribution_page_submit_spec')) {
  function _civicrm_api3_contribution_page_submit_spec(&$params, $apirequest) {
    if (isset($apirequest['params']['contribution_page_id'])) {
      $contribution_page_id = $apirequest['params']['contribution_page_id'];
      _add_page_details($contribution_page_id, $params);
      _add_profile_fields($contribution_page_id, $params);
      _add_price_fields($contribution_page_id, $params, $params['control']['is_allow_other_amount'], $params['control']['currency']);
      _add_credit_card_fields($params);     
    }
    $params['contribution_page_id']['api.required'] = TRUE;
    $params['contribution_page_id']['title'] = 'Contribution Page ID';
  }
}

function _add_page_details($id, &$params) {
  $return = array(
      'title',
      'intro_text',
      'thankyou_text',
      'is_active',
      'start_date',
      'currency',
      'min_amount',
      'is_allow_other_amount',
      'payment_processor'
    );
  $cp_params = array(
    'id' => $id,
    'return' => $return,
  );
  $result = civicrm_api3('ContributionPage', 'get', $cp_params);
  // We send three kinds of information out:
  // 1. Fields that should be rendered for input
  // 2. Fields that should be rendered read-only
  // 3. Control information.
  $params['readonly'] = array(
   'title' => $result['values'][$id]['title'],
   'intro_text' => $result['values'][$id]['intro_text'],
   'thankyou_text' => $result['values'][$id]['thankyou_text'],
  );
  $params['control'] = array(
   'is_active' => $result['values'][$id]['is_active'],
   'start_date' => $result['values'][$id]['start_date'],
   'currency' => $result['values'][$id]['currency'],
   'min_amount' => $result['values'][$id]['min_amount'],
   'is_allow_other_amount' => $result['values'][$id]['is_allow_other_amount'],
   'payment_processor' =>  $result['values'][$id]['payment_processor'],
  );
}

function _add_profile_fields($id, &$params) {
  // Now get profile fields.
  $result = civicrm_api3('UFJoin', 'get', array(
    'module' => 'CiviContribute',
    'entity_table' => 'civicrm_contribution_page',
    'entity_id' => $contribution_page_id,
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

function _add_price_fields($id, &$params, $allow_other = 0, $currency = 'USD') {
  $sql = "SELECT fv.id, fv.name, fv.label, fv.help_pre, fv.help_post, fv.amount, 
   fv.is_default, pse.price_set_id, pf.id AS price_field_id FROM 
   civicrm_price_field_value fv JOIN civicrm_price_field pf ON fv.price_field_id =
   pf.id JOIN civicrm_price_set_entity pse ON pse.price_set_id =
   pf.price_set_id WHERE pse.entity_table = 'civicrm_contribution_page' AND
   pse.entity_id = %0";
  $dao = CRM_Core_DAO::executeQuery($sql, array(0 => array($id, 'Integer')));
  $options = array();
  $default_value = NULL;
  $i = 0;
  while($dao->fetch()) {
    if ($dao->name == 'Other_Amount' && $allow_other != 1) {
      continue;
    }
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

function _add_credit_card_fields(&$params) {
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
