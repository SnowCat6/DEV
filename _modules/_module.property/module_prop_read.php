<?
function prop_read($db, $val, $data)
{
	$prop = module("prop:get:$data[id]:$data[group]");
	if (!$prop) return;

	echo '<ul>';
	foreach($prop as $name => $data)
	{
		if ($name[0] == ':') continue;
		$name	= htmlspecialchars($name);
		$prop	= htmlspecialchars($data['property']);
		if ($prop){
			$prop	= propFormat($prop, $data, true);
			echo "<li>$name: <b>$prop</b></li>";
		}else{
			echo "<li>$name</li>";
		}
	}
	echo '</ul>';
}
?>