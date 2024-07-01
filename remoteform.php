<?php

require_once 'remoteform.civix.php';
require_once 'remoteform.stripe.php';

use CRM_Remoteform_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function remoteform_civicrm_config(&$config) {
  _remoteform_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function remoteform_civicrm_install() {
  _remoteform_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function remoteform_civicrm_enable() {
  _remoteform_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */

function remoteform_civicrm_navigationMenu(&$menu) {
  _remoteform_civix_insert_navigation_menu($menu, 'Administer/Customize Data and Screens', array(
    'label' => E::ts('Remote Forms'),
    'name' => 'Remote Forms',
    'url' => 'civicrm/admin/remoteform',
    'permission' => 'access CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _remoteform_civix_navigationMenu($menu);
}

/*
 * Implementation of hook_idsException.
 *
 * Ensure we don't get caught in the IDS check.
 */
function remoteform_civicrm_idsException(&$skip) {
  $skip[] = 'civicrm/remoteform';
}

/** 
 * Get displayable code.
 *
 * Return the code that should be displayed so the user can copy and paste it.
 *
 */
function remoteform_get_displayable_code($id, $entity = 'Profile') {
  $query = NULL;
  $absolute = TRUE;
  $fragment = NULL;
  $frontend = TRUE;
  $htmlize = TRUE;
  $js_url = Civi::resources()->getUrl('net.ourpowerbase.remoteform', 'remoteform.js');
  $css_url = Civi::resources()->getUrl('net.ourpowerbase.remoteform', 'remoteform.css');
  $spin_css_url = Civi::resources()->getUrl('net.ourpowerbase.remoteform', 'spin.css');
  $post_url = CRM_Utils_System::url('civicrm/remoteform', $query, $absolute, $fragment, $htmlize, $frontend);
  $base_url = parse_url(CIVICRM_UF_BASEURL, PHP_URL_HOST);

  $extra_js_urls = [];
  $extra_js_params = NULL;
  if ($entity == 'ContributionPage') {
    $type = strtolower(remoteform_get_payment_processor_type($id));

    $extra_js_urls_func = 'remoteform' . $type . '_extra_js_urls';
    $extra_js_params_func = 'remoteform' . $type . '_extra_js_params';

    if (function_exists($extra_js_urls_func)) {
      $extra_js_urls += $extra_js_urls_func($id);
    }
    if (function_exists($extra_js_params_func)) {
      $extra_js_params = $extra_js_params_func($id);
    }
  }

  CRM_Utils_Hook::singleton()->invoke(
    ['id', 'extra_js_urls'],
    $id,
    $extra_js_urls,
    CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject,
    'civicrm_remoteform_extraJsUrls');
  CRM_Utils_Hook::singleton()->invoke(
    ['id', 'extra_js_params'],
    $id,
    $extra_js_params,
    CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject, CRM_Utils_Hook::$_nullObject,
    'civicrm_remoteform_extraJsParams');

  $extra_js_url_tags = '';
  foreach ($extra_js_urls as $extra_js_url) {
    $extra_js_url_tags .= htmlentities('<script src="' . $extra_js_url . '"></script>') . '<br />';
  }
  
  return 
      htmlentities('<!-- The stylesheet link is optional and can be removed ') . '<br />' .
      htmlentities('if you want to style the form yourself, or if you ') . '<br />' .
      htmlentities('already are including a bootstrap css environment. -->') . '<br />'.
      htmlentities('<link rel="stylesheet" property="stylesheet" href="' . $css_url . '">') . '<br />' .
      htmlentities('<link rel="stylesheet" property="stylesheet" href="' . $spin_css_url . '">') . '<br />' .
      htmlentities('<div id="remoteForm"></div>') . '<br />' .
      htmlentities('<script src="' . $js_url . '"></script>') . '<br />' . 
      htmlentities('<!-- Extra JavaScript URLs, if any: ... -->') . '<br />' .
        $extra_js_url_tags .
      htmlentities('<script> var remoteFormConfig = { ') . '<br />' .
      htmlentities(' url: "') . $post_url . '",' . '<br>' .
      htmlentities(' id: ' . $id . ',') . '<br/>' .
      htmlentities(' entity: "' . $entity . '",') . '<br/>' .
      htmlentities(' // Uncomment line below for test mode,') . '<br/>' .
      htmlentities(' // paymentTestMode: true,') . '<br/>' .
      htmlentities(' // Extra JavaScript Params, if any: ...') . '<br />' .
        $extra_js_params .
      htmlentities('};') . '<br />' .
      htmlentities('if (typeof remoteForm !== "function") {') . '<br />' .
      htmlentities('  document.getElementById("remoteForm").innerHTML = "Oh no! I was not able to display the form! Please check your security settings (for example Privacy Badger) and allow remote javascript to be displayed from ' . $base_url . '."') . '<br />' .
      htmlentities('  document.getElementById("remoteForm").style.color = "red";') . '<br />' .
      htmlentities('}') . '<br />' .
      htmlentities('else {') . '<br />' .
      htmlentities('  remoteForm(remoteFormConfig);') . '<br />' .
      htmlentities('}') . '<br />' .
      htmlentities('</script>');
}

/**
 * Add field to enable remote forms for this entity.
 *
 */
function remoteform_add_enable_field($form, $name, $label, $code) {
  $templatePath = realpath(dirname(__FILE__)."/templates");

  // Add the field element in the form
  $form->add('checkbox', 'remoteform_' . $name . '_enable', $label);

  // dynamically insert a template block in the page
  CRM_Core_Region::instance('page-body')->add(array(
    'template' => "{$templatePath}/{$name}.tpl"
  ));
  $id = intval($form->getVar('_id'));

  $form->assign('remoteform_code', $code);
  $enabled = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_' . $name));
  if (is_null($enabled)) {
    $enabled = array();
  }
  $defaults['remoteform_' . $name . '_enable'] = 0;
  if (in_array($id, $enabled)) {
    $defaults['remoteform_' . $name . '_enable'] = 1;
  }
  $form->setDefaults($defaults);
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * Add remoteform options to key forms in CiviCRM Core.
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function remoteform_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_ContributionPage_Settings') {
    // This form is called once as part of the regular page load and again via an ajax snippet.
    // We only want the new fields loaded once - so limit ourselves to the ajax snippet load.
    if (CRM_Utils_Request::retrieve('snippet', 'String', $form) == 'json') {
      // Sanity check: ensure the form only uses on payment processor.
      $id = intval($form->getVar('_id'));
      $results = \Civi\Api4\ContributionPage::get()
        ->addSelect('payment_processor')
        ->addWhere('id', '=', $id)
        ->execute()->first();
      if (count($results['payment_processor']) > 1 ) {
        $code = htmlentities('<!-- WARNING, RemoteForm only works with one payment processor configured. Please switch to just one processor if you want to enable RemoteForm -->');
        $message = E::ts('Please change to just one Payment Processor before enalbing Remote Form.');
      }
      else {
        $code = remoteform_get_displayable_code($id, 'ContributionPage');
        $message = E::ts('Allow remote submissions to this contribution page.');
      }
      remoteform_add_enable_field($form, 'contribution_page', $message, $code);
    }
  }
  else if ($formName == 'CRM_UF_Form_Group') {
    $id = intval($form->getVar('_id'));
    $code = remoteform_get_displayable_code($id, 'Profile');
    remoteform_add_enable_field($form, 'profile', E::ts('Allow remote submissions to this profile.'), $code);
  }
}

/**
 * Save remoteform enabled settings.
 *
 */
function remoteform_save_enabled_settings($form, $name) {
  $vals = $form->_submitValues;
  $id = intval($form->getVar('_id'));
  $enable = array_key_exists('remoteform_' . $name . '_enable', $vals) ? TRUE : FALSE;

  // Handle Default setting.
  $enabled = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_' . $name));
  if (is_null($enabled)) {
    $enabled = array();
  }
  if ($enable) {
    if (!in_array($id, $enabled)) {
      // Update
      $enabled[] = $id;
      civicrm_api3('Setting', 'create', array('remoteform_enabled_' . $name => $enabled));
    }
  }
  else {
    if (in_array($id, $enabled)) {
      $key = array_search($id, $enabled);
      unset($enabled[$key]);
      civicrm_api3('Setting', 'create', array('remoteform_enabled_' . $name => $enabled));
    }
  }
}

/**
 * Implements hook__civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess/
 */
function remoteform_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_UF_Form_Group') {
    remoteform_save_enabled_settings($form, 'profile');

  }
  elseif ($formName == 'CRM_Contribute_Form_ContributionPage_Settings') {
    remoteform_save_enabled_settings($form, 'contribution_page');

  }
}

/**
 * Get name of payment processor type for contribution id.
 *
 */
function remoteform_get_payment_processor_type($id) {
  $details = remoteform_get_contribution_page_details($id);
  $payment_processor_id = $details['payment_processor'];

  if ($payment_processor_id) {
    $sql = "SELECT ppt.name FROM civicrm_payment_processor_type ppt JOIN
      civicrm_payment_processor pp ON pp.payment_processor_type_id = ppt.id
      WHERE pp.id = %0";
    $dao = CRM_Core_DAO::executeQuery($sql, array(0 => array($payment_processor_id, 'Integer')));
    $dao->fetch();
    if (isset($dao->name)) {
      return $dao->name;
    }
  }
  return NULL;
}

/**
 * Get contribution page details.
 *
 * Return details about the contribution page.
 */
function remoteform_get_contribution_page_details($id) {
  $return = array(
      'title',
      'intro_text',
      'thankyou_text',
      'is_active',
      'start_date',
      'currency',
      'min_amount',
      'payment_processor'
   );
  $cp_params = array(
    'id' => $id,
    'return' => $return,
  );
  $result = civicrm_api3('ContributionPage', 'get', $cp_params);
  return $result['values'][$id];

}
