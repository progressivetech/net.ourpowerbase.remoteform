/**
 * Stripe related functions. Use this file as a model if you are extending
 * remoteform to work with another token-based payment processor.
 */

/** 
 *
 * initStripe
 *
 * This function is called after the form is created. It allows you to add
 * additional elements to it.
 *
 * submitStripe
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

  // Now ask Stripe to insert their janky iframe.
  stripe = Stripe(cfg.customSubmitDataParams.apiKey);
  var elements = stripe.elements();
  stripe_card = elements.create('card');
  target = document.getElementById("placeholder_stripe_cc_field").parentElement;
  stripe_card.mount(target);
}


function submitStripe(params, finalSubmitDataFunc, cfg, remoteformPostFunc) {
  console.log("cfg start", cfg);
  stripe.createPaymentMethod('card', stripe_card).then(function (result) {
    function handleServerResponse(result) {
      console.log('handleServerResponse', result);
      if (result.is_error) {
        // Show error from server on payment form
        console.log("Error: ", result);
      } else if (result.values.requires_action) {
        // Use Stripe.js to handle required card action
        handleAction(result.values);
      } else {
        // All good, we can submit the form
        successHandler('paymentIntentID', result.values.paymentIntent);
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
    
    function successHandler(type, object ) {
      params['params'][type] = object.id;
      console.log("Final post", params);
      finalSubmitDataFunc(params);
    }

    if (result.error) {
      // Show error in payment form
      console.log("Problems!", result);
    }
    else {
      var post_params = {
        payment_method_id: result.paymentMethod.id,
        amount: params['params']['amount'],
        currency: 'USD',
        payment_processor_id: params['params']['payment_processor_id'],
        description: document.title,
      };
      var args = {
        entity: 'paymentIntent',
        action: 'generate',
        params: post_params,
      }
      // Send paymentMethod.id to powerbase server
      remoteformPostFunc(args, handleServerResponse);
    }
  });
}
