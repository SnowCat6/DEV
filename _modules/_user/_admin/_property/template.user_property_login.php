<?
function user_property_login_update(&$data)
{
	$db = module('user', $data);
	if ($db->id() && !testValue('doChangeLogin')) return;
	$userLogin = getValue('userLogin');
	if (!is_array($userLogin)) return;
	
	if (isset($userLogin['login'])) $data['login'] = $userLogin['login'];
	if (isset($userLogin['passw'])) $data['passw'] = $userLogin['passw'];
}
?>
<? function user_property_login($data)
{
	$db	= module('user', $data);
	$id	= $db->id();
	@$fields	= $data['fields'];
	@$loginType	= $fields['loginType'];
	if (userID() != $id && !hasAccessRole('admin,developer,accountManager')) return;
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
  <td valign="top">Основной логин</td>
  <td valign="top">Привязанные логины</td>
</tr>
<tr>
<td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap">Логин</td>
    <td width="100%"><input type="text" name="userLogin[login]" value="{$data[login]}" class="input w100" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Пароль</td>
    <td><input type="text" name="userLogin[passw]" value="{$data[passw]}" class="input w100" /></td>
  </tr>
<? if ($id){ ?>
  <tr>
    <td>&nbsp;</td>
    <td nowrap="nowrap">
    <input type="checkbox" name="doChangeLogin" id="doChangeLogin" value="1" />
    <label for="doChangeLogin">Изменить логин и пароль</label>
    </td>
  </tr>
<? } ?>
</table>
</td>
<td width="50%" valign="top">
<div>{{loginza:enter}}</div>
</td>
</tr>
</table>
<? return '1-Логин'; } ?>