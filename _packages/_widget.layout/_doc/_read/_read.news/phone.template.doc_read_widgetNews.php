<?
//	+function phone_doc_read_widgetNews3
function phone_doc_read_widgetNews3($db, $val, $search)
{
	$search[':sortable']	= array(
		'select'=> 'tbody',
		'axis'	=> 'y'
	);
	$search['options']['size']	= '360x240';
	$imgWidth	= (int)$search['options']['size'];
?>
<link rel="stylesheet" type="text/css" href="css/news.css">
<link rel="stylesheet" type="text/css" href="css/widgetNews3.css">

<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	$link	= getURL($url);
	$menu	= doc_menu($id, $data, '+sortable');
	$note	= docNote($data, 500);
?>
    <h2><a href="{{url:$url}}" title="{$data[title]}">{$data[title]}</a></h2>
	<div class="image">
        <module:doc:titleImage +=":$id"
            clip = "$search[options][size]"
            hasAdmin = "top"
            adminMenu = "$menu"
            property.href = "$link"
            />
  		{!$data[datePublish]|date:%d %F %Y|tag:date}
    </div>
    <module:doc:editable +=":$id:note" default = "$note" adminMenu="$menu" />
<? } ?>
<? return $search; } ?>
