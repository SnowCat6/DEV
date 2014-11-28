<? function user_all(&$db, $val, $data){?>
{{page:title=Список пользователей}}
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
{{display:message}}
<form action="{{getURL:user_all}}" method="post" class="admin ajaxForm ajaxReload">
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
	$date	= $data['dateCreate'];
	if ($date) $date = date('d.m.Y', $date);
?>
<tr>
    <td><? if (userID() != $id){ ?><input name="deleteUsers[]" type="checkbox" value="{$id}" /><? } ?></td>
    <td>{$id}</td>
    <td><a href="{{getURL:user_edit_$id}}" id="ajax"><? module('user:name:full', $data)?></a></td>
    <td>{$access}</td>
    <td>{$date}</td>
</tr>
<? } ?>
</table>
<p><input type="submit" class="button" value="Удалить выделенных пользователей" /> <a href="{{getURL:user_add}}" id="ajax" >Создать нового</a></p>
</form>
<? } ?>