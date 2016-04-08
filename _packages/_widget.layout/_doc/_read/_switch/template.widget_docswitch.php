<widget:docswitch
	category= "Документы"
	name	= "Документы с картинкой"
    note	= "Документы с меню, боковой картинкой и текстом"
    cap		= "documents"
	exec	= "widget:docswitch:[id]=[@data.selector];options:[data]"
>
<cfg:data.selector	name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.elmSize	name = 'Размер изображения (ШxВ)' default = '550x300' />

<wbody>

<? $options = $data['options'] ?>

	<module:read:$options[folder] />
    <module:doc:read:docswitch @="$data" />

</wbody>

</widget:docswitch>

<?
//	+function phone_doc_read_docswitch
function phone_doc_read_docswitch($db, $val, $search)
{
	$options	= $search['options'];
	$elmSize	= '360x200';
	list($w, $h)= explode('x', $elmSize);
	$w1	= $w;
?>
<div class="docSwitchPhone widgetDocSwitch clearfix">
<? $db->seek(0); while($data = $db->next())
{
	$id		= $db->id();
	$link	= $db->url();
	$menu	= doc_menu($id, $data);
	$url	= getURL($link);
?>
<div class="itemElm">
	<div class="image">
        <h2 class="itemTitle">
        	<a href="{$url}">{$data[title]}</a>
         </h2>
    	<module:doc:titleImage +=":$id"
        	clip		= "$elmSize"
            adminMenu	= "$menu"
            property.href= "$url"
        />
    </div>
	<div class="content">
<module:doc:editable += ":$id" default="@" adminMenu="$menu">
{!$data|docNote}
</module:doc:editable>
    </div>
</div>
<? } ?>
</div>
<? } ?>

<?
//	+function doc_read_docswitch
function doc_read_docswitch($db, $val, $search)
{
	$options	= $search['options'];
	$elmSize	= $options['elmSize'];
	list($w, $h)= explode('x', $elmSize);
	$w1	= $w+10;
?>

<module:script:CrossSlide />

<link rel="stylesheet" type="text/css" href="css/widgetdocswitch.css">
<script src="script/widgetdocswitch.js"></script>

<div class="widgetDocSwitch clearfix">

<div class="widgetDocSwitchMenu clearfix">
<? while($data = $db->next())
{
	$id		= $db->id();
	$link	= $db->url();
	$ix		= $db->ndx-1;
	$dragID	= docDraggableID($id, $data);
	$note	= docNote($id, $data);
?>
<a href="{{url:$link}}" index="{$ix}" {!$dragID}>{$data[title]}</a>
<? } ?>
</div>

<div class="widgetDocSwitchHolder CrossFadeEx slider" style="height: {$h}px">
<? $db->seek(0); while($data = $db->next())
{
	$id		= $db->id();
	$link	= $db->url();
	$menu	= doc_menu($id, $data);
?>
<div class="itemElm">
	<div class="image">
    	<module:doc:titleImage +=":$id"
        	clip		= "$search[options][elmSize]"
            adminMenu	= "$menu"
        />
    </div>
	<div class="content" style="padding-right:{$w1}px">
<module:doc:editable += ":$id" default="@" >
{!$data|docNote}
</module:doc:editable>
    </div>
</div>
<? } ?>
</div>

</div>

<? return $search; } ?>
