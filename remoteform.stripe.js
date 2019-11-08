/**
 * Stripe related functions. Use this file as a model if you are extending
 * remoteform to work with another token-based payment processor.
 */

/** 
 * initStripe
 *
 * This function is called after the user chooses the amount to pay and has
 * filled out the profile.
 *
 * @params is the data they are submitting
 * @post is the function we should call after we do our business 
 * @cfg is the customSubmitDataParams which includes
 *   configuraiton items specific to this payment processor.
 */


// These variables are needed globally.
var stripe_card;
var stripe;

function initStripe(cfg) {
  // Create a div to hold the credit card fields.
  ccDiv = document.createElement('div');
  ccDiv.id = 'card-element';

  // We want to insert the credit card fields before the submit buttons.
  referenceEl = document.getElementById('remoteform-submit');
  document.getElementById('remoteForm-form-' + cfg.entity + cfg.id).insertBefore(ccDiv, referenceEl );

  // Now ask Stripe to insert their janky iframe.
  stripe = Stripe(cfg.customSubmitDataParams.apiKey);
  var elements = stripe.elements();
  stripe_card = elements.create('card');
  stripe_card.mount('#card-element');
}


function submitStripe(params, post, cfg) {
  stripe.createPaymentMethod('card', stripe_card).then(function (result) {
    if (result.error) {
      // Show error in payment form
      console.log("Problems!", result);
    }
    else {
      var params = {
        payment_method_id: result.paymentMethod.id,
        amount: getTotalAmount(),
        currency: CRM.vars.stripe.currency,
        id: CRM.vars.stripe.id,
        description: document.title,
      };
      var args = {
        entity: 'paymentIntent',
        action: 'generate',
        params: params,
      }
      // Send paymentMethod.id to server
      post(args, handleServerResponse);
    }

    function handleServerResponse(result) {
      console.log('handleServerResponse');
      if (result.error) {
        // Show error from server on payment form
        console.log(result);
      } else if (result.requires_action) {
        // Use Stripe.js to handle required card action
        handleAction(result);
      } else {
        // All good, we can submit the form
        successHandler('paymentIntentID', result.paymentIntent);
      }
    }

    function handleAction(response) {
      stripe.handleCardAction(response.payment_intent_client_secret)
        .then(function(result) {
          if (result.error) {
            // Show error in payment form
            console.log(result);
          } else {
            // The card action has been handled
            // The PaymentIntent can be confirmed again on the server
            successHandler('paymentIntentID', result.paymentIntent);
          }
        });
    }

    funciton successHandler(type, object ) {
      params['params']['stripe_token'] = token.id;
      params['params'][type] = object.id;
      post(params);
    }
  });
}
