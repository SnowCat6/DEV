<? function admin_panel_users(&$data)
{
	if (!hasAccessRole('admin,developer,accountManager')) return;

	if (is_array($userSettings = getValue('userSettings'))){
		$ini = getCacheValue('ini');
		$ini[':user'] = $userSettings;
		setIniValues($ini);
	};
	
	$id = userID();
	module('script:ajaxLink');
	module('script:ajaxForm');

	$ini	= getCacheValue('ini');
	@$deny	= $ini[':user']['denyRegisterNew'];
?><? module("display:message"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="50%" valign="top">
<div>
<a href="<? module("getURL:user_all"); ?>" id="ajax">Список пользователей</a>
<a href="<? module("getURL:user_edit_$id"); ?>" id="ajax">Перональные настройки</a>
</div>
<p><a href="<? module("getURL:user_add"); ?>" id="ajax" >Создать нового</a></p>
        </td>
        <td width="50%" valign="top">
<form method="post" action="#">
          <table border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td nowrap="nowrap"><label for="denyRegisterNew">Запретить регистрацию новых</label></td>
              <td>
<input type="hidden" name="userSettings[denyRegisterNew]" value="0" />
<input type="checkbox" name="userSettings[denyRegisterNew]" value="1" class="checkbox" id="denyRegisterNew" <?= $deny?' checked="checked"':''?> />
              </td>
            </tr>
          </table>
<p><input type="submit" class="ui-button ui-widget ui-state-default ui-corner-all" value="Сохранить" /></p>
        </form>
        </td>
        <td align="right" valign="top">
<a href="<? $module_data = array(); $module_data[] = "logout"; moduleEx("getURL", $module_data); ?>">Выход</a>
        </td>
  </tr>
</table>

<? return '150-Пользователи'; } ?>