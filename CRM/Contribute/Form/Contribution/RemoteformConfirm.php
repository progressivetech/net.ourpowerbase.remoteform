<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
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
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
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
    //this way the mocked up controller ignores the session stuff
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $form->controller = new CRM_Contribute_Controller_Contribution();
    $params['invoiceID'] = md5(uniqid(rand(), TRUE));

    $paramsProcessedForForm = $form->_params = self::getFormParams($params['id'], $params);

    $form->order = new CRM_Financial_BAO_Order();
    $form->order->setPriceSetIDByContributionPageID($params['id']);
    $form->order->setPriceSelectionFromUnfilteredInput($params);
    if (isset($params['amount']) && !$form->isSeparateMembershipPayment()) {
      // @todo deprecate receiving amount, calculate on the form.
      $form->order->setOverrideTotalAmount((float) $params['amount']);
    }
    // hack these in for test support.
    $form->_fields['billing_first_name'] = 1;
    $form->_fields['billing_last_name'] = 1;
    // CRM-18854 - Set form values to allow pledge to be created for api test.
    if (!empty($params['pledge_block_id'])) {
      $form->_values['pledge_id'] = $params['pledge_id'] ?? NULL;
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
    $capabilities = [];
    if ($form->_mode) {
      $capabilities[] = (ucfirst($form->_mode) . 'Mode');
    }
    $form->_paymentProcessors = CRM_Financial_BAO_PaymentProcessor::getPaymentProcessors($capabilities);
    $form->_params['payment_processor_id'] = $params['payment_processor_id'] ?? 0;
    if ($form->_params['payment_processor_id'] !== '') {
      // It can be blank with a $0 transaction - then no processor needs to be selected
      $form->_paymentProcessor = $form->_paymentProcessors[$form->_params['payment_processor_id']];
    }

    if (!empty($params['useForMember'])) {
      $form->set('useForMember', 1);
      $form->_useForMember = 1;
    }
    $priceFields = $priceFields[$priceSetID]['fields'];
    $membershipPriceFieldIDs = [];
    foreach ($form->order->getLineItems() as $lineItem) {
      if (!empty($lineItem['membership_type_id'])) {
        $form->set('useForMember', 1);
        $form->_useForMember = 1;
        $membershipPriceFieldIDs['id'] = $priceSetID;
        $membershipPriceFieldIDs[] = $lineItem['price_field_value_id'];
      }
    }
    $form->set('memberPriceFieldIDS', $membershipPriceFieldIDs);
    $form->setRecurringMembershipParams();
    // Modified by remoteform:
    // $form->processFormSubmission($params['contact_id'] ?? NULL);
    return $form->processFormSubmission($params['contact_id'] ?? NULL);
  }
}
