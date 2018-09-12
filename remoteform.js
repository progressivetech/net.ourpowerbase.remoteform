function remoteForm(config) {

  /**
   * Handle configuration.
   *
   * cfg is a global configuration object and config is the object passed
   * in by the user.
   */

  var cfg = {};
  // Required parameters.
  cfg.url = config.url || null;
  cfg.id = config.id|| null;
  if (!cfg.url || !cfg.id) {
    friendlyErr("Please include url and id in your configuration."); 
    return false;
  }

  // Default values for optional configuration.

  // The id of the element to which the form will be appended.
  cfg.parentId = config.parentID || 'remoteForm';

  // The CiviCRM entity we are creating (currently only Profile is supported).
  cfg.entity = config.entity || 'Profile';

  // How the form will be initialized - either true if the form will be
  // initialized on page load or false if a button will need to be clicked in
  // order to display the form.
  cfg.autoInit = config.autoInit == false ? false : true;

  // If autoInit is false, the text display on the button to click
  // for the user to display the form.
  cfg.initTxt = config.initTxt || 'Join our mailing list';

  // The text to display on the form's submit button.
  cfg.submitTxt = config.submitTxt || 'Join';

  // The text to display on the form's cancel button.
  cfg.cancelTxt = config.cancelTxt || 'Cancel';

  // The message displayed to the user upon successful submission of the
  // form.
  cfg.successMsg = config.successMsg || 'Thank you! Your submission was received.';

  // Whether or not the form labels should be displayed when placeholder
  // text could be used instead to save space.
  cfg.displayLabels = config.displayLabels == true ? true : false;

  // Custom function to override the function used to create html fields
  // from the field definitions.
  cfg.createFieldDivFunc = config.createFieldDivFunc || createFieldDiv;

  // Custom css - indicate classes that should be used on various parts
  // of the form.
  if (!config.css) {
    config.css = {};
  }
  cfg.css = config.css || {};
  cfg.css.userSuccessMsg = config.css.userSuccessMsg || 'alert alert-success';
  cfg.css.userFailureMsg = config.css.userFailureMsg || 'alert alert-warning';
  cfg.css.button = config.css.button || 'btn btn-info';
  cfg.css.form = config.css.form || 'rf-form';
  cfg.css.inputDiv = config.css.inputDiv || 'form-group';
  cfg.css.checkDiv = config.css.checkDiv || 'form-check';
  cfg.css.input = config.css.input || 'form-control';
  cfg.css.checkInput = config.css.checkInput || 'form-check-input';
  cfg.css.textarea = config.css.input || 'form-control';
  cfg.css.label = config.css.label || 'rf-label';
  cfg.css.sr_only = config.css.sr_only || 'sr-only';
  cfg.css.checkLabel = config.css.checkLabel || 'form-check-label';
  cfg.css.select = config.css.select || 'custom-select';
  cfg.css.small = config.css.small || 'text-muted form-text';

  /**
   * Handling communication.
   */
  function clearUserMsg() {
    userMsgDiv.innerHTML = '';
    userMsgDiv.className = '';
  }

  function userMsg(msg, type = 'error') {
    userMsgDiv.innerHTML = msg;
    if (type == 'error') {
      userMsgDiv.className = cfg.css.userFailureMsg;
    }
    else {
      userMsgDiv.className = cfg.css.userSuccessMsg;
    }
  }

  function adminMsg(msg) {
    console.log(msg);
  }

  function friendlyErr(err) {
    adminMsg(err);
    userMsg("Sorry, we encountered an error! See console log for more details.");
  }
  // Sanity checking
  if (cfg.entity != 'Profile' && cfg.entity != 'ContributionPage') {
    friendlyErr("Only Profile and ContributionPage entities is currently supported.");
    return false;
  }

  // Initialize our global entities. We should end up with a parentDiv that
  // contains a userMsg div (for giving feedback to the user), form (containing
  // the form the user will submit) and an initButton that kicks everything
  // off.
  var parentDiv = document.getElementById(cfg.parentId);
  var form = document.createElement('form');
  form.id = 'remoteForm-form-' + cfg.entity + cfg.id;
  form.className = cfg.css.form;

  var userMsgDiv = document.createElement('div');
  parentDiv.appendChild(userMsgDiv);

  // Create button that has click event to kick things off. We need this
  // even if autoInit is true so that after submission we can re-submit.
  var initButton = document.createElement('button');
  initButton.innerHTML = cfg.initTxt;
  initButton.className = cfg.css.button;
  initButton.addEventListener("click", function() {
    displayForm();
  });
  parentDiv.appendChild(initButton);

  if (cfg.autoInit == 1) {
    displayForm();
  }

  // Make a request for a list of fields to display then process the
  // response by passing it to buildForm.
  function displayForm() {
    parentDiv.appendChild(form);
    // Clear any left over user messages.
    clearUserMsg();

    var params;
    var submitEntity = cfg.entity;

    if (cfg.entity == 'Profile') {
      params = {
        profile_id: cfg.id,
        api_action: 'submit',
        get_options: 'all'
      };
    }
    else if (cfg.entity == 'ContributionPage') {
      params = {
        contribution_page_id: cfg.id,
        api_action: 'submit',
        get_options: 'all'
      };
      // Override the entity to use our own ContributionPage entity
      // because the built-in one doesn't handle our use case.
      submitEntity = 'RemoteFormContributionPage';
    }
    var args = {
      action: 'getfields',
      entity: submitEntity,
      params: params 
    };

    post(cfg.url, args, processGetFieldsResponse);
  }

  function processGetFieldsResponse(data) {
    if (data['is_error'] == 1) {
      friendlyErr(data['error_message']);
      return;
    }
    if (validateFields(data['values'])) {
      buildForm(data['values']);
      // Hide the init button.
      initButton.style.display = 'none';
    }
    else {
      friendlyErr("Failed to validate fields. You may be trying to use an entity that is too complicated for me.");
    }
  }

  // We don't support all entities - just a few and a limited set of
  // functionalities for the ones we do support. This function is
  // designed to stop if we can't handle something too complex.
  function validateFields(fields) {
    if (cfg.entity == 'ContributionPage') {
      // We can only handle one payment processor since we don't have 
      // provide the user the choice of which to use.
      if (fields.control.payment_processor.length == 0) {
        adminMsg("Your contribution page does not have a payment processor selected.");
        return false;
      }
      if (fields.control.payment_processor.length > 1) {
        adminMsg("Your contribution page has more than one payment processor selected. Please only check off one.");
        return false;
      }
    }
    return true;
  }

  function submitData(fields) {
    var params;
    if (cfg.entity == 'Profile') {
      params = {
        action: 'submit',
        entity: cfg.entity,
        params: {
          profile_id: cfg.id
        }
      };
    }
    else if (cfg.entity == 'ContributionPage') {
      params = {
        action: 'submit',
        entity: 'RemoteFormContributionPage',
        params: {
          contribution_page_id: cfg.id
        }
      };
      // We have to submit a total amount. This will be calculated when
      // we process the price set fields below.
      var amount = 0.00;
      var payment_processor = fields.control.payment_processor;
    }
    for (var key in fields) {
      if (fields.hasOwnProperty(key)) {
        var def = fields[key];
        if (!def.entity) {
          continue;
        }

        var field_name = key;

        type = getType(def);

        // Pick a variable type - single value or multiple or dict?
        if (key == 'credit_card_exp_date_M') {
          // Credit card expiration date is a dict with month and year keys.
          var value = {};
        }
        else if (type == 'checkbox') {
          // Checkboxes submit a list of values.
          var value = [];
        }
        else {
          // Everything else is a simple variable.
          var value = null;
        }

        // Obtain the value (varies depending on type and field).
        
        // Credit card expiration date has to be submitted
        // as a an array with M and Y elements.
        if (key == 'credit_card_exp_date_Y') {
          // Skip it, we'll pick it up on Month below.
          continue;
        }
        else if (key == 'credit_card_exp_date_M') {
          field_name = 'credit_card_exp_date';
          var value = {
            'M': document.getElementById('credit_card_exp_date_M').value,
            'Y': document.getElementById('credit_card_exp_date_Y').value
          };
        }
        else if (type == 'hidden') {
          value = def.default_value;
        }
        else if (type == 'checkbox' || type == 'radio') {
          var options = document.getElementsByName(key);
          for (var i = 0; i < options.length; i++) {
            if (options[i].checked) {
              if (type == 'checkbox') {
                value.push(options[i].value);
              }
              else {
                value = options[i].value;
              }

              // If this is a price set field, then we will need to calculate
              // the amount. Either it will be an 'Other_Amount' option, which
              // means we have to find the Other_Amount field to get the amount
              // or it will have the amount as a data-amount attribute.
              if (/price_[0-9]+/.test(field_name)) {
                if (options[i].hasAttribute('data-is-other-amount')) {
                  // Get the total from the Other_Amount field.
                  amount = parseFloat(document.getElementById('Other_Amount').value);
                  console.log("Amount is now ", amount);
                }
                else if (options[i].hasAttribute('data-amount')) {
                  amount = parseFloat(options[i].getAttribute('data-amount'));
                }
              }
            }
          }
        }
        else {
          var value = document.getElementById(key).value;
        }

        params['params'][field_name] = value;
      }
    }
    if (amount) {
      params['params']['amount'] = amount;
    }
    if (payment_processor) {
      params['params']['payment_processor_id'] = payment_processor;
    }
    post(cfg.url, params, processSubmitDataResponse);
  }

  function processSubmitDataResponse(data) {
    if (data['is_error'] == 1) {
      userMsg(data['error_message']);
      return;
    }
    else {
      // Success!
      resetForm(cfg.successMsg);
    }
  }

  function resetForm(msg) {
    initButton.style.display = 'inline';
    // Remove all fields to prepare for a new submission.
    while (form.firstChild) {
      form.removeChild(form.firstChild);
    }
    form.parentElement.removeChild(form);
    userMsg(msg, 'success');
  }

  /**
   * Post data to the CiviCRM server.
   */
  function post(url, params, onSuccess = console.log, onError = friendlyErr) {
    var request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.onreadystatechange = function() {
      if (request.readyState === 4) {
        if (request.status >= 200 && request.status < 400) {
          try {
            onSuccess(JSON.parse(request.responseText));
          }
          catch (err) {
            onError(err);
          }
        } else {
          onError(new Error('Response returned with non-OK status'));
          console.log(request);
        }
      }
    };
    //request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.setRequestHeader("Content-type", "application/json");
    //var data = encodeURIComponent(JSON.stringify(params));
    var data = JSON.stringify(params);
    request.send(data);
    //request.send(params);
  }

  /**
   * Functions related to building html widgets.
   */

  /**
   * This function starts everything - returns the built form.
   */
  function buildForm(fields) {
    for (var key in fields) {
      if (fields.hasOwnProperty(key)) {
        var def = fields[key];
        if (!def.entity) {
          continue;
        }
        var field;
        var type = getType(def);
        var html = cfg.createFieldDivFunc(key, def, type, createField, wrapField);
        if (html) {
          form.appendChild(html);
        }
      }
    };
    // Now add submit and cancel buttons.
    var submitButton = createSubmit();
    submitButton.value = cfg.submitTxt;
    submitButton.className = cfg.css.button;

    var cancelButton = createSubmit();
    cancelButton.value = cfg.cancelTxt;
    cancelButton.className = cfg.css.button;
    cancelButton.addEventListener('click', function() {
      resetForm("Action canceled");
    });

    var submitDiv = document.createElement('div');
    submitDiv.className = cfg.css.inputDiv;
    submitDiv.appendChild(submitButton);
    submitDiv.appendChild(cancelButton);
    form.appendChild(submitDiv);

    // Add a submit listener to the form rather than a click listener
    // to the button so we can take advantage of html5 validation which
    // is triggered on submission of a form.
    form.addEventListener('submit', function(event) {
      event.preventDefault();
      submitData(fields);
    });
  }

  /**
   * Create a single field with associated label wrapped in a div.
   *
   * This function can be overridden using the createFieldDivFunc config
   * parameter.
   */
  function createFieldDiv(key, def, type, createFieldFunc, wrapFieldFunc) {
    var field = createFieldFunc(key, def, type);
    if (field === null) {
      return null;
    }
    return wrapFieldFunc(key, def, field);
  }

  /**
   * Return field type.
   *
   * This works around CiviCRM API idiosyncracies.
   */
  function getType(def) {
    var type;
    if (def.html && def.html.type) {
      type = def.html.type.toLowerCase();
    }
    else if (def.html_type) {
      type = def.html_type.toLowerCase();
    }
    if (!type) {
      return null;
    }
    if (type == 'text' && def.entity == 'email') {
      type = 'email';
    }
    if (type == 'select date') {
      type = 'date';
    }
    return type;
  }

  function createField(key, def, type) {
    if (type == 'select') {
      return createSelect(key, def);
    }
    else if (type == 'email') {
      return createEmailInput(key, def);
    }
    else if (type == 'checkbox') {
      return createCheckboxesOrRadios(key, def, 'checkbox');
    }
    else if (type == 'radio') {
      return createCheckboxesOrRadios(key, def, 'radio');
    }
    else if (type == 'textarea') {
      return createTextArea(key, def);
    }
    else if (type == 'date') {
      return createDate(key, def);
    }
    else if (type =='hidden') {
      return null;
    }
    else {
      return createTextInput(key, def);
    }
  }

  function wrapField(key, def, field) {
    var div = document.createElement('div');
    div.className = cfg.css.inputDiv;
    if (def.help_pre) {
      var small = document.createElement('small');
      small.className = cfg.css.small;
      small.innerHTML = def.help_pre;
      div.appendChild(small);
    }

    var label = document.createElement('label');
    if (cfg.displayLabels == true) {
      label.className = cfg.css.label;
    }
    else {
      // sr_only will hide except for screen readers.
      label.className = cfg.css.sr_only;
    }
    label.for = key;
    label.innerHTML = def.title;
    div.appendChild(label);

    div.appendChild(field);
    if (def.help_post) {
      var small = document.createElement('small');
      small.className = cfg.css.small;
      small.innerHTML = def.help_post;
      div.appendChild(small);
    }
    return div;
  }

  /**
   * Helper for creating <input> fields.
   */
  function createInput(key, def, inputType = null) {
    var field = document.createElement('input');
    if (key) {
      field.id = key;
    }
    if (inputType) {
      field.type = inputType;
    }
    if (def["api.required"] == 1) {
      field.setAttribute('required', 'required');
    }
    if (def.title) {
      field.placeholder = def.title;
    }
    field.className = cfg.css.input;
    if (def.default_value) {
      field.value = def.default_value;
    }
    return field;
  }
  
  function createEmailInput(key, def) {
    return createInput(key, def, 'email');
  }

  function createTextInput(key, def) {
    return createInput(key, def, 'text');
  }

  function createSubmit() {
    var def = {};
    var key = null;
    return createInput(key, def, 'submit');
  }

  /**
   * Checkbox and Radio collections.
   */
  function createCheckboxesOrRadios(key, def, type) {
    // Creating enclosing div for the collection.
    var collectionDiv = document.createElement('div');

    // One label for the collection (we include a label even if
    // cfg.displayLabels is false becaues there is no other way to show it.
    var label = document.createElement('label');
    label.className = cfg.css.label;
    label.innerHTML = def.title;
    collectionDiv.appendChild(label);
    
    // Another div to enclose just the options.
    var optionsDiv = document.createElement('div');

    var isPriceSet = false;
    var hasOtherAmountOption = false;
    // Treat price sets differently.
    if (/price_[0-9]+/.test(key)) {
      isPriceSet = true;

      // If there is an other_amount option, we need to know up front so
      // we can add event listeners that will make a new other amount
      // text box appear.
      for (var option in def.options) {
        if (def.options.hasOwnProperty(option)) {
          if (def.options.name == 'Other_amount') {
            hasOtherAmountOption = true;
          }
        }
      }
    }

    // Now iterate over options again to build out the html.
    for (var option in def.options) {
      if (def.options.hasOwnProperty(option)) {
        // Another div to enclose this particular option.
        var optionDiv = document.createElement('div');

        // We use the same class for both radio and checkbox.
        optionDiv.className = cfg.css.checkDiv;

        // Create the input.
        var optionInput = document.createElement('input');
        optionInput.type = type;

        // We set an id so the label below can properly reference the right
        // input.
        optionInput.id = def.name + '-' + option;
        optionInput.className = cfg.css.checkInput;

        // We use the name field to find the values when we submit. This 
        // value has to be unique (in case we have multiple pricesets).
        optionInput.name = key;

        // Option display on the option type.
        var optionDisplay = null; 
        if (isPriceSet) {
          // Priceset options are a dict of values.
          var optionObj = def.options[option];
          var prefix;
          if (optionObj['currency'] == 'USD') {
            prefix = '$';
          }

          // Price set options called "Other_Amount" are handled differently.
          var optionDisplay;
          if (optionObj['name'] == 'Other_Amount') {
            // Don't display "amount" (because with other_amount it is 
            // set to is the minimum amount).
            optionDisplay = optionObj['label'];

            // Add an attribute so we know it is an Other_Amount field when
            // we are calculating the total amount to submit.
            optionInput.setAttribute('data-is-other-amount', 1);

            // If clicked, show other amount text box.
            optionInput.addEventListener('click', function(e) {
              // If Other_Amount is chosen, display box for user to enter
              // the other amount. It should be inserted after the enclosing
              // div of the other amount option.
              var referenceNode = document.getElementById(optionInput.id).parentNode;
              var otherAmountDef = {
                'api.required': 1,
                title: 'Other Amount'
              };

              var otherAmountEl = cfg.createFieldDivFunc('Other_Amount', otherAmountDef, 'text', createField, wrapField);
              referenceNode.parentNode.insertBefore(otherAmountEl, referenceNode.nextSibling);
            });
          }
          else {
            optionDisplay = optionObj['label'] + ' (' + prefix + parseFloat(optionObj['amount']).toFixed(2) + ')';
            optionInput.setAttribute('data-amount', optionObj['amount']);
            if (hasOtherAmountOption) {
              // This is not an other amount field, but since there is 
              // an other amount option, we have to hide the other amount
              // text field if it is clicked on.
              optionInput.addEventListener('click', function(e) {
                document.getElementById('Other_Amount').style.display = 'none';
              });
            }
          }
        }
        else {
          optionDisplay = def.options[option];
        }

        optionInput.value = option;
        
        // Create the label.
        var optionLabel = document.createElement('label');
        optionLabel.for = optionInput.id; 

        // We have both simple options (the label is the value, e.g.
        // options = [ { key: label }, { key: label} ] and also 
        // complex options (used for price sets) which have more data:
        // options = [ {key: { label: label, amount: amount, name: name}, etc.
        
        optionLabel.innerHTML = optionDisplay;
        optionLabel.className = cfg.css.checkLabel;

        // Insert all our elements.
        optionDiv.appendChild(optionInput);
        optionDiv.appendChild(optionLabel);
        optionsDiv.appendChild(optionDiv);
      }
    }
    collectionDiv.appendChild(optionsDiv);
    return collectionDiv;
  }

  function createSelect(key, def) {
    // Create the select element.
    var selectInput = document.createElement('select');
    selectInput.id = key;
    if (def["api.required"] == 1) {
      selectInput.setAttribute('required', 'required');
    }
    selectInput.className = cfg.css.select;

    var optionEl;
    if (cfg.displayLabels == false) {
      // If we are not showing labels, then create an initial option with
      // no value that displays the label in the drop down.
      optionEl = document.createElement('option');
      optionEl.value = '';
      optionEl.innerHTML = '--' + def.title + '--';
      selectInput.appendChild(optionEl);
    }
    for (var option in def.options) {
      if (def.options.hasOwnProperty(option)) {
        var optionDef = def.options[option];
        optionEl = document.createElement('option');
        optionEl.value = option;
        optionEl.innerHTML = def.options[option];
        selectInput.appendChild(optionEl);
      }
    }
    return selectInput;
  }
  
  function createTextArea(key, def) {
    field = document.createElement('textarea');
    if (def["api.required"] == 1) {
      field.setAttribute('required', 'required');
    }
    if (def.title) {
      field.placeholder = def.title;
    }
    field.className = cfg.css.input;
    if (key) {
      field.id = key;
    }
    if (def.default_value) {
      field.innerHTML = def.default_value;
    }
    field.className = cfg.css.textarea;
    return field;
  }

  function createDate(key, def) {
    return createInput(key, def, 'date'); 
  }

  /**
   * Stripe related functions.
   */
  function sendToken(token) {
    // You can access the token ID with `token.id`.
    // Get the token ID to your server-side code for use.
    // NOTE: requires CORS enabled destination.
    
    var params = "token_id=" + encodeURIComponent(token.id);
    params += "&token_email=" + encodeURIComponent(token.email);
    
    for (var i=0; i < remoteFormParams.fields.length; i++) {
      params += '&' + remoteFormParams.fields[i].id + '=' +
        document.getElementById(remoteFormParams.fields[i].id).value;
    }
    
    post(url, params);
  }

  function initStripe() {
    var handler = StripeCheckout.configure({
      key: remoteFormParams.apiKey ,
      image: 'https://stripe.com/img/documentation/checkout/marketplace.png',
      locale: 'auto',
      token: function(token) {
        sendToken(token);   
      }
    });

    document.getElementById('remoteFormButton').addEventListener('click', function(e) {
      // Open Checkout with further options:
      handler.open({
        name: 'PowerBase',
        description: 'Donation',
        zipCode: false,
        amount: document.getElementById('remoteFormAmount').value * 100
      });
      e.preventDefault();
    });

    // Close Checkout on page navigation:
    window.addEventListener('popstate', function() {
      handler.close();
    });
  }
}


