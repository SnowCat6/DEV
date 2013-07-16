<? function site_settings_packages_update(&$ini)
{
	if (!hasAccessRole('developer')) return;

	$packages	= &$ini[':packages'];
	if (!is_array($packages)) return;
	
	$files	= getDirs('_packages');
	foreach($packages as $name => &$path){
		if ($path) $path = $files[$name];
		else unset($packages[$name]);
	}
}
function site_settings_packages($ini){
	if (!hasAccessRole('developer')) return;
?>
<? foreach(getDirs('_packages') as $name => $path){
	$class	= isset($ini[':packages'][$name])?' checked="checked"':'';
?>
<div><label>
<input type="hidden" name="settings[:packages][<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="" <? if(isset($class)) echo $class ?> />
<input type="checkbox" name="settings[:packages][<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="<? if(isset($name)) echo htmlspecialchars($name) ?>" <? if(isset($class)) echo $class ?> /><? if(isset($name)) echo htmlspecialchars($name) ?>
</label></div>
<? } ?>
<? return '8-Модули'; } ?>