<?
function user_property_access_update(&$data){
	if (!hasAccessRole('admin,developer,accountManager')) return;

	$roles = getValue('userAccess');
	if (!is_array($roles)) return;
	
	$access = array();
	$localUserRoles = getCacheValue('localUserRoles');
	foreach($roles as $role){
		 if ($role && isset($localUserRoles[$role])) $access[] = $role;
	}
	$data['access'] = implode(',', $access);
}
?>
<? function user_property_access($data){ ?>
<?
	if (!hasAccessRole('admin,developer,accountManager')) return;
	$db		= module('user', $data);
	$roles	= getCacheValue('localUserRoles');
?>
<table border="0" cellspacing="0" cellpadding="2">
<?
	$userRoles	= explode(',', $data['access']);
	foreach($roles as $role => $name){
		$class = is_int(array_search($role, $userRoles))?' checked="checked"':'';
?>
<tr>
    <td>
    <input name="userAccess[{$role}]" type="hidden" value="" />
    <input name="userAccess[{$role}]" type="checkbox" id="role_{$role}" {!$class} value="{$role}" />
    </td>
    <td><label for="role_{$role}">{$name}</label></td>
</tr>
<? } ?>
</table>
<? return '20-Права доступа'; } ?>