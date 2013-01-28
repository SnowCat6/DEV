<?
function links_get(&$db, $val)
{
	$res = array();
	makeSQLValue($val);
	$db->open("nativeURL = $val");
	while($data = $db->next()){
		$res[$data['link']] = $data['link'];
	}
	return $res;
}
?>