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

<? function doc_read_docswitch($db, $val, $search)
{
	$options	= $search['options'];
	$elmSize	= $options['elmSize'];
	list($w, $h)= explode('x', $elmSize);
?>

<module:script:CrossSlide />

<link rel="stylesheet" type="text/css" href="css/widgetdocswitch.css">
<script src="script/widgetdocswitch.js"></script>

<div class="widgetDocSwitch clearfix">

<div class="widgetDocSwitchMenu clearfix">
<? while($data = $db->next()){
	$link	= $db->url();
	$ix		= $db->ndx-1;
?>
<a href="{{url:$link}}" index="{$ix}">{$data[title]}</a>
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
	<div class="content" style="padding-right:{$w}px">
{beginAdmin}
{document}
{endAdmin}
    </div>
</div>
<? } ?>
</div>

</div>

<? return $search; } ?>
