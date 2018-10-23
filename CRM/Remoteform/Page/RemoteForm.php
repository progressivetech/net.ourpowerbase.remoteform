<?php
use CRM_Remoteform_ExtensionUtil as E;

class CRM_Remoteform_Page_RemoteForm extends CRM_Core_Page {

  public function run() {
    $this->printCorsHeaders();
		$data = json_decode(stripslashes(file_get_contents("php://input")));
    try {
      $data = $this->sanitizeInput($data);
    }
    catch (Exception $e) {
      $this->exitError($e->getMessage());
    }

    try {
      // CRM_Core_Error::debug_var('data', $data);
      $result = civicrm_api3($data['entity'], $data['action'], $data['params'] ); 
      $this->exitSuccess($result['values']);
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
      throw new CiviCRM_API3_Exception('You cannot use JSSubmit while logged into CiviCRM.');
    }

    $entity = $input->entity;
    $input_params = get_object_vars($input->params);
    $action = $input->action;
    if ($entity == 'Profile') {
      // Ensure this site allows access to profiles.
      if (!CRM_Core_Permission::check('profile create')) {
        throw new CiviCRM_API3_Exception("You don't have permission to create contacts via profiles.");
      }

      // Let's see if this particular profile is allowed.
      $input_params = get_object_vars($input->params);
      $id = intval($input_params['profile_id']);
      $enabled = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_profile'));
      if (!in_array($id, $enabled)) {
        throw new CiviCRM_API3_Exception("This profile is not configured to accept remote form submissions.");
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
        return array(
          'entity' => 'Profile',
          'action' => 'submit',
          'params' => $input_params
        );
      }
      else {
        throw new CiviCRM_API3_Exception("That action is not allowed.");
      }
    }
    else if ($entity == 'RemoteFormContributionPage') {
      // Ensure this site allows access to contributions.
      if (!CRM_Core_Permission::check('make online contributions')) {
        throw new CiviCRM_API3_Exception("You don't have permission to create contributions.");
      }

      // Make sure this contribution page is configured to accept remote submissions.
      $id = intval($input_params['contribution_page_id']);
      $enabled = civicrm_api3('Setting', 'getvalue', array('name' => 'remoteform_enabled_contribution_page'));
      if (!in_array($id, $enabled)) {
        throw new CiviCRM_API3_Exception("This contribution page is not configured to accept remote form submissions.");
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
        
        CRM_Core_Error::debug_var('params', $params);
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
        throw new CiviCRM_API3_Exception("That action is not allowed.");
      }
    }
    else {
      throw new CiviCRM_API3_Exception("That entity is not allowed.");
    }
  }
}
