<?
function links_delete(&$db, $val)
{
	$db->deleteByKey('nativeURL', $val);
	$a = array();
	setCacheValue('links', $a);
}
?>