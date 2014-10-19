<? function admin_panel_tools(&$data)
{
	if (!hasAccessRole('admin,developer,writer,manager,SEO') &&	!access('use', 'adminPanel')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="25%" valign="top" class="adminToolMenu">
<h2 class="ui-state-default">Документы</h2>
{{admin:menu:admin.tools.add}}
    </td>
    <td width="25%" valign="top" class="adminToolMenu">
<h2 class="ui-state-default">Изменить</h2>
{{admin:menu:admin.tools.edit}}
    </td>
    <td width="25%" valign="top" class="adminToolMenu">
<h2 class="ui-state-default">Настроить</h2>
{{admin:menu:admin.tools.settings}}
   </td>
    <td width="25%" align="right" valign="top" class="adminToolMenu">
<h2 class="ui-state-default">Обслуживание</h2>
{{admin:menu:admin.tools.service}}
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>
