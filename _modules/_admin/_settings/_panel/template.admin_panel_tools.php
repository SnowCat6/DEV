<? function admin_panel_tools(&$data)
{
	if (!hasAccessRole('admin,developer,writer,manager,SEO') &&	!access('use', 'adminPanel')) return;
?>
<module:script:jq />
<script src="../../script/admin.js"></script>

<link rel="stylesheet" type="text/css" href="../../css/admin.css" />

<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="40%" valign="top" class="adminToolMenu">
    	<h2 class="ui-state-default">Документы</h2>
     </td>
    
    <td width="20%" valign="top" class="adminToolMenu">
	    <a href="#tabAdminTools" class="adminTabSelector current">
            <h2 class="ui-state-default">Изменить</h2>
        </a>
    </td>
    <td width="20%" valign="top" class="adminToolMenu">
        <a href="#tabAdminSettings" class="adminTabSelector">
        	<h2 class="ui-state-default">Настроить</h2>
        </a>
    </td>
    <td width="20%" align="right" valign="top" class="adminToolMenu">
        <a href="#tabAdminService" class="adminTabSelector">
        	<h2 class="ui-state-default">Обслуживание</h2>
        </a>
    </td>
  </tr>
  <tr>
    <td width="25%" valign="top" class="adminToolMenu">
        {{admin:menu:admin.tools.add}}
    </td>
    <td colspan="3" valign="top" class="adminToolMenu">
<div id="tabAdminTools" class="adminTabContent">
    <div class="adminTabLeft">
          {{admin:menu:admin.tools.edit}}
    </div>
    <div  class="adminTabRight">
        {{admin:menu:admin.tools.edit2}}
    </div>
</div>
<div id="tabAdminSettings" class="adminTabContent">
    <div class="adminTabLeft">
        {{admin:menu:admin.tools.settings}}
    </div>
    <div  class="adminTabRight">
        {{admin:menu:admin.tools.settings2}}
    </div>
</div>
<div id="tabAdminService" class="adminTabContent">
    <div class="adminTabLeft">
        {{admin:menu:admin.tools.service}}
    </div>
    <div  class="adminTabRight">
        {{admin:menu:admin.tools.service2}}
    </div>
</div>
    </td>
  </tr>
</table>
<? return '1-Инструменты'; } ?>
