<?
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
	
	$date	= makeDate($data['datePublish']);
	if ($date){
		$date	= date('d.m.Y', $date);
		$date	= "<b>$date</b> ";
	}
?>
<p>
{beginAdmin}
{beginCompile:news3}
<a href="{!$url}"><? displayThumbImageMask($folder = docTitleImage($id), 'design/maskNews.png') ?></a>
{endCompile:news3}
<date>{!$date}</date>
<a href="{$url}">{$data[title]}</a>
<blockquote>{!$note}</blockquote>
{endAdminTop}
</p>
<? } ?>
</div>
<? return $search; } ?>
