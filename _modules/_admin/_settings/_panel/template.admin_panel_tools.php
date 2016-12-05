<? function admin_panel_tools(&$data)
{
	if (!hasAccessRole('admin,developer,writer,manager,SEO') &&	!access('use', 'adminPanel')) return;
?>
<module:script:jq />
<script src="../../script/admin.js"></script>
<link rel="stylesheet" type="text/css" href="../../css/admin.css" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="40%" valign="top"> {{admin:menu:admin.tools.add}} </td>
    <td width="60%" valign="top"><table width="100%">
        <tr>
          <td width="20%" valign="top" class="adminToolMenu"><a href="#tabAdminTools" class="adminTabSelector current">
            <h2 class="ui-state-default">Изменить</h2>
            </a></td>
          <td width="20%" valign="top" class="adminToolMenu"><a href="#tabAdminSettings" class="adminTabSelector">
            <h2 class="ui-state-default">Настроить</h2>
            </a></td>
          <td width="20%" align="right" valign="top" class="adminToolMenu"><a href="#tabAdminService" class="adminTabSelector">
            <h2 class="ui-state-default">Обслуживание</h2>
            </a></td>
        </tr>
        <tr>
          <td colspan="3" valign="top" class="adminToolMenu"><div id="tabAdminTools" class="adminTabContent">
              <div class="adminTabLeft"> {{admin:menu:admin.tools.edit}} </div>
              <div  class="adminTabRight"> {{admin:menu:admin.tools.edit2}} </div>
            </div>
            <div id="tabAdminSettings" class="adminTabContent">
              <div class="adminTabLeft"> {{admin:menu:admin.tools.settings}} </div>
              <div  class="adminTabRight"> {{admin:menu:admin.tools.settings2}} </div>
            </div>
            <div id="tabAdminService" class="adminTabContent">
              <div class="adminTabLeft"> {{admin:menu:admin.tools.service}} </div>
              <div  class="adminTabRight"> {{admin:menu:admin.tools.service2}} </div>
            </div></td>
        </tr>
      </table></td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>
