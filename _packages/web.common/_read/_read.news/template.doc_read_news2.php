<?
function doc_read_news2_beginCache(&$db, $val, &$search)
{
	return userID()?'':hashData($search);
}
function doc_read_news2(&$db, $val, &$search)
{
	$search[':sortable']	= array(
		'axis'	=> 'y',
	);
?>
<link rel="stylesheet" type="text/css" href="css/news.css">
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	$link	= getURL($url);
	$date	= $data['datePublish'];
	if ($date) $date	= '<date>' . date('d.m.Y', $date) . '</date>';
	$menu	= doc_menu($id, $data, '+sortable');
	$note	= docNote($data);
?>
<div class="news2">
	{{doc:titleImage:$id:mask=mask:design/news2Mask.png;hasAdmin:true;adminMenu:$menu;property.href:$link}}
	{!$date}
    <blockquote>
	    <h2><a href="{{url:$url}}" title="{$data[title]}">{$data[title]}</a></h2>
    </blockquote>
</div>
<? } ?>
<? return $search; } ?>