<?php
use CRM_Remoteform_ExtensionUtil as E;

require_once('api/Exception.php');
require_once('api/v3/utils.php');
class CRM_Remoteform_Page_RemoteForm extends CRM_Core_Page {

  public function run() {
    $this->printCorsHeaders();
		$data = json_decode(stripslashes(file_get_contents("php://input")));
    if (empty($data)) {
      $this->exitError(E::ts("No data was received."));
    }
    try {
      $data = $this->sanitizeInput($data);
    }
    catch (Exception $e) {
      $this->exitError($e->getMessage());
    }

    try {
      // CRM_Core_Error::debug_var('data', $data);
      // Special exception to check for dupes.
      if (strtolower($data['entity']) == 'stripepaymentintent' && strtolower($data['action']) == 'processpublic') {
        // Special exceptioin - we need to run an api4 call, not an api3 call.
        $result = \Civi\Api4\StripePaymentintent::processPublic(TRUE)
          ->setPaymentMethodID($data['params']['payment_method_id'])
          ->setAmount(strval($data['params']['amount']))
          ->setCurrency($data['params']['currency'])
          ->setPaymentProcessorID($data['params']['id'])
          ->setIntentID(NULL)
          ->setDescription($data['params']['description'])
          ->setCsrfToken($data['params']['csrfToken'])
          ->execute();
        $this->exitSuccess($result);
      }
      else {
        if (strtolower($data['entity']) == 'profile' && strtolower($data['action']) == 'submit') {
          $checkPerms = FALSE;
          $excludedContactIds = [];
          // issue#13 Workaround because the deduping is case-sensitive
          if (!empty($data['params']['email-primary'])) {
            $data['params']['email-Primary'] = $data['params']['email-primary'];
          }
          $dupes = CRM_Contact_BAO_Contact::getDuplicateContacts($data['params'], 'Individual', 'Unsupervised', $excludedContactIds, $checkPerms);
          $num = count($dupes);
          if ($num > 0) {
            // We have 1 or more dupes. We better do something.
            // First, let's see what the policy is for this profile.
            // 0 means issue warning and do not update, 1 means update the dupe, 2 means create dupe.
            $is_update_dupe = civicrm_api3('UFGroup', 'getvalue', [ 'return' => "is_update_dupe", 'id' => $data['params']['profile_id'] ]);

            if ($is_update_dupe == '0') {
              throw new CiviCRM_API3_Exception(E::ts("You are already in the database! Congrats."));
            }
            elseif ($is_update_dupe == 1) {
              if ($num == 1) {
                // Just one. Ok, this must be the same contact. Update our params
                // to include the dupe contact id and we should be good.
                $data['params']['contact_id'] = array_pop($dupes);
              }
              else {
                // More than one dupe. Now what? In keeping with what happens when
                // you fill out a profile, we simply pick off the first one.
                $data['params']['contact_id'] = array_shift($dupes);
              }
            }
            elseif ($is_update_dupe == 2) {
              // No op. We don't have to do anything to create the dupe.
            }
            else {
              // Error
              throw new CiviCRM_API3_Exception(E::ts("Your profile has a mis-configured setting for duplicate handling."));
            }
          }
        }
        $result = civicrm_api3($data['entity'], $data['action'], $data['params'] ); 
        // Special exception - The API profile submit function doesn't add
        // contacts to a group or send email notification, even if the profile
        // specifies that it should.
        // See: https://lab.civicrm.org/dev/core/issues/581
        if (strtolower($data['entity']) == 'profile' && strtolower($data['action']) == 'submit') {
          $uf_group_id = $data['params']['profile_id'];
          $contact_id = $result['id'];
          $this->profilePostSubmit($uf_group_id, $contact_id);
        }
        // More exceptions... we never return values on submit to avoid leaks.
        if (strtolower($data['action']) == 'submit') {
          $result['values'] = [];
        }
        $this->exitSuccess($result['values']);
      }
    }
    catch (Exception $e) {
      $this->exitError($e->getMessage());
    }
  }

