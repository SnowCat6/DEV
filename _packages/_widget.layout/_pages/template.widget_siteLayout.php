<widget:sitePage
    category= 'Макет'
    name	= 'Страница сайта'
    title	= 'Формат страницы сайта'
    cap		= "layout"
>
<cfg:data.style.width		name = 'Ширина' default = '1100px' />
<cfg:data.style.margin		name = 'Отсупы' default = '0 auto' />
<cfg:data.style.background	name = 'Фон' type = 'color' />
<cfg:data.style.padding		name = 'Отступы' default = '0px 20px' />
<cfg:data.class				name = 'class' type="checkbox" checked = 'shadow' />

<?
//	+function widget_sitePage
function widget_sitePage($id, $data){?>

<div {!$data[style]|style}{!$data[class]}>
{{holder:$id.layout}}
</div>

<? } ?>
</widget:sitePage>


<widget:siteLayout2
    category= 'Макет'
    name	= '2 колонки левая'
    title	= 'Двух колоночный формат'
    cap		= 'layout'
>
<cfg:data.widthLeft		name = 'Ширина левая' default = '250px' />
<cfg:data.padding		name = 'Отступ' default 	= '20px' />
<cfg:data.class			name = 'class' />

<?
//	+function widget_siteLayout2
function widget_siteLayout2($id, $data){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0"{!$data[class]}>
  <tbody>
    <tr>
      <td valign="top" style="width: {$data[widthLeft]}; min-width: {$data[widthLeft]}; padding-right: {$data[padding]}" class="siteLayoutLeft">
          {{holder:$id.layoutLeft}}
      </td>
      <td valign="top" class="siteLayout">
          {{holder:$id.layout}}
      </td>
    </tr>
  </tbody>
</table>

<? } ?>
</widget:siteLayout2>



<widget:siteLayout2Right
    category= 'Макет'
    name	= '2 колонки права'
    title	= 'Двух колоночный формат '
    cap		= 'layout'
>
<cfg:data.widthRight	name = 'Ширина правая' default = '250px' />
<cfg:data.padding		name = 'Отступ' default 	= '20px' />
<cfg:data.class			name = 'class' />

<?
//	+function widget_siteLayout2Right
function widget_siteLayout2Right($id, $data){ ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0"{!$data[class]}>
  <tbody>
    <tr>
      <td valign="top" class="siteLayout">
          {{holder:$id.layout}}
      </td>
      <td valign="top" style="width: {$data[widthRight]}; min-width: {$data[widthRight]}; padding-left: {$data[padding]}" class="siteLayoutLeft">
          {{holder:$id.layoutRight}}
      </td>
    </tr>
  </tbody>
</table>


<? } ?>
</widget:siteLayout2Right>



<widget:siteLayout3
    category= 'Макет'
    name	= '3 колонки'
    title	= 'Трех колоночный формат '
    cap		= 'layout'
>
<cfg:data.widthLeft		name = 'Ширина левая' default = '250px' />
<cfg:data.widthRight	name = 'Ширина правая' default = '250px' />
<cfg:data.padding		name = 'Отступ' default = '20px' />

<?
//	+function widget_siteLayout3
function widget_siteLayout3($id, $data){ ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="{$data[class]}">
  <tbody>
    <tr>
      <td valign="top" style="width: {$data[widthLeft]}; min-width: {$data[widthLeft]}; max-width: {$data[widthLeft]}; padding-right: {$data[padding]}" class="siteLayoutLeft">
          {{holder:$id.layoutLeft}}
      </td>
      <td valign="top" class="siteLayout">
          {{holder:$id.layout}}
      </td>
      <td valign="top" style="width: {$data[widthRight]}; min-width: {$data[widthRight]}; max-width: {$data[widthRight]}; padding-left: {$data[padding]}" class="siteLayoutRight">
          {{holder:$id.layoutRight}}
      </td>
    </tr>
  </tbody>
</table>

<? } ?>
</widget:siteLayout3>