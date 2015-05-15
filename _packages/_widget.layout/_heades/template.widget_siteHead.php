<widget:siteHead
    category= 'Макет'
    name	= 'Заголовок'
    title	= 'Верх страницы'
    cap		= 'layout'
>
<cfg:data.logoSize		name = 'Размеры лого (WxH)' default = '250' />
<cfg:data.logoPadding	name = 'Отступ о логотипа' default = '30' />
<cfg:data.style.padding-bottom 		name = 'Отступ снизу' default="20px" />
<cfg:data.class 		name = 'class' />

<?
//	+function widget_siteHead
function widget_siteHead($id, $data)
{
	mkDir($data['imageFolder']);
	$url	= getURL();
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="siteHead {$data[class]}" {!$data[style]}>
  <tbody>
    <tr>
      <td valign="top" class="siteLogo" style="padding-right:{$data[logoPadding]}px">
        	{{file:image=size:$data[logoSize];uploadFolder:$data[imageFolder]/Title;hasAdmin:top;property.href:$url}}
      </td>
      <td valign="top" class="siteInfo" style="width:100%">
            {{holder:$id.layout}}
      </td>
    </tr>
  </tbody>
</table>

<? } ?>
</widget:siteHead>


<widget:siteBottom
    category= 'Макет'
    name	= 'Подвал'
    title	= 'Низ страницы'
    cap		= 'layout'
>
<cfg:data.logoSize		name = 'Размеры лого (WxH)' default = '250' />
<cfg:data.logoPadding	name = 'Отступ о логотипа' default = '30' />
<cfg:data.style.padding-top 		name = 'Отступ сверху' default="20px" />
<cfg:data.class 		name = 'class' />

<?
//	+function widget_siteBottom
function widget_siteBottom($id, $data)
{
	mkDir($data['imageFolder']);
	$url	= getURL();
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="siteBottom {$data[class]}" {!$data[style]}>
  <tbody>
    <tr>
      <td valign="top" style="padding-right:{$data[logoPadding]}px">
		    <div class="siteBottomLogo">
        		{{file:image=size:$data[logoSize];uploadFolder:$data[imageFolder]/Title;hasAdmin:top;property.href:$url}}
            </div>
            <div class="siteBottomLeft" style="{$width}">
	            {{holder:$id.left}}
            </div>
      </td>
      <td valign="top" class="siteBottomInfo" width="100%">
            {{holder:$id.layout}}
      </td>
    </tr>
  </tbody>
</table>

<? } ?>

</widget:siteBottom>