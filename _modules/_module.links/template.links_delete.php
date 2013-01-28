<?
function links_delete(&$db, $val)
{
	$db->deleteByKey('nativeURL', $val);
}
?>