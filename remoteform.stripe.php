<?php

/**
 * Stripe specific functions. Use this file as a model for creating an
 * extension for other token-based payment processors.
 */


// Whether or not credit card fields should be included in the form
// sent back to the browser. With STRIPE we no longer use the widget
// so we needs these fields displayed. Return TRUE to include the 
// standard CiviCRM cc fields, FALSE to include nothing, or a string
// to include what is returned.
function remoteformstripe_include_cc_fields_in_form() {
  return [
    'placeholder_stripe_cc_field' => [
      'title' => 'Credit Card',
      'entity' => 'contribution',
      'html' => [
        'type' => 'text'
      ]
    ]
  ];

}

function remoteformstripe_extra_js_urls() {
  $js_url = Civi::resources()->getUrl('net.ourpowerbase.remoteform', 'remoteform.stripe.js');
  return htmlentities('<script src="' . $js_url . '"></script>') . '<br />' . 
    htmlentities('<script src="https://js.stripe.com/v3/"></script>') . '<br />';

}

function remoteformstripe_extra_js_params($id) {
  $details = remoteform_get_contribution_page_details($id);
  $live_id = $details['payment_processor'];
  $test_id = $live_id + 1; // Is this right??

  $live_key = remoteformstripe_get_public_key($live_id);
  $test_key = remoteformstripe_get_public_key($test_id);

  return htmlentities(' customInitFunc: initStripe,') . '<br />' .
    htmlentities(' customSubmitDataFunc: submitStripe,') . '<br />' .
    htmlentities(' customSubmitDataParams: {') . '<br />' .
    htmlentities(' apiKey: "' . $live_key . '",') . '<br />' .
    htmlentities(' // uncomment for testing: apiKey: "' . $test_key . '",') . '<br />' .
    htmlentities(' },') . '<br />';
}

function remoteformstripe_get_public_key($ppid) {
  return CRM_Core_Payment_Stripe::getPublicKeyById($ppid);
}
