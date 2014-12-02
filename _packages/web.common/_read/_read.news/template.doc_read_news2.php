<? function doc_read_news2(&$db, $val, &$search)
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
	$date	= date('d.m.Y', $data['datePublish']);
	$menu	= doc_menu($id, $data, '+sortable');
	$note	= docNote($data);
?>
<div class="news2">
	{{doc:titleImage:$id:mask=mask:design/news2Mask.png;hasAdmin:true;adminMenu:$menu;property.href:$link}}
	<date>{$date}</date>
    <blockquote>
	    <h2><a href="{{url:$url}}" title="{$data[title]}">{$data[title]}</a></h2>
    </blockquote>
</div>
<? } ?>
<? return $search; } ?>