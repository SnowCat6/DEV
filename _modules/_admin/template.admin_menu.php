<?
function admin_menu($eventName, &$data)
{
	ob_start();
	$menu	= array();
	event($eventName, $menu);
	$p		= ob_get_clean();
	
	foreach($menu as $name => &$data)
	{
		if (is_array($data)) continue;
	
		$url= $data;
		$id	= NULL;
		list($name, $id) = explode('#', $name, 2);
		if ($id) $id = " id=\"$id\"";
		if ($url) echo "<div><a href=\"$url\"$id>$name</a></div>";
		else echo "<h2>$name</h2>";
	}
	
	foreach($menu as $name => &$data)
	{
		if (!is_array($data)) continue;
	
		echo '<div>';
		foreach($data as $name => &$url)
		{
			$id	= NULL;
			list($name, $id) = explode('#', $name, 2);
			if ($id) $id = " id=\"$id\"";
			
			if (!$url) echo "<h2>$name</h2>";
			else{
				if (!is_array($url)) echo "<a href=\"$url\"$id>$name</a> ";
				else{
					$property		= array();
					$property['id']	= $id;
					foreach($url as $n => $value){
						$value	= htmlspecialchars($value);
						if ($value) $property[$n]	= "$n=\"$value\"";
					}
					$property	= implode(' ', $property);
					echo "<a $property>$name</a>";
				}
			}
		}
		echo '</div>';
	}
	echo $p;
}
?>

<?
//	+function admin_settingsMenu
function admin_settingsMenu(&$eventName, &$settings)
{
	ob_start();
	event($eventName, $settings);
	$p		= ob_get_clean();

?>
<table cellpadding="0" cellspacing="0">
<?	foreach($settings as $name => $value)
{
	$id			= $value['id'];
	if (!$id) $id = md5(rand());
	if ($value['disable']) $name = "<s>$name</s>";
?>
<tr>
	<th valign="top"><label for="{$id}">{!$name}</label></th>
    <td valign="top">
<? if ($value['value']){ ?>
    	<input type="hidden" name="{$value[name]}" value="{$value[default]}" />
    	<input type="checkbox" id="{$id}" name="{$value[name]}" value="{$value[value]}" {checked:$value[checked]} />
<? } ?>
     </td>
</tr>
<?	} ?>
</table>
<?
	echo $p;
} ?>