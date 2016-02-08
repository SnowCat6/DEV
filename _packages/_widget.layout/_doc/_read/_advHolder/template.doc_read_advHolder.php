<widget:advHolder
	category= "Документы"
	name	= "Баннер с текстом и картинкойй"
    note	= "Документы с переключением и ткстом"
    cap		= "documents"
	exec	= "widget:advHolder:[id]=[@data.selector];options:[data]"
>
<cfg:data.selector	name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.elmSize	name = 'Размер изображения (ШxВ)' default = '1100x420' />
<cfg:data.style.margin	name	= "Отсуп"    default	= "10px auto"    />

<wbody>

<? $options = $data['options'] ?>

<div class="indexHolder" {!$options[style]|style}> 
    <module:doc:read:advHolder @="$data" />

    <div class="welcomeHolder welcomeHolderPadding">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tbody>
            <tr>
              <td><module:read:$options[folder]/1 /></td>
              <td align="right"><module:read:$options[folder]/2 /></td>
            </tr>
          </tbody>
        </table>
    </div>

</div>

</wbody>

</widget:advHolder>

<?
//	+function doc_read_advHolder
function doc_read_advHolder($db, $val, $search)
{
	m('script:jq');
	m('fileLoad', 'script/advHolder.js');
	$options	= $search['options'];
	$elmSize	= $options['elmSize'];
	list($w, $h)= explode('x', $elmSize);
?>
<link rel="stylesheet" type="text/css" href="css/advHolder.css">
<div class="advSliderEx" style="width:{$w}px; height:{$h}px">
<?
	$style	= NULL;
	while($data = $db->next()){
		$id		= $db->id();
		$menu	= doc_menu($id, $data);
		$url	= $db->url();
		$style	= is_null($style)?'':' style="display:none"';
		$url	= getURL($url);
?>
    <div class="advSlider"{!$style}>
        <div class="image">
           	{{doc:titleImage:$id:mask=clip:$elmSize;hasAdmin:bottom;adminMenu:$menu;property.href:$url;property.title:$data[title]}}
        </div>
        <blockquote>
       		{{doc:editable:$id:note}}
        </blockquote>
    </div>
<? } ?>
</div>
<? return $search; } ?>