<div class="help">
<p>Remote Forms is an extension that allows you to submit data directly from remote sites.</p>

<p>To use Remote Forms, you must list the addresses of all sites that will be submitting data. Only data submitted from sites listed below will be accepted.</p>

<p>Once you have specified the remote sites below, you can configure the Profile or Contribution page you want to display.</p>
</div>

<div class="crm-block crm-form-block">
{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}


<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
</div>
