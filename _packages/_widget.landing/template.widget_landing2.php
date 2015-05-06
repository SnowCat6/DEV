<widget:landing2
    category= 'Лендинг'
    name	= 'Фотоплитка'
    desc	= 'Фотографии одинакового размера с сылками на документы'
    cap		= "documents"
    exec	= 'doc:read:landing2=[@data.selector];options:[data]'
>
<cfg:data.style.width	name = 'Ширина окна' default = '1100' />
<cfg:data.elmSize		name = 'Размер плитки (ШxВ)' default = '220x220' />
<cfg:data.style.background	name = 'Цвет фона' />
<cfg:data.selector			name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />

<?
//	+function doc_read_landing2
function doc_read_landing2($db, $val, $search)
{
	$options			= $search['options'];
	$search[':sortable']= array(
		'select'=> '.landing2',
	);
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding2.css">
<div class="landing2"{!$data[:style]}>
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data, '+sortable');
?>
<div class="landing2elm"{!$options[elmStyle]}>
<module:doc:titleImage +=":$id" clip="$options[elmSize]" hasAdmin="bottom" adminMenu="$menu" property.href="$url" />
</div>
<? } ?>
</div>

<? return $search; } ?>
</widget:landing2>