  function exitError($data) {
    CRM_Utils_JSON::output(civicrm_api3_create_error($data));
  }

  function exitSuccess($data) {
    CRM_Utils_JSON::output(civicrm_api3_create_success($data));
  }

  function printCorsHeaders() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      // CRM_Core_Error::debug_var('_SERVER', $_SERVER);
      $urls = explode("\n", civicrm_api3('setting', 'getvalue', array('name' => 'remoteform_cors_urls')));
      foreach($urls as $url) {
        // Who knows what kind of spaces and line return nonesense we may have.
        // This regex should kill all the Control Characters (see
        // https://en.wikipedia.org/wiki/Control_character
        $url = preg_replace('/[\x00-\x1F\x7F]/', '', trim($url));
        if ($_SERVER['HTTP_ORIGIN'] == $url) {
          header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
          header('Access-Control-Allow-Credentials: true');
          header('Access-Control-Max-Age: 86400');    // cache for 1 day
          continue;
        }
      }
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
      }
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
      }
      CRM_Utils_System::civiExit();
    }
  }

  /**
   * Take user input object and return a safe array. 
   **/
  function sanitizeInput($input) {
    // Ensure the user is not logged in. If we allowed logged in users
    // then we are at risk of a CSRF attack.
    if (CRM_Utils_System::isUserLoggedIn()) {
      throw new CiviCRM_API3_Exception(E::ts('You cannot use JSSubmit while logged into CiviCRM.'));
    }

    $entity = $input->entity;
    $input_params = get_object_vars($input->params);
    $session_id = $input_params['session_id'] ?? NULL;
    if ($session_id) {
      session_id($session_id);
    }
    $action = $input->action;
    if ($entity == 'Profile') {
      // Ensure this site allows access to profiles.
      if (!CRM_Core_Permission::check('profile create')) {
        throw new CiviCRM_API3_Exception(E::ts("You don't have permission to create contacts via profiles."));
      }

      // Let's see if this particular profile is allowed.
      $input_params = get_object_vars($input->params);
      $id = intval($input_params['profile_id']);
      $enabled = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_profile'));
      if (!in_array($id, $enabled)) {
        throw new CiviCRM_API3_Exception(E::ts("This profile is not configured to accept remote form submissions."));
      }

      if ($action == 'getfields') {
        // Sanitize input parameters.
        $api_action = $input_params['api_action'] == 'submit' ? 'submit' : NULL;
        $get_options = $input_params['get_options'] == 'all' ? 'all' : NULL;
        $params = array(
          'profile_id' => $id,
          'api_action' => $api_action,
          'get_options' => $get_options 
        );
        return array(
          'entity' => 'Profile',
          'action' => 'getfields',
          'params' => $params
        );
      }
      if ($action == 'submit') {
        // Avoid updates by ensuring no contact_id is specified.
        unset($input_params['contact_id']);
        return array(
          'entity' => 'Profile',
          'action' => 'submit',
          'params' => $input_params
        );
      }
      else {
        throw new CiviCRM_API3_Exception(E::ts("That action is not allowed."));
      }
    }
    else if ($entity == 'RemoteFormContributionPage') {
      // Ensure this site allows access to contributions.
      if (!CRM_Core_Permission::check('make online contributions')) {
        throw new CiviCRM_API3_Exception(E::ts("You don't have permission to create contributions."));
      }

      // Make sure this contribution page is configured to accept remote submissions.
      $id = intval($input_params['contribution_page_id']);
      $enabled = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_contribution_page'));
      if (!in_array($id, $enabled)) {
        throw new CiviCRM_API3_Exception(E::ts("This contribution page is not configured to accept remote form submissions."));
      }
      if ($action == 'getfields') {
        // Sanitize input parameters.
        $api_action = $input_params['api_action'] == 'submit' ? 'submit' : NULL;
        $get_options = $input_params['get_options'] == 'all' ? 'all' : NULL;
        $test_mode = $input_params['test_mode'] == '1' ? '1' : NULL;
        $params = array(
          'contribution_page_id' => intval($input_params['contribution_page_id']),
          'api_action' => $api_action,
          'get_options' => $get_options,
          'test_mode' => $test_mode
        );
        
        return array(
          'entity' => 'RemoteFormContributionPage',
          'action' => 'getfields',
          'params' => $params
        );
      }
      if ($action == 'submit') {
        $input_params['id'] = $input_params['contribution_page_id'];
        if (array_key_exists('credit_card_exp_date', $input_params)) {
          $input_params['credit_card_exp_date'] = (Array)$input_params['credit_card_exp_date'];
        }
        return array(
          'entity' => 'RemoteFormContributionPage',
          'action' => 'submit',
          'params' => $input_params
        );
      }
      else {
        throw new CiviCRM_API3_Exception(E::ts("That action is not allowed."));
      }
    }
    else if ($entity == 'RemoteForm') {
      if ($action == 'Stateprovincesforcountry') {
        $params['country_id'] = isset($input_params['country_id']) ? intval($input_params['country_id']) : NULL;
        return array(
          'entity' => 'RemoteForm',
          'action' => 'Stateprovincesforcountry',
          'params' => $params,
        );
      }
      if ($action == 'Countiesforstateprovince') {
        $params['state_province_id'] = isset($input_params['state_province_id']) ? intval($input_params['state_province_id']) : NULL;
        return array(
          'entity' => 'RemoteForm',
          'action' => 'Countiesforstateprovince',
          'params' => $params,
        );
      }
      if ($action == 'Countries') {
        return array(
          'entity' => 'RemoteForm',
          'action' => 'Countries',
          'params' => array(),
        );
      }
      else {
        throw new CiviCRM_API3_Exception(E::ts("That action is not allowed."));
      }
    }
    else if ($entity == 'StripePaymentintent') {
      if ($action != 'processPublic') {
        throw new CiviCRM_API3_Exception(E::ts("That action is not allowed."));
      }
      $params = array(
        'payment_method_id' => $input_params['payment_method_id'],
        'amount' => $input_params['amount'],
        'id' => $input_params['payment_processor_id'],
        'currency' => $input_params['currency'],
        'csrfToken' => $input_params['csrfToken'],
        'description' => $input_params['description']
      ); 

      return array(
        'entity' => 'StripePaymentintent',
        'action' => 'processPublic',
        'params' => $params,
      );
    }
    else {
      throw new CiviCRM_API3_Exception(E::ts("That entity is not allowed: $entity."));
    }
  }

  function profilePostSubmit($uf_group_id, $contact_id) {
    // See: https://github.com/civicrm/civicrm-core/pull/13410/
    // This following code executes what is included in the pull request.  When
    // the pull request is merged into CiviCRM, we will need to detect whether
    // or not to run the following code based on the version of CiviCRM in
    // which the code is merged.
    
    // Get notify and add to group for this profile.
    $profile_actions_params = array(
      'id' => $uf_group_id,
      'return' => array('add_to_group_id', 'notify'),
    );
    $profile_actions = civicrm_api3('UFGroup', 'getsingle', $profile_actions_params);
    if (isset($profile_actions['add_to_group_id'])) {
      $method = 'Web';
      CRM_Contact_BAO_GroupContact::addContactsToGroup(array($contact_id), $profile_actions['add_to_group_id'], $method);
    }
    if (isset($profile_actions['notify'])) {
      $val = CRM_Core_BAO_UFGroup::checkFieldsEmptyValues($uf_group_id, $contact_id, NULL);
      CRM_Core_BAO_UFGroup::commonSendMail($contact_id, $val);
    }
  }
}
