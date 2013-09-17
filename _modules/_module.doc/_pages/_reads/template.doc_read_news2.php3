<?
function doc_read_news2_beginCache(&$db, $val, &$search)
{
	if (userID()) return;
	return hashData($search);
}
function doc_read_news2(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$note	= docNote($data);
?>
{beginAdmin}
<h3><a href="{$url}">{$data[title]}</a></h3>
<p>{!$note}</p>
{endAdminTop}
<? } ?>
<? return $search; } ?>
