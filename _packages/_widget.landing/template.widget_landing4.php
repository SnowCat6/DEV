<widget:landing4
    category= 'Лендинг'
    name	= 'Фото документов'
    desc	= 'Титульная фотография документов'
    cap		= "documents"
	exec	= 'doc:read:landing4:[id]=[@data.selector];options:[data]'
    update	= 'widget:landingUpdate:[id]'
    delete	= 'widget:landingDelete:[id]'
    preview	= 'widget:landingPreview:[id]=image:design/preview_landing4.jpg'
>
<cfg:data.size				name = 'Размер изображения (ШxВ)' default	='1100x750' />
<cfg:data.style.background	name = 'Цвет фона' />
<cfg:data.selector		    name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />

<?
//	+function doc_read_landing4
function doc_read_landing4($db, $val, $search)
{
	$data	= $search['options'];
	$size	= $data['size'];
	if (!$size) $size = '1100x750';
?>
{{script:landing4}}
<link rel="stylesheet" type="text/css" href="css/widgetLanding4.css">

<div class="landing4"{!$data[:style]}>
<? while($data = $db->next())
{
	$id		= $db->id();
	$menu	= doc_menu($id, $data);
	$class	= $db->ndx > 1?'':' current';
	$url	= getURL($db->url());
?>
<div class="item {$class}">
	<h2>{$data[title]}</h2>
    {{doc:titleImage:$id=size:$size;hasAdmin:top;adminMenu:$menu;property.href:$url}}
</div>
<? } ?>
</div>

<? return $search; } ?>

<? function script_landing4(){ ?>
{{script:CrossSlide}}
<script>
$(function(){
	$(".landing4").CrossSlideEx();
});
</script>
<? } ?>
</widget:landing4>