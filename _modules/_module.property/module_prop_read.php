<?
function prop_read($db, $val, $data)
{
	$prop = module("prop:get:$data[id]:$data[group]");
	if (!$prop) return;

	echo '<ul>';
	foreach($prop as $name => $data)
	{
		if ($name[0] == ':') continue;
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