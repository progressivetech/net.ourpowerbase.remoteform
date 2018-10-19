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

function initStripe(params, post, cfg) {
  // Stripe related options.
  apiKey = cfg.apiKey || null;
  checkoutLogoUrl = cfg.checkoutLogoUrl || 'https://stripe.com/img/documentation/checkout/marketplace.png';
  checkoutName = cfg.checkoutName || 'Please support us';
  checkoutDescription = cfg.checkoutDescription || 'Donation';
  requireZipCode = cfg.requireZipCode || false; 

  var handler = StripeCheckout.configure({
    key: apiKey,
    image: checkoutLogoUrl,
    locale: 'auto',
    token: function(token) {
      params['params']['stripe_token'] = token.id;
      post(params);
    }
  });

  // Look for a field that looks like an email field so we don't make
  // the user fill in their email address twice.
  var email = null;
  for (var field_name in params['params']) {
    if (field_name.search('email') != -1) {
      email = params['params'][field_name];
      break;
    }
  }

  // Open Checkout with further options:
  handler.open({
    name: checkoutName,
    description: checkoutDescription,
    zipCode: requireZipCode,
    email: email,
    amount: params['params']['amount'] * 100
  });

  // Close Checkout on page navigation:
  window.addEventListener('popstate', function() {
    handler.close();
  });
}

