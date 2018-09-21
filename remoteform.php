<?php

require_once 'remoteform.civix.php';
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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function remoteform_civicrm_xmlMenu(&$files) {
  _remoteform_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function remoteform_civicrm_postInstall() {
  _remoteform_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function remoteform_civicrm_uninstall() {
  _remoteform_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function remoteform_civicrm_enable() {
  _remoteform_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function remoteform_civicrm_disable() {
  _remoteform_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function remoteform_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _remoteform_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function remoteform_civicrm_managed(&$entities) {
  _remoteform_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function remoteform_civicrm_caseTypes(&$caseTypes) {
  _remoteform_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function remoteform_civicrm_angularModules(&$angularModules) {
  _remoteform_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function remoteform_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _remoteform_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function remoteform_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function remoteform_civicrm_navigationMenu(&$menu) {
  _remoteform_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _remoteform_civix_navigationMenu($menu);
} // */

/*
 * Implementation of hook_idsException.
 *
 * Ensure we don't get caught in the IDS check.
 */
function remoteform_civicrm_idsException(&$skip) {
  $skip[] = 'civicrm/remoteform';
}

// Ensure our overridden API is included until we (hopefully) get it included
// upstream.
if (!function_exists('_civicrm_api3_contribution_page_submit_spec')) {
  require_once('api/v3/ContributionPage/Submit.php');
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
  if ($formName == 'CRM_UF_Form_Group') {
     // Assumes templates are in a templates folder relative to this file
    $templatePath = realpath(dirname(__FILE__)."/templates");
    // Add the field element in the form
    $form->add('checkbox', 'remoteform_profile_enable', ts('Allow remote submissions to this profile'));
    // dynamically insert a template block in the page
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "{$templatePath}/profile.tpl"
    ));
    $profile_id = intval($form->getVar('_id'));
    $query = NULL;
    $absolute = TRUE;
    $post_url = CRM_Utils_System::url('civicrm/remoteform', $query, $absolute);

    $js_url = Civi::resources()->getUrl('net.ourpowerbase.remoteform', 'remoteform.js');

    $code = htmlentities('<script src="' . $js_url . '"></script>') . '<br />' . 
      htmlentities('<script> var config = { ') . '<br />' .
      htmlentities(' url: "' . $post_url . '",') . '<br>' .
      htmlentities(' id: ' . $profile_id . ',') . '<br/>' .
      htmlentities(' autoInit: false,') . '<br />' .
      htmlentities(' displayLabels: false') .  '<br />' .
      htmlentities('};');

    $form->assign('remoteform_profile_code', $code);
    $enabled_profiles = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_profiles'));
    if (is_null($enabled_profiles)) {
      $enabled_profiles = array();
    }
    $profile_id = intval($form->getVar('_id'));
    $defaults['remoteform_profile_enable'] = 0;
    if (in_array($profile_id, $enabled_profiles)) {
      $defaults['remoteform_profile_enable'] = 1;
    }
    $form->setDefaults($defaults);

  }
}

/**
 * Implements hook__civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess/
 */
function remoteform_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_UF_Form_Group') {
    $vals = $form->_submitValues;
    $profile_id = intval($form->getVar('_id'));
    $remoteform_profile_enable = array_key_exists('remoteform_profile_enable', $vals) ? TRUE : FALSE;

    // Handle Default setting.
    $enabled_profiles = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_profiles'));
    if (is_null($enabled_profiles)) {
      $enabled_profiles = array();
    }
    if ($remoteform_profile_enable) {
      if (!in_array($profile_id, $enabled_profiles)) {
        // Update
        $enabled_profiles[] = $profile_id;
        civicrm_api3('Setting', 'create', array('remoteform_enabled_profiles' => $enabled_profiles));
      }
    }
    else {
      if (in_array($profile_id, $enabled_profiles)) {
        $key = array_search($profile_id, $enabled_profiles);
        unset($enabled_profiles[$key]);
        civicrm_api3('Setting', 'create', array('remoteform_enabled_profiles' => $enabled_profiles));
      }
    }
  }
}
