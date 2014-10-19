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
		foreach($data as $name => &$url){
			$id	= NULL;
			list($name, $id) = explode('#', $name, 2);
			if ($id) $id = " id=\"$id\"";
			if ($url) echo "<a href=\"$url\"$id>$name</a> ";
			else echo "<h2>$name</h2>";
		}
		echo '</div>';
	}
	echo $p;
}
?>