<?
function doc_read_news2(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$note	= makeNote($data['originalDocument']);
?>
<h3>
{beginAdmin}
<a href="{$url}">{$data[title]}</a>
{endAdminTop}
</h3>
<p>{!$note}</p>
<? } ?>
<? return $search; } ?>
