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
      $this->enhanceResults($result['values']);
      $this->exitSuccess($result['values']);
    }
    catch (CiviCRM_API3_Exception $e) {
      $this->exitError($e->getMessage());
    }
  }

  function enhanceResults(&$values) {
    reset($values);
    foreach($values as $id => $value) {
      CRM_Core_Error::debug_var('value', $value);
      if (preg_match('/^custom_([0-9]+)/', $value['field_name'], $matches)) {
        // Lookup the custom field parameters.
        $result = civicrm_api3('CustomField', 'getsingle', array('id' => $matches[1]));
        $values[$id]['html_type'] = $result['html_type'];
        if (!empty($result['option_group_id'])) {
          $params = array('option_group_id' => $result['option_group_id'], 'is_active' => 1);
          $options_result = civicrm_api3('OptionValue', 'get', $params);
          foreach($options_result['values'] as $option) {
            $option_id = $option['value'];
            $option_label = $option['label'];
            $values[$id]['options'][$option_id] = $option_label;
          }
        }
      }
      else {
        // Core fields might also have options
        $options = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', $value['field_name']);
        if (!empty($options)) {
          $values[$id]['html_type'] = 'select';
          foreach($options as $option_id => $option_label) {
            $values[$id]['options'][$option_id] = $option_label;
          }
        }
      }
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
    if ($entity == 'UFField') {
      // Ensure this site allows access to profiles.
      if (!CRM_Core_Permission::check('profile create')) {
        throw new Exception("You don't have permission to create contacts via profiles.");
      }
      $action = $input->action;
      if ($action == 'get') {
        $input_params = get_object_vars($input->params);
        if (!array_key_exists('uf_group_id', $input_params)) {
          throw new Exception("Missing uf_group_id.");
        }
        $params = array(
          'uf_group_id' => intval($input_params['uf_group_id'])
        );
        return array(
          'entity' => $entity,
          'action' => $action,
          'params' => $params
        );
      }
      else {
        throw new Exception("That action is not allowed.");
      }
    }
    elseif ($entity == 'Profile') {
      // Ensure this site allows access to profiles.
      if (!CRM_Core_Permission::check('profile create')) {
        throw new Exception("You don't have permission to create contacts via profiles.");
      }
      $action = $input->action;
      if ($action == 'submit') {
        $input_params = get_object_vars($input->params);
        CRM_Core_Error::debug_var('params', $input_params);
        return array(
          'entity' => $entity,
          'action' => $action,
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
