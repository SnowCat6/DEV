<?
function doc_read_news(&$db, &$search, &$data)
{
	if (!$db->rows()) return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
	
	$date	= makeDate($data['datePublish']);
	if ($date){
		$date	= date('d.m.Y', $date);
		$date	= "<b>$date</b> ";
	}
?>
<p>
{beginAdmin}
{!$date}<a href="{$url}">{$data[title]}</a>
{endAdminTop}
</p>
<? } ?>
<? return $search; } ?>
