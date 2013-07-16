<? function user_all(&$db, $val, $data){?>
<? $module_data = array(); $module_data[] = "Список пользователей"; moduleEx("page:title", $module_data); ?>
<?
	if (!hasAccessRole('admin,developer,accountManager')){
		module('message:error', 'Недостаточно прав доступа');
		module('display:message');
		return;
	}
	
	$deleteUsers = getValue('deleteUsers');
	if (is_array($deleteUsers)){
		foreach($deleteUsers as $userID){
			module("user:update:$userID:delete");
		}
	}
	
	module('script:ajaxLink');
	module('script:ajaxForm');
?>
<? module("display:message"); ?>
<form action="<? module("getURL:user_all"); ?>" method="post" class="admin ajaxForm ajaxReload">
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
$db->open();
while($data = $db->next()){
	$id			= $db->id();
	$userRoles	= explode(',', $data['access']);
	foreach($userRoles as $ndx => &$name){
		if (@$roles[$name]) $name = $roles[$name];
	};
	$access = implode(', ', $userRoles);
	$date	= makeDate($data['dateCreate']);
	if ($date) $date = date('d.m.Y', $date);
?>
<tr>
    <td><? if (userID() != $id){ ?><input name="deleteUsers[]" type="checkbox" value="<? if(isset($id)) echo htmlspecialchars($id) ?>" /><? } ?></td>
    <td><? if(isset($id)) echo htmlspecialchars($id) ?></td>
    <td><a href="<? module("getURL:user_edit_$id"); ?>" id="ajax"><? module('user:name:full', $data)?></a></td>
    <td><? if(isset($access)) echo htmlspecialchars($access) ?></td>
    <td><? if(isset($date)) echo htmlspecialchars($date) ?></td>
</tr>
<? } ?>
</table>
<p><input type="submit" class="button" value="Удалить выделенных пользователей" /> <a href="<? module("getURL:user_add"); ?>" id="ajax" >Создать нового</a></p>
</form>
<? } ?>