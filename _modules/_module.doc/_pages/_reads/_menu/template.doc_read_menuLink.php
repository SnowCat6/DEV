<?
function doc_read_menuLink(&$db, $val, &$search)
{
	$split = ' id="first"';
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
		$class	= currentPage() == $id?' class="current"':'';
?>
<a href="{$url}" {!$split} title="{$data[title]}">{$data[title]}</a>
<? $split = ''; } ?>
<? return $search; } ?>