<?
function prop_read($db, $val, $data)
{
	$prop = module("prop:get:$data[id]:$data[group]");
	if (!$prop) return;

	echo '<ul>';
	foreach($prop as $name => $data){
		$name = htmlspecialchars($name);
		$prop = htmlspecialchars($data['property']);
		echo "<li>$name: <b>$prop</b></li>";
	}
	echo '</ul>';
}
?>