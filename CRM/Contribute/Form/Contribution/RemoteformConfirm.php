<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 */

/**
 * This class overrides the Confirm class only so the submit function can return
 * created values. 
 */
class CRM_Contribute_Form_Contribution_RemoteformConfirm extends CRM_Contribute_Form_Contribution_Confirm {
  /**
   * Submit function.
   *
   * @param array $params
   *
   * @throws CiviCRM_API3_Exception
   */
  public static function submit($params) {
    # Added by remoteform:
    if (array_key_exists('email-primary', $params)) {
      # For some reason, the email is not created without the capitalization.
      $params['email-Primary'] = $params['email-primary'];
    }
    $form = new CRM_Contribute_Form_Contribution_Confirm();
    $form->_id = $params['id'];

    // Added by remoteform:
    if (isset($params['test_mode']) && $params['test_mode'] == 1) {
      $form->_mode = 'test';
    }

    CRM_Contribute_BAO_ContributionPage::setValues($form->_id, $form->_values);
    $form->_separateMembershipPayment = CRM_Contribute_BAO_ContributionPage::getIsMembershipPayment($form->_id);
    //this way the mocked up controller ignores the session stuff
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $form->controller = new CRM_Contribute_Controller_Contribution();
    $params['invoiceID'] = md5(uniqid(rand(), TRUE));
    $paramsProcessedForForm = $form->_params = self::getFormParams($params['id'], $params);
    $form->_amount = $params['amount'];
    // hack these in for test support.
    $form->_fields['billing_first_name'] = 1;
    $form->_fields['billing_last_name'] = 1;
    // CRM-18854 - Set form values to allow pledge to be created for api test.
    if (CRM_Utils_Array::value('pledge_block_id', $params)) {
      $form->_values['pledge_id'] = CRM_Utils_Array::value('pledge_id', $params, NULL);
      $form->_values['pledge_block_id'] = $params['pledge_block_id'];
      $pledgeBlock = CRM_Pledge_BAO_PledgeBlock::getPledgeBlock($params['id']);
      $form->_values['max_reminders'] = $pledgeBlock['max_reminders'];
      $form->_values['initial_reminder_day'] = $pledgeBlock['initial_reminder_day'];
      $form->_values['additional_reminder_day'] = $pledgeBlock['additional_reminder_day'];
      $form->_values['is_email_receipt'] = FALSE;
    }
    $priceSetID = $form->_params['priceSetId'] = $paramsProcessedForForm['price_set_id'];
    $priceFields = CRM_Price_BAO_PriceSet::getSetDetail($priceSetID);
    $priceSetFields = reset($priceFields);
    $form->_values['fee'] = $priceSetFields['fields'];
    $form->_priceSetId = $priceSetID;
    $form->setFormAmountFields($priceSetID);
    $capabilities = array();
    if ($form->_mode) {
      $capabilities[] = (ucfirst($form->_mode) . 'Mode');
    }
    $form->_paymentProcessors = CRM_Financial_BAO_PaymentProcessor::getPaymentProcessors($capabilities);
    $form->_params['payment_processor_id'] = !empty($params['payment_processor_id']) ? $params['payment_processor_id'] : 0;
    $form->_paymentProcessor = $form->_paymentProcessors[$form->_params['payment_processor_id']];
    if (!empty($params['payment_processor_id'])) {
      // The concept of contributeMode is deprecated as is the billing_mode concept.
      if ($form->_paymentProcessor['billing_mode'] == 1) {
        $form->_contributeMode = 'direct';
      }
      else {
        $form->_contributeMode = 'notify';
      }
    }

    $priceFields = $priceFields[$priceSetID]['fields'];
    $lineItems = array();
    CRM_Price_BAO_PriceSet::processAmount($priceFields, $paramsProcessedForForm, $lineItems, 'civicrm_contribution', $priceSetID);
    $form->_lineItem = array($priceSetID => $lineItems);
    $membershipPriceFieldIDs = array();
    foreach ((array) $lineItems as $lineItem) {
      if (!empty($lineItem['membership_type_id'])) {
        $form->set('useForMember', 1);
        $form->_useForMember = 1;
        $membershipPriceFieldIDs['id'] = $priceSetID;
        $membershipPriceFieldIDs[] = $lineItem['price_field_value_id'];
      }
    }
    $form->set('memberPriceFieldIDS', $membershipPriceFieldIDs);
    // Added by remoteform:
    return $form->processFormSubmission(CRM_Utils_Array::value('contact_id', $params));
  }
}
