<? function widget_siteLayout3($id, $data){ ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="{$data[class]}">
  <tbody>
    <tr>
      <td valign="top" style="width: {$data[widthLeft]}; min-width: {$data[widthLeft]}; padding-right: {$data[padding]}" class="siteLayoutLeft">
          {{holder:$id.layoutLeft}}
      </td>
      <td valign="top" class="siteLayout">
          {{holder:$id.layout}}
      </td>
      <td valign="top" style="width: {$data[widthRight]}; min-width: {$data[widthRight]}; padding-left: {$data[padding]}" class="siteLayoutRight">
          {{holder:$id.layoutLeft}}
      </td>
    </tr>
  </tbody>
</table>


<? } ?>