<widget:siteMenu
    category= 'Навигация'
    name	= 'Вертикальное меню'
    title	= 'Одноуровневое меню'
    cap		= 'documents'
    exec	= 'widget:siteMenu:[id]=[@data.selector];options:[data]'
>
<cfg:data.selector	name = 'Выбор документов' default="@!place:[id]" />
<cfg:data.class		name = 'class' default="siteMenu menu" />
<cfg:data.deep		name = 'Глубина' default="1" />

<? function widget_siteMenu($id, $data){
	$data[':deep']	= $data['options']['deep'];
?>
<link rel="stylesheet" type="text/css" href="css/siteMenu.css">
<div {!$data[options][class]}>
    {{doc:read:menu=$data}}
</div>
<? } ?>
</widget:siteMenu>


<widget:siteMenuInline
    category= 'Навигация'
    name	= 'Горизонтальное меню'
    title	= 'Одноуровневое меню'
    cap		= 'documents'
    exec	= 'widget:siteMenuInline:[id]=[@data.selector];options:[data]'
>
<cfg:data.selector	name = 'Выбор документов' default="@!place:[id]" />
<cfg:data.class		name = 'class' default="siteMenu menu inline" />
<cfg:data.deep		name = 'Глубина' default="1" />

<?
//	+function widget_siteMenuInline
function widget_siteMenuInline($id, $data){
	$data[':deep']	= $data['options']['deep'];
?>
<link rel="stylesheet" type="text/css" href="css/siteMenu.css">
<div {!$data[options][class]}>
    {{doc:read:menu=$data}}
</div>
<? } ?>
</widget:siteMenuInline>

