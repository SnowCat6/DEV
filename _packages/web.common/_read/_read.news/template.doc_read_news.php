<?
function doc_read_news_beginCache(&$db, $val, &$search)
{
	return userID()?'':hashData($search);
}
function doc_read_news(&$db, $val, &$search)
{
	$search[':sortable']	= array(
		'select'=> 'tbody',
		'axis'	=> 'y'
	);
?>
<link rel="stylesheet" type="text/css" href="css/news.css">
<table class="news" cellpadding="0" cellspacing="0" border="0" width="100%">
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	$link	= getURL($url);
	$date	= date('d.m.Y', $data['datePublish']);
	$menu	= doc_menu($id, $data, '+sortable');
	$note	= docNote($data, 500);
?>
<tr>
<th>
    {{doc:titleImage:$id:mask=mask:design/news2Mask.png;hasAdmin:true;adminMenu:$menu;property.href:$link}}
</th>
<td>
{beginAdmin:$menu}
    <h2><a href="{{url:$url}}" title="{$data[title]}">{$data[title]}</a></h2>
{endAdmin}
    {{doc:editable:$id:note=default:$note}}
</td>
</tr>
<? } ?>
</table>
<? return $search; } ?>