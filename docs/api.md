# Remoteform
## Introduction

All example code uses YOURSITE.ORG in place of the domain name of your
actual CiviCRM installation.

In addition, the examples are written from Drupal paths, but WordPress and
Joomla should work just as well by substituting the paths for ones
appropriate to your CMS.

## Overview

When including a remoteform on a web site, there are three kinds of
resources to provide.

### CSS, Javascript and HTML

Typically, you will start with:

```
<link rel="stylesheet" property="stylesheet" href="https://YOURSITE.ORG/sites/all/extensions/remoteform/remoteform.css">
```

The CSS line is purely optional. The code generates HTML with Bootstrap
based classes, so if your web site uses the Bootstrap CSS framework you
should omit the CSS line entirely and it should integrate perfectly well.

Alternatively, you can adjust your web site CSS based on the example css to
fully control the display.

Or, if you use a different CSS framework, see below for how you can change
the CSS classes that are printed.

Next up:

``` 
<script src="https://YOURSITE.ORG/sites/all/extensions/remoteform/remoteform.js"></script>
```

This line is required, it pulls in the javascript that makes everything
work. The javascript has no dependencies and should not conflict with any
existing libraries in use by your site.

And lastly:

```
<div id="remoteForm"></div>
```

You have to provide a div that will enclose the form created. See below if
you want to use a div already present on your page but with a different id.

### The parameters

You must pass a config option to the remoteForm function:

```
var config = { 
url: "https://YOURSITE.ORG/civicrm/remoteform",
id: 1, 
entity: "ContributionPage"
};
```

The minimum parameters are url, id and entity. See below for details on all
the available parameters.

### The function

Finally, you have to call the function:

```
remoteForm(config); 
```
## Properties

### cfg 

cfg is a sanitized global configuration object based on config, which
is the object passed in by the user. All the parameters below can be
changed by adding or editing your ```var config``` line. For example,
if you read about cfg.parentElementId and decide you want to change the
parentId, you would pass the following to the remoteForm function:

```
var config = { 
url: "https://YOURSITE.ORG/civicrm/remoteform",
id: 1, 
entity: "ContributionPage",
parentId: "my-parent-id"
};
```
### cfg.url

The url of the CiviCRM installation we are posting to. Required.
### cfg.id

The id of the entity (profile id, contribution page id, etc.). Required.
### cfgt.parentElementId

The id of the element to which the form will be appended. Default: remoteform.
### cfg.entity

The CiviCRM entity we are creating (currently only Profile and ContributionPage are supported). 
Default: Profile.
### cfg.paymentTestMode

For ContributionPage entities only, indicates whether you should submit to the
test payment processor or the live payment processor. Default: false
### cfg.autoInit

How the form will be initialized - either true if the form will be
initialized on page load or false if a button will need to be clicked in
order to display the form. Default: true.
### cfg.initTxt

If cfg.autoInit is false, the text displayed on the button to click
for the user to display the form. Default: Join our mailing list.
### cfg.submitTxt

The text to display on the form's submit button. Default: Join.
### cfg.cancelTxt

The text to display on the form's cancel button. Default: Cancel.
### cfg.SuccessMsg

The message displayed to the user upon successful submission of the
form. Default: Thank you! Your submission was received.
### cfg.displayLabels
Whether or not the form labels should be displayed when placeholder
text could be used instead to save space. Default: false.
### cfg.createFieldDivFunc

Custom function to override the function used to create html fields
from the field definitions. If you don't like the way your fields are
being turned into html, set this parameter to a funciton that you have
defined and you can completley control the creation of all fields.
See createFieldDiv for instructions on how to create your custom function.
### cfg.customSubmitDataFunc

Customize the post action if you want to use a token-based payment processor
and you need to send credit card details to a different server before sending
them to CivICRM..

If you define this function, it should accept the following arguments.

- params - the fields submitted by the user, including the amount
field 
- submitDataPost - after your function has done it's business, you 
should call this function, passing in params (which can be modified
by your function, for example, to remove the credit card number), to
complete the process and send the info back to CiviCRM.
- customSubmitDataParams - any custom parameters passed by the user (see
below). You may need to the user to include an api key, etc.

See remoteform.stripe.js for an example.
### cfg.customSubmitDataParams

An object containing any data that is specific to your customSubmitDataFunc,
such as an api key, etc.
### cfg.css

Indicate classes that should be used on various parts of the form if you
want more control over look and feel. The defaults are designed to work
with bootstrap. If you are not using bootstrap, you may want to include
the remoteform.css file which tries to make things look nice with the
default classes.
#### cfg.css.SuccessMsg

Default: alert alert-success
#### cfg.css.FailureMsg

Default: alert alert-warning
#### cfg.css.button

Default: btn btn-info
#### cfg.css.form.

Default: rf-form
#### cfg.css.inputDiv

Default: form-group
#### cfg.css.checkDiv

Default: form-check
#### cfg.css.input

Default: form-control
#### cfg.css.checkInput

Default: form-check-input
#### cfg.css.textarea

Default: form-control
#### cfg.css.label

Default: rf-label
#### cfg.css.sr_only

Default: sr-only
#### cfg.css.checkLabel

Default: form-check-label
#### cfg.css.select

Default: custom-select
#### cfg.css.small

Default: text-muted form-text
## Functions related to building html widgets.

### createFieldDiv

```createFieldDiv(key, def, type, createFieldFunc, wrapFieldFunc)```

Create a single field with associated label wrapped in a div.

This function can be overridden using the createFieldDivFunc config
parameter.

#### Parameters:

- key - the unique field key
- type - the type of field to build
- createFieldFunc - the function to use to build the field, override
as needed. See createField for a model.
- wrapFieldFunc - the function to use to build the div around the field,
override as needed. See wrapField as a model.

#### Returns:

An HTML entity including both a field label and field.

If you don't override this function, it will do the following:

```
var field = createFieldFunc(key, def, type);
if (field === null) {
return null;
}
return wrapFieldFunc(key, def, field);
}
```

By overriding the class, you can do things like create your own
createFieldFunc or wrapFieldFunc, then simply call createFieldDiv but pass
it your own function names instead of the default ones. Or you can pick
out a field type you want to cusotmize or even a field key and only change
the behavior for that one.
### getType

```getType(def)```

If you pass in a field definition provided by CiviCRM, this function
returns an html input type, working around some CiviCRM idiosyncracies.

#### Parameters
- def - a CiviCRM provided field definition object.

#### Returns
A string field type
### createField

```createField(key, def, type)```

Return an HTML entity that renders the given field.

#### Parameters
- key - The unique string id for the field
- def - The CiviCRM provided field definition object.
- type - The string type for the field.

### Returns

HTML entity.
### wrapField

```wrapField(key, def, field)```

Return an HTML entity that includes both the given field and a label.

#### Parameters
- key - The unique string id for the field
- def - The CiviCRM provided field definition object.
- field - An HTML entity with the field 

### Returns

HTML entity.
Check if this is an "other amount" price set

Some price set options should only be displayed if the user has
clicked the "other amount" option. Unfortunately, it's hard to
tell if an option is an other amount option. With normal price sets
the option has the name "Other_Amount" - however, if you have a
contribution page and you are not using price sets, then it's called
Contribution_Amount.

This function return true if we think this is an other amount
option or false otherwise.
Checkbox and Radio collections.
