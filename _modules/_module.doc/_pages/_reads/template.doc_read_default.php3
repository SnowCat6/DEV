<?
function doc_read_default(&$db, $val, &$search){
	if (!$db->rows())  return $search;
?>
<? while($data = $db->next()){
	$fn		= getFn("doc_read_$data[doc_type]_$data[template]");
	if ($fn){
		$fn($db, &$val, &$search);
		continue;
	}
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
?>
{beginAdmin}
<div><a href="{$url}">{$data[title]}</a></div>
{endAdminTop}
<? } ?>
<? return $search; } ?>
