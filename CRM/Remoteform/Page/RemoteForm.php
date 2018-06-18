<?php
use CRM_Remoteform_ExtensionUtil as E;

class CRM_Remoteform_Page_RemoteForm extends CRM_Core_Page {

  public function run() {
    $this->printCorsHeaders();
		$data = json_decode(stripslashes(file_get_contents("php://input")));
    try {
      $data = $this->sanitizeInput($data);
    }
    catch (CiviCRM_API3_Exception $e) {
      $this->exitError($e->getMessage());
    }

    try {
      $result = civicrm_api3($data['entity'], $data['action'], $data['params'] ); 
      $this->exitSuccess($result['values']);
    }
    catch (CiviCRM_API3_Exception $e) {
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
      if ($_SERVER['HTTP_ORIGIN'] == 'https://progressivetech.loc.cx') {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
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
      throw new Exception('You cannot use JSSubmit while logged into CiviCRM.');
    }

    $entity = $input->entity;
    if ($entity == 'Profile') {
      // Ensure this site allows access to profiles.
      if (!CRM_Core_Permission::check('profile create')) {
        throw new Exception("You don't have permission to create contacts via profiles.");
      }
      $action = $input->action;
      $input_params = get_object_vars($input->params);
      if ($action == 'getfields') {
        // Sanitize input parameters.
        $api_action = $input_params['api_action'] == 'submit' ? 'submit' : NULL;
        $get_options = $input_params['get_options'] == 'all' ? 'all' : NULL;
        $params = array(
          'profile_id' => intval($input_params['profile_id']),
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
        throw new Exception("That action is not allowed.");
      }
    }
    else {
      throw new Exception("That entity is not allowed.");
    }
  }
}
