/**
 * # Remoteform
 * ## Introduction
 *
 * All example code uses YOURSITE.ORG in place of the domain name of your
 * actual CiviCRM installation.
 *
 * In addition, the examples are written from Drupal paths, but WordPress and
 * Joomla should work just as well by substituting the paths for ones
 * appropriate to your CMS.
 *
 * ## Overview
 *
 * When including a remoteform on a web site, there are three kinds of
 * resources to provide.
 *
 * ### CSS, Javascript and HTML
 *
 * Typically, you will start with:
 *
 * ```
 * <link rel="stylesheet" property="stylesheet" href="https://YOURSITE.ORG/sites/all/extensions/remoteform/remoteform.css">
 * ```
 *
 * The CSS line is purely optional. The code generates HTML with Bootstrap
 * based classes, so if your web site uses the Bootstrap CSS framework you
 * should omit the CSS line entirely and it should integrate perfectly well.
 *
 * Alternatively, you can adjust your web site CSS based on the example css to
 * fully control the display.
 *
 * Or, if you use a different CSS framework, see below for how you can change
 * the CSS classes that are printed.
 *
 * Next up:
 *
 * ``` 
 * <script src="https://YOURSITE.ORG/sites/all/extensions/remoteform/remoteform.js"></script>
 * ```
 *
 * This line is required, it pulls in the javascript that makes everything
 * work. The javascript has no dependencies and should not conflict with any
 * existing libraries in use by your site.
 *
 * And lastly:
 *
 * ```
 * <div id="remoteForm"></div>
 * ```
 *
 * You have to provide a div that will enclose the form created. See below if
 * you want to use a div already present on your page but with a different id.
 *
 * ### The parameters
 *
 * You must pass a config option to the remoteForm function:
 *
 * ```
 * var remoteFormConfig = { 
 *  url: "https://YOURSITE.ORG/civicrm/remoteform",
 *  id: 1, 
 *  entity: "ContributionPage"
 * };
 *  ```
 *
 * The minimum parameters are url, id and entity. See below for details on all
 * the available parameters.
 *
 * ### The function
 *
 * Finally, you have to call the function:
 *
 * ```
 * remoteForm(remoteFormConfig); 
 * ```
 */
