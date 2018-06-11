function remoteForm(config) {
  function clearUserMsg() {
    userMsgDiv.innerHTML = '';
    userMsgDiv.className = '';
  }

  function userMsg(msg, type = 'error') {
    // Log all messages.
    adminMsg(msg);
    userMsgDiv.innerHTML = msg;
    if (type == 'error') {
      userMsgDiv.className = userFailureMsgClass;
    }
    else {
      userMsgDiv.className = userSuccessMsgClass;
    }
  }

  function adminMsg(msg) {
    console.log(msg);
  }

  function friendlyErr(err) {
    adminMsg(err);
    userMsg("Sorry, we encountered an error! See console log for more details.");
  }

  var url = config['url'] || null;
  var id = config['id'] || null;

  // url and id are required.
  if (!url || !id) {
    friendlyErr("Please include url and id in your configuration."); 
    return false;
  }

  // Default values for other items.
  var parentId = config['parentId'] || 'remoteForm';
  var entity = config['entity'] || 'Profile';
  var userSuccessMsg = config['successUserMsg'] || 'Thank you! Your submission was received.';
  var userSuccessMsgClass = config['userSuccessMsgClass'] || 'alert alert-success';
  var userFailureMsgClass = config['userFailureMsgClass'] || 'alert alert-warning';
  var buttonClass = config['userMsgClass'] || 'btn btn-info';
  var massageFieldsFunc = config['massageFields'] || massageFields;
  var createFieldsFunc = config['createFields'] || createFields;
  var initMessage = config['initMessage'] || 'Join our mailing list';
  var submitMessage = config['initMessage'] || 'Join';

  // Sanity checking
  if (entity != 'Profile') {
    friendlyErr("Only Profile entity is currently supported.");
    return false;
  }

  // Initialize our global variables and entities. We should end up with a
  // parentDiv that contains a userMsg div (for giving feedback to the user),
  // fieldsetDiv (containing the form the user will submit) and an initButton
  // that kicks everything off.
  var fieldsetId = 'remoteForm-fieldset-' + entity + id;
  var initButtonId = 'remoteForm-initButton-' + entity + id;
  var userMsgId = 'remoteForm-userMsg-' + entity + id;

  var parentDiv = document.getElementById(parentId);

  var fieldsetDiv = document.createElement('form');
  fieldsetDiv.id = fieldsetId
  parentDiv.appendChild(fieldsetDiv);

  var userMsgDiv = document.createElement('div');
  userMsgDiv.id = userMsgId;
  parentDiv.appendChild(userMsgDiv);

  // The initButton is the part the makes everything else happen.
  var initButton = document.createElement('button');
  initButton.addEventListener("click", function() {
    displayForm(id);
  });
  initButton.innerHTML = initMessage;
  initButton.id = initButtonId;
  initButton.className = buttonClass;
  parentDiv.appendChild(initButton);
  
  // Make a request for a list of fields to display then process the
  // response by passing it to the createFieldsFunc.
  function displayForm(id) {
    // Clear any left over user messages.
    clearUserMsg();
    getFields(entity, id);
    function getFields(entity, id) {
      var getEntity = null;
      if (entity == 'Profile') {
        getEntity = 'UFField';
      }

      var params = {
        action: 'get',
        entity: getEntity,
        params: {
          uf_group_id: id
        }
      };

      post(url, params, processGetFieldsResponse);
    }

    function processGetFieldsResponse(data) {
      if (data['is_error'] == 1) {
        friendlyErr(data['error_message']);
        return;
      }
      var fields = massageFieldsFunc(data['values']);
      initButton.style.display = 'none';
      createFieldsFunc(fields, fieldsetDiv);
    }
  }

  // Optionally you can change any of the field values provided by the
  // server via a custom function.
  function massageFields(fields) {
    // Override if you want.
    return fields;
  }
  
  // Given the fields and the fieldsetDiv, create the fields to be displayed.
  function createFields(fields, fieldsetDiv) {
    for (var key in fields) {
      if (fields.hasOwnProperty(key)) {
        var def = fields[key];
        var input;
        console.log(def);
        if (def.options) {
          input = document.createElement('select');
          for (var option in def.options) {
            if (def.options.hasOwnProperty(option)) {
              var optionDef = def.options[option];
              var select = document.createElement('option');
              select.value = option;
              select.innerHTML = def.options[option];
              input.appendChild(select);
            }
          }
        }
        else {
          input = document.createElement('input');
          if (def.field_name == 'email') {
            input.type = 'email';
          }
          else {
            input.type = 'text';
          }
        }
        input.className = def.id;
        input.name = def.id;
        input.id = def.id;
        input.placeholder = def.label
        if (def.is_required == 1) {
          input.setAttribute('required', 'required');
        }
        fieldsetDiv.appendChild(input);
      }
    };
    // Now add a submit button.
    var submitButton = document.createElement('input');
    submitButton.type = 'submit';
    submitButton.value = submitMessage;
    fieldsetDiv.addEventListener('submit', function(event) {
      event.preventDefault();
      submitData(fields);
    });

    submitButton.className = buttonClass;
    fieldsetDiv.appendChild(submitButton);
  }

  function submitData(fields) {
    var params = {
      action: 'submit',
      entity: 'Profile',
      params: {
        profile_id: id
      }
    };
    for (var key in fields) {
      if (fields.hasOwnProperty(key)) {
        var def = fields[key];
        var name = def.field_name;
        if (def.location_type_id) {
          name += '-' + def.location_type_id;
        }
        var value = document.getElementById(def.id).value;
        //if (def.is_required == 1 && !value) {
        //  userMsg("Please enter a value for " + def.label + '.');
        //  return;
        //}
        params['params'][name] = value;
      }
    }
    post(url, params, processSubmitResponse);
  }

  function processSubmitResponse(data) {
    if (data['is_error'] == 1) {
      userMsg(data['error_message']);
      return;
    }
    else {
      // Success!
      initButton.style.display = 'inline';
      // Remove all fields to prepare for a new submission.
      while (fieldsetDiv.firstChild) {
        fieldsetDiv.removeChild(fieldsetDiv.firstChild);
      }
      userMsg(userSuccessMsg, 'success');
    }
  }

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

  function post(url, params, onSuccess = console.log, onError = console.log) {
    var request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.onreadystatechange = function() {
      if (request.readyState === 4) {
        if (request.status >= 200 && request.status < 400) {
          onSuccess(JSON.parse(request.responseText));
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
