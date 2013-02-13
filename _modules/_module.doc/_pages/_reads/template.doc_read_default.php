<?
function doc_read_default(&$db, $val, &$search){
	if (!$db->rows())  return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
?>
{beginAdmin}
<div><a href="{$url}">{$data[title]}</a></div>
{endAdminTop}
<? } ?>
<? return $search; } ?>
