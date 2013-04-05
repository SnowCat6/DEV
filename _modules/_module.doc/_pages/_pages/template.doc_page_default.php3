<? function doc_page_default(&$db, &$menu, &$data){
	$id = $db->id();
?>
{beginAdmin}
{document}
{endAdminTop}
<?
$s = array();
$s['parent']	= $db->id();
$s['type']		= 'article';
module("doc:read:$data[doc_type]:news", $s);
?>
<? event('document.gallery',	$id)?>
<? event('document.feedback',	$id)?>
<? event('document.comment',	$id)?>
<? event('document.end',		$id)?>
<? } ?>