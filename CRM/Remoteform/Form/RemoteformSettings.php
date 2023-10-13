<?php

use CRM_Remoteform_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Remoteform_Form_RemoteformSettings extends CRM_Core_Form {
  private $_settingFilter = array('group' => 'remoteform');
	private $_settings = NULL; 
	private $_submittedValues = array();

  function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts("Remote Form Settings"));

    $settings = $this->getFormSettings();
    foreach ($settings as $name => $setting) {
      if (isset($setting['quick_form_type'])) {
        $add = 'add' . $setting['quick_form_type'];
        if ($add == 'addElement') {
          $this->$add($setting['html_type'], $name, ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array ()));
        }
        elseif ($setting['html_type'] == 'Select') {
          $optionValues = array();
          if (!empty($setting['pseudoconstant'])) {
            if(!empty($setting['pseudoconstant']['optionGroupName'])) {
              $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
            }
            elseif (!empty($setting['pseudoconstant']['callback'])) {
              $cb = Civi\Core\Resolver::singleton()->get($setting['pseudoconstant']['callback']);
              $optionValues = call_user_func_array($cb, $optionValues);
            }
          }
          $this->add('select', $setting['name'], $setting['title'], $optionValues, FALSE, $setting['html_attributes']);
        }
        else {
          $this->$add($name, ts($setting['title']));
        }
        $this->assign("{$setting['description']}_description", ts('description'));
      }
    }
    $this->addButtons(array(
      array (
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      )
    ));
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }
  function postProcess() {
    $this->_submittedValues = $this->exportValues();
    $this->saveSettings();
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
    // items don't have labels. We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function getFormSettings() {
		if (is_null($this->_settings)) {
      $this->_settings = array();
      $result = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
      $this->_settings = $result['values'];
    }
    return $this->_settings;
  }

  /**
   * Get the settings we are going to allow to be set on this form.
   *
   * @return array
   */
  function saveSettings() {
    $settings = $this->getFormSettings();
    $values = array_intersect_key($this->_submittedValues, $settings);
    civicrm_api3('setting', 'create', $values);
    $session = CRM_Core_Session::singleton();
    $session->setStatus(E::ts("Settings were saved."), E::ts("Remote Form"), "success");

  }
  /**
   * Set defaults for form.
   *
   * @see CRM_Core_Form::setDefaultValues()
   */
  function setDefaultValues() {
    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
    $defaults = array();
    $domainID = CRM_Core_Config::domainID();
    foreach ($existing['values'][$domainID] as $name => $value) {
      $defaults[$name] = $value;
    }
    return $defaults;
  }

  /**
   * Rules callback. 
  */
  public function addRules() {
    $this->addFormRule(array('CRM_Remoteform_Form_RemoteformSettings', 'myRules'));
  }

  static function myRules($values) {
    $errors = array();

    $urls = explode("\n", $values['remoteform_cors_urls']);
    foreach($urls as $url) {
      if (substr($url, 0, 8) != 'https://') {
        $errors['remoteform_cors_urls'] = E::ts('Please add one URL per line and ensure they all start with https://');
      }
      if (substr($url, -1, 1) == '/') {
        $errors['remoteform_cors_urls'] = E::ts('URLs should not end in a slash.');
      }
    }

    return empty($errors) ? TRUE : $errors;
  }
}


