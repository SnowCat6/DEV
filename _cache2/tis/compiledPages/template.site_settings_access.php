<? function site_settings_access_update(&$ini)
{
	if (!hasAccessRole('admin')) return;
	@removeEmpty($ini[':siteAccess']);
}?><? function site_settings_access($ini)
{
	if (!hasAccessRole('admin')) return;
	$roles	= getCacheValue('localUserRoles');
?>
<h2>Доступ к сайту только для:</h2>
<table border="0" cellspacing="0" cellpadding="2">
<?
	@$userRoles	= array_keys($ini[':siteAccess']);
	if (!is_array($userRoles)) $userRoles = array();
	foreach($roles as $role => $name){
		$class = is_int(array_search($role, $userRoles))?' checked="checked"':'';
?>
<tr>
    <td>
    <input name="settings[:siteAccess][<? if(isset($role)) echo htmlspecialchars($role) ?>]" type="hidden" value="" />
    <input name="settings[:siteAccess][<? if(isset($role)) echo htmlspecialchars($role) ?>]" type="checkbox" id="role_<? if(isset($role)) echo htmlspecialchars($role) ?>" <? if(isset($class)) echo $class ?> value="1" />
    </td>
    <td><label for="role_<? if(isset($role)) echo htmlspecialchars($role) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></label></td>
</tr>
<? } ?>
</table>
<? return 'Доступ к сайту'; } ?>