function remoteForm(config) {

  /** 
   * ## Properties
   *
   * ### cfg 
   *
   * cfg is a sanitized global configuration object based on config, which
   * is the object passed in by the user. All the parameters below can be
   * changed by adding or editing your ```var config``` line. For example,
   * if you read about cfg.parentElementId and decide you want to change the
   * parentId, you would pass the following to the remoteForm function:
   *
   * ```
   * var config = { 
   *  url: "https://YOURSITE.ORG/civicrm/remoteform",
   *  id: 1, 
   *  entity: "ContributionPage",
   *  parentId: "my-parent-id"
   * };
   *  ```
   */
  var cfg = {};

  /**
   * ### cfg.url
   *
   * The url of the CiviCRM installation we are posting to. Required.
   */
  cfg.url = config.url || null;

  /**
   * ### cfg.id
   *
   * The id of the entity (profile id, contribution page id, etc.). Required.
   */
  cfg.id = config.id|| null;
  if (!cfg.url || !cfg.id) {
    friendlyErr("Please include url and id in your configuration."); 
    return false;
  }

  /**
   * ### cfg.parentElementId
   *
   * The id of the element to which the form will be appended. Default: remoteform.
   */
  cfg.parentElementId = config.parentElementId || 'remoteForm';

  /**
   * ### cfg.entity
   *
   * The CiviCRM entity we are creating (currently only Profile and ContributionPage are supported). 
   * Default: Profile.
   */
  cfg.entity = config.entity || 'Profile';

  /**
   * ### cfg.paymentTestMode
   *
   * For ContributionPage entities only, indicates whether you should submit to the
   * test payment processor or the live payment processor. Default: false
   */
  cfg.paymentTestMode = config.paymentTestMode || false;

  /**
   * ### cfg.autoInit
   *
   * How the form will be initialized - either true if the form will be
   * initialized on page load or false if a button will need to be clicked in
   * order to display the form. Default: true.
   */
  cfg.autoInit = config.autoInit == false ? false : true;

  /** 
   * ### cfg.initTxt
   *
   * If cfg.autoInit is false, the text displayed on the button to click
   * for the user to display the form. Default: Fill in the form.
   */
  cfg.initTxt = config.initTxt || 'Fill in the form';

  /** 
   * ### cfg.submitTxt
   *
   * The text to display on the form's submit button. Default: Submit.
   */
  cfg.submitTxt = config.submitTxt || 'Submit';

  /** 
   * ### cfg.cancelTxt
   *
   * The text to display on the form's cancel button. Default: Cancel.
   */
  cfg.cancelTxt = config.cancelTxt || 'Cancel';

  /** 
   * ### cfg.successMsg
   *
   * The message displayed to the user upon successful submission of the
   * form. Default: Thank you! Your submission was received.
   */
  cfg.successMsg = config.successMsg || 'Thank you! Your submission was received.';

  /** 
   * ### cfg.displayLabels
   * Whether or not the form labels should be displayed when placeholder
   * text could be used instead to save space. Default: false.
   */
  cfg.displayLabels = config.displayLabels == true ? true : false;

  /** 
   * ### cfg.createFieldDivFunc
   *
   * Custom function to override the function used to create html fields
   * from the field definitions. If you don't like the way your fields are
   * being turned into html, set this parameter to a funciton that you have
   * defined and you can completley control the creation of all fields.
   * See createFieldDiv for instructions on how to create your custom function.
   */
  cfg.createFieldDivFunc = config.createFieldDivFunc || createFieldDiv;

  /**
   * ### cfg.customSubmitDataFunc
   *
   * Customize the post action if you want to use a token-based payment processor
   * and you need to send credit card details to a different server before sending
   * them to CivICRM..
   *
   * If you define this function, it should accept the following arguments.
   *
   *  - params - the fields submitted by the user, including the amount
   *    field 
   *  - submitDataPost - after your function has done it's business, you 
   *    should call this function, passing in params (which can be modified
   *    by your function, for example, to remove the credit card number), to
   *    complete the process and send the info back to CiviCRM.
   *  - customSubmitDataParams - any custom parameters passed by the user (see
   *    below). You may need to the user to include an api key, etc.
   *
   * See remoteform.stripe.js for an example.
   */
  cfg.customSubmitDataFunc = config.customSubmitDataFunc || null;

  /**
   * ### cfg.customSubmitDataParams
   *
   * An object containing any data that is specific to your customSubmitDataFunc,
   * such as an api key, etc.
   */
  cfg.customSubmitDataParams = config.customSubmitDataParams || {};

  if (!config.css) {
    config.css = {};
  }

  /**
   * ### cfg.css
   *
   * Indicate classes that should be used on various parts of the form if you
   * want more control over look and feel. The defaults are designed to work
   * with bootstrap. If you are not using bootstrap, you may want to include
   * the remoteform.css file which tries to make things look nice with the
   * default classes.
   */
  cfg.css = config.css || {};

  /**
   * #### cfg.css.userSuccessMsg
   *
   * Default: alert alert-success
   */
  cfg.css.userSuccessMsg = config.css.userSuccessMsg || 'alert alert-success';

  /** 
   * #### cfg.css.FailureMsg
   *
   * Default: alert alert-warning
   */
  cfg.css.userFailureMsg = config.css.userFailureMsg || 'alert alert-warning';

  /** 
   * #### cfg.css.button
   *
   * Default: btn btn-info
   */ 
  cfg.css.button = config.css.button || 'btn btn-info';

  /** 
   * #### cfg.css.form.
   *
   * Default: rf-form
   */
  cfg.css.form = config.css.form || 'rf-form';

  /** 
   * #### cfg.css.inputDiv
   *
   * Default: form-group
   */
  cfg.css.inputDiv = config.css.inputDiv || 'form-group';

  /**
   * #### cfg.css.checkDiv
   *
   * Default: form-check
   */
  cfg.css.checkDiv = config.css.checkDiv || 'form-check';

  /** 
   * #### cfg.css.input
   *
   * Default: form-control
   */
  cfg.css.input = config.css.input || 'form-control';

  /**
   * #### cfg.css.checkInput
   *
   * Default: form-check-input
   */
  cfg.css.checkInput = config.css.checkInput || 'form-check-input';

  /**
   * #### cfg.css.textarea
   *
   * Default: form-control
   */
  cfg.css.textarea = config.css.textarea || 'form-control';

  /**
   * #### cfg.css.label
   *
   * Default: rf-label
   */
  cfg.css.label = config.css.label || 'rf-label';

  /**
   * #### cfg.css.sr_only
   *
   * Default: sr-only
   */
  cfg.css.sr_only = config.css.sr_only || 'sr-only';

  /**
   * #### cfg.css.checkLabel
   *
   * Default: form-check-label
   */
  cfg.css.checkLabel = config.css.checkLabel || 'form-check-label';

  /**
   * #### cfg.css.select
   *
   * Default: custom-select
   */
  cfg.css.select = config.css.select || 'custom-select';

  /**
   * #### cfg.css.small
   *
   * Default: text-muted form-text
   */
  cfg.css.small = config.css.small || 'text-muted form-text';

  // Communicating with the user.
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

  // Initialize our global entities. We should end up with a *parentDiv* that
  // contains a *userMsg* div (for giving feedback to the user), *form* (containing
  // the form the user will submit) and an *initButton* that kicks everything
  // off. These are all global variables.
  var parentDiv = document.getElementById(cfg.parentElementId);
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

  // If the user wants to auto init the form, do so now.
  if (cfg.autoInit == 1) {
    displayForm();
  }

  // Now we are done. Event handler code is below.

  // Make a request for a list of fields to display, then process the
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

      // Add testing mode if necessary.
      if (cfg.paymentTestMode) {
        userMsg("In testing mode.");
        params["test_mode"] = true;
      }
    }
    var args = {
      action: 'getfields',
      entity: submitEntity,
      params: params 
    };

    // Once we get a response, send the response to processGetFieldsResponse.
    post(cfg.url, args, processGetFieldsResponse);
  }

  // Validate the response we get, then pass the validated fields to the
  // buildForm function to build the fields.
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
      // Make sure we get a single, numeric value for the payment processor
      // (if more than one is provided, we get an array)
      if (isNaN(parseFloat(fields.control.payment_processor)) || !isFinite(fields.control.payment_processor)) {
        adminMsg("Your contribution page has more than one payment processor selected. Please only check off one payment processor.");
        console.log(fields);
        return false;
      }
    }
    return true;
  }

  // When the user has filled in all their data and click submit, submitData
  // is invoked. If you want to first process a credit card, then you can
  // pass the configuration parameter customSubmitDataFunc to override.
  function submitData(fields) {
    var params = processSubmitData(fields);
    if (cfg.customSubmitDataFunc) {
      cfg.customSubmitDataFunc(params, submitDataPost, cfg.customSubmitDataParams);
    }
    else {
      submitDataPost(params);
    }
  }

  function submitDataPost(params) {
    post(cfg.url, params, processSubmitDataResponse);
  }

  // Take what the user submitted, and parse it into a more easily usable
  // object.
  function processSubmitData(fields) {
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

      // Check to see if it's a test
      if (cfg.paymentTestMode) {
        params["params"]["test_mode"] = true;
      }
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
    return params;
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

  // Post data to the CiviCRM server.
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
          console.log(url);
          console.log(params);
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
   * ## Functions related to building html widgets.
   *
   */

  // This function starts everything - returns the built form.
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
   * ### createFieldDiv
   *
   * ```createFieldDiv(key, def, type, createFieldFunc, wrapFieldFunc)```
   *
   * Create a single field with associated label wrapped in a div.
   *
   * This function can be overridden using the createFieldDivFunc config
   * parameter.
   *
   * #### Parameters:
   *
   *  - key - the unique field key
   *  - type - the type of field to build
   *  - createFieldFunc - the function to use to build the field, override
   *    as needed. See createField for a model.
   *  - wrapFieldFunc - the function to use to build the div around the field,
   *    override as needed. See wrapField as a model.
   *
   * #### Returns:
   *
   * An HTML entity including both a field label and field.
   *
   * If you don't override this function, it will do the following:
   *
   * ```
   * var field = createFieldFunc(key, def, type);
   *  if (field === null) {
   *    return null;
   *  }
   *  return wrapFieldFunc(key, def, field);
   * }
   * ```
   *   
   * By overriding the class, you can do things like create your own
   * createFieldFunc or wrapFieldFunc, then simply call createFieldDiv but pass
   * it your own function names instead of the default ones. Or you can pick
   * out a field type you want to cusotmize or even a field key and only change
   * the behavior for that one.
   *
   * Here's an example of overriding createFieldFunc to change the list of
   * groups displayed when a profile includes groups.
   *
   * ```
   * function myCreateFieldDiv(key, def, type, createFieldFunc, wrapFieldFunc) {
   *   if (key == 'group_id') {
   *     def.options = {
   *       1: "Group one",
   *       2: "group two"
   *     }
   *   }
   *   var field = createFieldFunc(key, def, type);
   *   if (field === null) {
   *    return null;
   *   }
   *   return wrapFieldFunc(key, def, field);
   * }
   * ```
   *
   * Once you have defined this function, you could pass in the paramter:
   *
   * ```
   * createFieldDivFunc: myCreateFieldDiv
   * ```
   *
   * to your remoteFormConfig.
   *
   */
  function createFieldDiv(key, def, type, createFieldFunc, wrapFieldFunc) {
    var field = createFieldFunc(key, def, type);
    if (field === null) {
      return null;
    }
    return wrapFieldFunc(key, def, field);
  }

  /**
   * ### getType
   *
   * ```getType(def)```
   *
   * If you pass in a field definition provided by CiviCRM, this function
   * returns an html input type, working around some CiviCRM idiosyncracies.
   *
   * #### Parameters
   *  - def - a CiviCRM provided field definition object.
   *
   * #### Returns
   * A string field type
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
    if (type == 'chainselect') {
      type = 'select';
    }
    return type;
  }

  /**
   * ### createField
   *
   * ```createField(key, def, type)```
   *
   * Return an HTML entity that renders the given field.
   *
   * #### Parameters
   *  - key - The unique string id for the field
   *  - def - The CiviCRM provided field definition object.
   *  - type - The string type for the field.
   *
   * ### Returns
   *
   * HTML entity.
   */
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

  /**
   * ### wrapField
   *
   * ```wrapField(key, def, field)```
   *
   * Return an HTML entity that includes both the given field and a label.
   *
   * #### Parameters
   *  - key - The unique string id for the field
   *  - def - The CiviCRM provided field definition object.
   *  - field - An HTML entity with the field 
   *
   * ### Returns
   *
   * HTML entity.
   */

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

  // Helper for creating <input> fields.
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
   * Check if this is an "other amount" price set
   *
   * Some price set options should only be displayed if the user has
   * clicked the "other amount" option. Unfortunately, it's hard to
   * tell if an option is an other amount option. With normal price sets
   * the option has the name "Other_Amount" - however, if you have a
   * contribution page and you are not using price sets, then it's called
   * Contribution_Amount.
   *
   * This function return true if we think this is an other amount
   * option or false otherwise.
   */
  function isOtherAmountOption(option) {
    if (option["name"] == 'Other_Amount') {
      return true;
    }
    else if(option["name"] == 'Contribution_Amount') {
      return true;
    }
    return false;
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
    // Always create a label, but don't create two if we already display labels.
    if (cfg.displayLabels !== true) {
      label.className = cfg.css.label;
      label.innerHTML = def.title;
      collectionDiv.appendChild(label);
    }
    
    // Another div to enclose just the options.
    var optionsDiv = document.createElement('div');

    var isPriceSet = false;

    // Keep track of whether or not this price set has an "other amount"
    // option. If so, we have to display it when requested and hide it when
    // not requested. This variable keeps track of whether one exists for
    // this particular priceset.
    var pricesetHasOtherAmountOption = false;

    // Treat price sets differently.
    if (/price_[0-9]+/.test(key)) {
      isPriceSet = true;

      // If there is an other_amount option, we need to know up front so
      // we can add event listeners that will make a new other amount
      // text box appear.
      for (var optionId in def.options) {
        if (def.options.hasOwnProperty(optionId)) {
          if (isOtherAmountOption(def.options[optionId])) {
            pricesetHasOtherAmountOption = true;
            break;
          }
        }
      }
    }

    // Now iterate over options again to build out the html.
    for (var optionId in def.options) {
      if (def.options.hasOwnProperty(optionId)) {
        // Another div to enclose this particular option.
        var optionDiv = document.createElement('div');

        // We use the same class for both radio and checkbox.
        optionDiv.className = cfg.css.checkDiv;

        // Create the input.
        var optionInput = document.createElement('input');
        optionInput.type = type;

        // We set an id so the label below can properly reference the right
        // input.
        optionInput.id = def.name + '-' + optionId;
        optionInput.className = cfg.css.checkInput;

        // We use the name field to find the values when we submit. This 
        // value has to be unique (in case we have multiple pricesets).
        optionInput.name = key;

        // Option display on the option type.
        var optionDisplay = null; 
        if (isPriceSet) {
          // Priceset options are a dict of values.
          var optionObj = def.options[optionId];
          var prefix;
          if (optionObj['currency'] == 'USD') {
            prefix = '$';
          }

          // Price set options called "Other_Amount" are handled differently.
          var optionDisplay;

          if (isOtherAmountOption(optionObj)) {
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
            optionDisplay = optionObj['label'] ? optionObj['label'] + ' - ' : '';
            optionDisplay += prefix + parseFloat(optionObj['amount']).toFixed(2);
            optionInput.setAttribute('data-amount', optionObj['amount']);
            if (pricesetHasOtherAmountOption) {
              // This is not an other amount field, but since there is 
              // an other amount option, we have to hide the other amount
              // text field if it is clicked on.
              optionInput.addEventListener('click', function(e) {
                // If we have not clicked the other amount option, then the other amount
                // field may not even exist.
                if (document.getElementById('Other_Amount')) {
                  document.getElementById('Other_Amount').style.display = 'none';
                }
              });
            }
          }
        }
        else {
          optionDisplay = def.options[optionId];
        }

        optionInput.value = optionId;
        
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

  /**
   * Populate a location drop down with the appropriate values.
   *
   * We dynamically populate the state/province, county and country
   * drop down lists by querying CiviCRM for the appropriate values.
   *
   * In the case of state province, the right values will depend on the
   * chosen country. In the case of county, the right values will depend
   * on the chosen state.
   **/
  function populateLocationOptions(loc, chosen = null, selectInput = null) {
    // Try to find the right chosen field. 
    if (chosen === null) {
      if (loc == 'state_province') {
        var country_elems = document.getElementsByClassName('remoteform-country');
        if (country_elems[0]) {
          // If there is more than one country field, we take the first.
          chosen = country_elems[0].value;
        }
      }
    }

    if (selectInput === null) {
      // Find the selectInput element to populate.
      var elementId = 'remoteform-' + loc;
      var target_elems = document.getElementsByClassName(elementId);
      if (target_elems[0]) {
        // If there is more than one, we take the first.
        selectInput = target_elems[0];
      }
      else {
        console.log("Could not find the target element.");
        return;
      }
    }

    var action = null;
    var key_field = null;
    var params = {};
    var args = {
      params: {}
    }

    var label = null;
    if (loc == 'state-province') {
      action = 'Stateprovincesforcountry';
      key_field = 'country_id';
      args['params']['country_id'] = chosen;
      label = 'State';
    }
    else if (loc == 'country') {
      action = 'Countries';
      label = 'Country';
    }
    args['action'] = action;
    args['entity'] = 'RemoteForm';

    post(cfg.url, args, function(data) {
      var optionEl;
      // Purge existing options.
      selectInput.innerHTML = '';
      if (cfg.displayLabels == false) {
        // If we are not showing labels, then create an initial option with
        // no value that displays the label in the drop down.
        optionEl = document.createElement('option');
        optionEl.value = '';
        optionEl.innerHTML = '-- select ' + label + ' --';
        selectInput.appendChild(optionEl);
      }
      Object.keys(data['values']).forEach(function(key) {
        value = data['values'][key];
        optionEl = document.createElement('option');
        optionEl.value = key;
        optionEl.innerHTML = value;
        selectInput.appendChild(optionEl);
      });
    });
  }

  // Country, state, and county fields are related - given the country,
  // we want to show the right states, given the state, we want to show
  // the right counties. This function handles the logic of setting the
  // proper callback functions and querying the civicrm database to 
  // get the correct option lists depending on other values on the form.
  function handleLocationOptions(selectInput, def) {
    var loc;
    // Add special classes so we can be sure to find these elements later
    // using getElementsByClass.
    if (def.name == 'country_id') {
      // We need to add a callback.
      selectInput.addEventListener('change', function() {
        populateLocationOptions('state-province', this.value);
     });
     loc = 'country';
    }
    else if (def.name == 'county_id') {
      selectInput.className += ' remoteform-county';
    }
    else if (def.name == 'state_province_id') {
      selectInput.className += ' remoteform-state-province';
      loc = 'state-province';
    }
    
    populateLocationOptions(loc, null, selectInput);
  }

  function createSelect(key, def) {
    // Create the select element.
    var selectInput = document.createElement('select');
    selectInput.id = key;
    if (def["api.required"] == 1) {
      selectInput.setAttribute('required', 'required');
    }
    selectInput.className = cfg.css.select;

    if (def.name == 'country_id' || def.name == 'county_id' || def.name == 'state_province_id' ) {
      handleLocationOptions(selectInput, def);
    }
    else {
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

}


