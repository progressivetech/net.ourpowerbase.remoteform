<table>
  <tr id="remoteform-contribution-page-enable-tr">
    <td class="label"><label>{ts}Remote Form{/ts}</label></td>
    <td>
      {$form.remoteform_contribution_page_enable.html}
      {$form.remoteform_contribution_page_enable.label}
      <div class="description">{ts}If enabled, you will be able to allow people to make contributions via this page from your on web site by including a few lines of javascript code.{/ts}</div>
      <div id="remoteform-code-to-copy"><pre>{$remoteform_code}</pre></div>
    </td>
  </tr>
</table>

{literal}

  <script type="text/javascript">

    // Insert our javascript after the is active checkbox.
    CRM.$('tr#remoteform-contribution-page-enable-tr').insertAfter('tr.crm-contribution-contributionpage-settings-form-block-is_active');

    // Handle where or not to display the javascript code.
    function remoteform_handle_code_display() {
      if (CRM.$('#remoteform_contribution_page_enable').is(':checked')) {
        CRM.$('#remoteform-code-to-copy').show();
      }
      else {
        CRM.$('#remoteform-code-to-copy').hide();
      }
    }

    // Handle display on page load and also anytime it is clicked.
    remoteform_handle_code_display();
    CRM.$('#remoteform_contribution_page_enable').click(function() {
      remoteform_handle_code_display();
    });

  </script>
{/literal}

