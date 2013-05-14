<?
function links_delete(&$db, $val)
{
	$db->deleteByKey('nativeURL', $val);

	$a = NULL;
	setCacheValue('links', $a);
	setCacheValue('nativeLink', $a);
}
?>