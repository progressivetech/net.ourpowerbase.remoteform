<?php

/**
 * Stripe specific functions. Use this file as a model for creating an
 * extension for other token-based payment processors.
 */


// Whether or not credit card fields should be included in the form
// sent back to the browser. With Stripe, we draw our widget that
// asks for the user to enter their credit card info, so we don't
// want to include those fields in the CiviCRM generated form.
function remoteformstripe_include_cc_fields_in_form() {
  return FALSE;
}

function remoteformstripe_extra_js_urls($id) {
  $js_url = Civi::resources()->getUrl('net.ourpowerbase.remoteform', 'remoteform.stripe.js');
  return htmlentities('<script src="' . $js_url . '"></script>') . '<br />' . 
    htmlentities('<script src="https://checkout.stripe.com/checkout.js"></script>') . '<br />';

}

function remoteformstripe_extra_js_params($id) {
  $details = remoteform_get_contribution_page_details($id);
  $live_id = $details['payment_processor'];
  $test_id = $live_id + 1; // Is this right??

  $live_key = remoteformstripe_get_public_key($live_id);
  $test_key = remoteformstripe_get_public_key($test_id);

  return htmlentities(' customSubmitDataFunc: initStripe,') . '<br />' .
    htmlentities(' customSubmitDataParams: {') . '<br />' .
    htmlentities(' "apiKey": "' . $live_key . '",') . '<br />' .
    htmlentities(' // uncomment for testing: "apiKey": "' . $test_key . '",') . '<br />' .
    htmlentities(' },') . '<br />';
}

function remoteformstripe_get_public_key($ppid) {
  return civicrm_api3('PaymentProcessor', 'getvalue', array(
    'id' => $ppid,
    'return' => 'password'
  ));
}
