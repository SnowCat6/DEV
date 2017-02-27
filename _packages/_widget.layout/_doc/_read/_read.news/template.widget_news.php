<widget:news1
	category= "Документы.Новости"
	name	= "Новости"
    note	= "Список документов с датой и текстом"
	exec	= "doc:read:widgetNews1=[@data.selector];max:[data.max]"
    cap		= "documents"
>
<cfg:data.selector	name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.max		name = 'Мксимальное количество' default = '3' />

<?
//	+function doc_read_widgetNews1
function doc_read_widgetNews1(&$db, $val, &$search){
?>
<link rel="stylesheet" type="text/css" href="css/widgetNews1.css">
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
?>
<div class="widgetNews1" {!$search[options][style]|style}>
    <p>
{beginAdmin}
        {!$data[datePublish]|date:%d %F %Y|tag:date}
        <a href="{$url}">{$data[title]}</a>
{endAdminTop}
    </p>
</div>
<? } ?>
<? return $search; } ?>

</widget:news1>


<widget:news2
	category= "Документы.Новости"
	name	= "Новости на фоне"
    note	= "Список документов с фоновой картинкой и текстом"
	exec	= "doc:read:widgetNews2=[@data.selector];max:[data.max];options:[data]"
    cap		= "documents"
>
<cfg:data.style.size	name = 'Размер изображения (ШхВ)' default = '325x215' />
<cfg:data.selector		name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.max			name = 'Мксимальное количество' default = '3' />

<?
//	+function doc_read_widgetNews2
function doc_read_widgetNews2(&$db, $val, &$search)
{
	$search[':sortable']	= array(
		'axis'	=> 'y',
	);
?>
<link rel="stylesheet" type="text/css" href="css/widgetNews2.css">
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	$link	= getURL($url);
	$menu	= doc_menu($id, $data, '+sortable');
?>
<div class="widgetNews2" {!$search[options][style]|style}>
	<module:doc:titleImage +=":$id"
    	clip = "$search[options][size]"
    	hasAdmin = "top"
        adminMenu = "$menu"
        property.href = "$link"
        />
    {!$data[datePublish]|date:%d %F %Y|tag:date}
    <blockquote>
	    <h2><a href="{{url:$url}}" title="{$data[title]}">{$data[title]}</a></h2>
    </blockquote>
</div>
<? } ?>
<? return $search; } ?>

</widget:news2>



<widget:news3
	category= "Документы.Новости"
	name	= "Новости с большой картинокой и текстом"
    note	= "Список документов с большой картинкой и текстом"
	exec	= "doc:read:widgetNews3=[@data.selector];max:[data.max];options:[data]"
    cap		= "documents"
>
<cfg:data.style.size	name = 'Размер изображения (ШхВ)' default = '325x215' />
<cfg:data.selector		name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.max			name = 'Мксимальное количество' default = '3' />

<?
//	+function doc_read_widgetNews3
function doc_read_widgetNews3(&$db, $val, &$search)
{
	$search[':sortable']	= array(
		'select'=> 'tbody',
		'axis'	=> 'y'
	);
	$imgWidth	= (int)$search['options']['size'];
?>
<link rel="stylesheet" type="text/css" href="css/news.css">
<link rel="stylesheet" type="text/css" href="css/widgetNews3.css">

<table class="widgetNews3" cellpadding="0" cellspacing="0" border="0" width="100%">
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	$link	= getURL($url);
	$menu	= doc_menu($id, $data, '+sortable');
	$note	= docNote($data, 500);
?>
<tr>
<th width="{$imgWidth}" style="max-width:{$imgWidth}px">
	<div class="image">
        <module:doc:titleImage +=":$id"
            clip = "$search[options][size]"
            hasAdmin = "top"
            adminMenu = "$menu"
            property.href = "$link"
            />
  		{!$data[datePublish]|date:%d %F %Y|tag:date}
    </div>
</th>
<td>
{beginAdmin:$menu}
    <h2><a href="{{url:$url}}" title="{$data[title]}">{$data[title]}</a></h2>
{endAdmin}
    <module:doc:editable +=":$id:note" default = "$note" />
</td>
</tr>
<? } ?>
</table>
<? return $search; } ?>
</widget:news3>



<widget:news4
	category= "Документы.Новости"
	name	= "Новости с картинокой и текстом"
    note	= "Список документов с картинкой и текстом"
	exec	= "doc:read:widgetNews4=[@data.selector];max:[data.max];options:[data]"
    cap		= "documents"
>
<cfg:data.style.size	name = 'Размер изображения (ШхВ)' default = '220x120' />
<cfg:data.selector		name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.max			name = 'Мксимальное количество' default = '3' />

<?
//	+function doc_read_widgetNews4
function doc_read_widgetNews4_before(&$db, $val, &$search){
	$search[':order'] = '-date,sort';
}
function doc_read_widgetNews4(&$db, $val, &$search){?>

<link rel="stylesheet" type="text/css" href="css/widgetNews4.css">
<? while($data = $db->next()){
	$id		= $db->id();
    $link	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
?>
<div class="widgetNews4">
	<div class="image">
        <module:doc:titleImage +=":$id"
            clip = "$search[options][size]"
            hasAdmin = "top"
            adminMenu = "$menu"
            property.href = "$link"
            />
    </div>
    <p>
        {!$data[datePublish]|date:%d %F %Y|tag:b|tag:date}
        <a href="{$link}">{$data[title]}</a>
    </p>
    {!$data|docNote|tag:blockquote}
</div>
<? } ?>
<? return $search; } ?>

</widget:news4>