# net.ourpowerbase.remoteForm

![Screenshot](/images/screenshot.png)

RemoteForm allows you to place a few lines of javascript on a remote (or same) site to your CiviCRM installation.

e.g.:

    <script src="https://checkout.stripe.com/checkout.js"></script>
    <script src="https://demo.loc.cx/sites/all/extensions/jssubmit/jssubmit.js"></script>

    <div id="jsSubmitFields"></div>
    <button id="jsSubmitButton">Purchase</button>
    <script>
    var jsSubmitParams = {
      onError: console.log,
      onSuccess: console.log,
      apiKey: 'pk_test_6pRNASCoBOKtIshFeQd4XMUh',
      url: 'https://demo.loc.cx/civicrm/ssp',
      fields: [
        {
        'id': 'jsSubmitFirstName',
        'label': 'First Name',
        'type': 'text',
        },
        {
        'id': 'jsSubmitLastName',
        'label': 'Last Name',
        'type': 'text',
        },
        {
        'id': 'jsSubmitAmount',
        'label': 'Amount',
        'type': 'text',
        }
      ],
    }

    jsSubmitInit();
    jsSubmitCreateFields();

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM (4.7)

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl net.ourpowerbase.jssubmit@https://github.com/FIXME/net.ourpowerbase.jssubmit/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/net.ourpowerbase.jssubmit.git
cv en jssubmit
```

## Usage

(* FIXME: Where would a new user navigate to get started? What changes would they see? *)

## Known Issues

(* FIXME *)
