<?
function prop_read($db, $val, $data)
{
	$props = module("prop:get:$data[id]:$data[group]");
	if (!$props) return;
	
	$fn = getFn("prop_read_$val");
	if ($fn) return $fn(&$props);

	echo '<ul>';
	foreach($props as $name => $data)
	{
		if ($name[0] == ':' || $name[0] == '!') continue;
		
		$note	= htmlspecialchars($data['note']);
		$name	= htmlspecialchars($name);
		$prop	= htmlspecialchars($data['property']);
		
		if ($prop){
			$prop	= propFormat($prop, $data, true);
			echo "<li title=\"$note\">$name: <b>$prop</b></li>";
		}else{
			echo "<li title=\"$note\">$name</li>";
		}
	}
	echo '</ul>';
}
?>
<?
function prop_read_plain(&$props)
{
	$split = '';
	foreach($props as $name => $data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		$prop	= htmlspecialchars($data['property']);
		if (!$prop) continue;
		echo $split, $prop;
		$split = ' ';
	}
}
?>