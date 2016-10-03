<? function user_all($val, $data)
{
	if (!hasAccessRole('admin,developer,accountManager'))
		return;
	
	$deleteUsers = getValue('deleteUsers');
	if (is_array($deleteUsers)){
		foreach($deleteUsers as $userID){
			module("user:update:$userID:delete");
		}
	}
	
	module('script:ajaxLink');
	module('script:ajaxForm');
	
	$ini			= getIniValue(':user');
	$userSettings	= getValue('userSettings');
	if (is_array($userSettings))
	{
		$ini	= $userSettings;
		setIniValue(':user', $ini);
	}
	moduleEx("admin:tabUpdate:user_tab", $ini);
?>
{{ajax:template=ajax_edit}}
{{display:message}}
{{page:title=Список пользователей}}
<form action="{{getURL:user_all}}" method="post" class="admin ajaxForm ajaxReload">
	<module:admin:tab:user_tab @="$ini" />
</form>
<? } ?>


<?
//	+function user_tab_all
function user_tab_all($ini)
{
	$id	= userID();
?>
<p>
	<a href="{{getURL:user_edit_$id}}" id="ajax_edit">Перональные настройки</a>
</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th>ID</th>
    <th>Логин</th>
    <th>Роль</th>
    <th>Регистрация</th>
</tr>
<?
$roles	= getCacheValue('localUserRoles');
$db		= user::find();
while($data = $db->next())
{
	$id			= $db->id();
	$userRoles	= explode(',', $data['access']);
	foreach($userRoles as $ndx => &$name){
		if ($roles[$name]) $name = $roles[$name];
	};
?>
<tr>
    <td>
<? if (userID() != $id){ ?>
	<input name="deleteUsers[]" type="checkbox" value="{$id}" />
<? } ?>
    </td>
    <td>{$id}</td>
    <td><a href="{{getURL:user_edit_$id}}" id="ajax">
	<? module('user:name:full', $data)?>
    </a></td>
    <td>{$userRoles|implode:, }</td>
    <td>{$data[dateCreate]|date:%d.%m.%Y}</td>
</tr>
<? } ?>
</table>

<? return "Список пользователей"; } ?>


<?
//	+function user_tab_settings
function user_tab_settings($ini){?>
<table border="0" cellspacing="0" cellpadding="2">
  <tbody>
    <tr>
      <td>Запретить регистрацию</td>
      <td>
<input type="hidden" name="userSettings[denyRegisterNew]" value="0" />
<input type="checkbox" name="userSettings[denyRegisterNew]" value="1" class="checkbox" id="denyRegisterNew" {checked:$ini[denyRegisterNew]} />
      </td>
    </tr>
  </tbody>
</table>

<? return 'Настройки'; } ?>



<?
//	+function user_tab_new_update
function user_tab_new_update($ini)
{
	if (!testValue('userSave')) return;

	$data = array();
	moduleEx('admin:tabUpdate:user_property', $data);
	$iid = moduleEx("user:update::add", $data);
	if ($iid) module('message', 'Пользователь создан, можете добавить еще.');
}

//	+function user_tab_new
function user_tab_new($ini)
{
	$data = array();
	$data['access']	= implode(',', getValue('userAccess'));
	if (!$data['access']) $data['access']	= 'user';
?>
<input type="hidden" name="userForm" />
<? moduleEx('admin:tab:user_property::userSave:Добавить пользователя', $data)?>

<? return 'Добавить нового'; } ?>