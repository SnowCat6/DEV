<?
function doc_read_news3_beginCache(&$db, $val, &$search){
	if (userID()) return;
	return hashData($search);
}
function doc_read_news3_before(&$db, $val, &$search){
	$search[':order'] = '-date,sort';
}
function doc_read_news3(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
?>
<div class="news3">
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$note	= docNote($data);
	
	$date	= $data['datePublish'];
	if ($date){
		$date	= date('d.m.Y', $date);
		$date	= "<span class='bold'>$date</span> ";
	}
?>
<div>
{beginAdmin}
{beginCompile:news3}
<a href="{!$url}">
{{doc:titleImage:$id=mask:design/maskNews.png}}
</a>
{endCompile}
<date>{!$date}</date>
<a href="{$url}">{$data[title]}</a>
<blockquote>{!$note}</blockquote>
{endAdminTop}
</div>
<? } ?>
</div>
<? return $search; } ?>
