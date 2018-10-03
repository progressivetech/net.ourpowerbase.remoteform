<table>
  <tr id="remoteform-profile-enable-tr">
    <td class="label"><label>{ts}Remote Form{/ts}</label></td>
    <td>
      {$form.remoteform_profile_enable.html}
      {$form.remoteform_profile_enable.label}
      <div class="description">{ts}If enabled, you will be able to allow people to fill out this profile from your on web site by including a few lines of javascript code.{/ts}</div>
      <div id="remoteform-code-to-copy"><pre>{$remoteform_code}</pre></div>
    </td>
  </tr>
</table>

{literal}

  <script type="text/javascript">

    // Insert our javascript at the bottom of the advanced section.
    CRM.$('tr#remoteform-profile-enable-tr').insertAfter('tr.crm-uf-advancesetting-form-block-is_uf_link');

    // Handle where or not to display the javascript code.
    function remoteform_handle_code_display() {
      if (CRM.$('#remoteform_profile_enable').is(':checked')) {
        CRM.$('#remoteform-code-to-copy').show();
      }
      else {
        CRM.$('#remoteform-code-to-copy').hide();
      }
    }

    // Handle display on page load and also anytime it is clicked.
    remoteform_handle_code_display();
    CRM.$('#remoteform_profile_enable').click(function() {
      remoteform_handle_code_display();
    });

  </script>
{/literal}

