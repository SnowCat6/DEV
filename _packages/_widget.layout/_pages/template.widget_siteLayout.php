<widget:sitePage
    category= 'Макет'
    name	= 'Страница сайта'
    title	= 'Формат страницы сайта'
    cap		= "layout"
>
<cfg:data.style.width		name = 'Ширина' default = '1100px' />
<cfg:data.style.margin		name = 'Отсупы (margin)' default = '0 auto' />
<cfg:data.style.padding		name = 'Отступы (padding)' default = '0px 20px' />
<cfg:data.style.background	name = 'Цвет фона' type = 'color' />
<cfg:data.class				name = 'Тень страницы' type="checkbox" checked = 'shadow' />

<wbody>

<div {!$data[style]|style}{!$data[class]}>
	<module:holder:$id.layout layoutWidth="$data[width]" />
</div>

</wbody>

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

<wbody>

<table width="100%" border="0" cellspacing="0" cellpadding="0"{!$data[class]}>
  <tbody>
    <tr>
      <td valign="top" style="width: {$data[widthLeft]}; min-width: {$data[widthLeft]}; padding-right: {$data[padding]}" class="siteLayoutLeft">
          <module:holder:$id.layoutLeft layoutWidth="$data[widthLeft]" />
      </td>
      <td valign="top" class="siteLayout">
          <module:holder:$id.layout layoutWidth="" />
      </td>
    </tr>
  </tbody>
</table>

</wbody>

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

<wbody>

<table width="100%" border="0" cellspacing="0" cellpadding="0"{!$data[class]}>
  <tbody>
    <tr>
      <td valign="top" class="siteLayout">
          <module:holder:$id.layout />
      </td>
      <td valign="top" style="width: {$data[widthRight]}; min-width: {$data[widthRight]}; padding-left: {$data[padding]}" class="siteLayoutLeft">
          <module:holder:$id.layoutRight layoutWidth="$data[widthRight]" />
      </td>
    </tr>
  </tbody>
</table>

</wbody>

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

<wbody>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="{$data[class]}">
  <tbody>
    <tr>
      <td valign="top" style="width: {$data[widthLeft]}; min-width: {$data[widthLeft]}; max-width: {$data[widthLeft]}; padding-right: {$data[padding]}" class="siteLayoutLeft">
          <module:holder:$id.layoutLeft layoutWidth="$data[widthLeft]" />
      </td>
      <td valign="top" class="siteLayout">
          <module:holder:$id.layout />
      </td>
      <td valign="top" style="width: {$data[widthRight]}; min-width: {$data[widthRight]}; max-width: {$data[widthRight]}; padding-left: {$data[padding]}" class="siteLayoutRight">
          <module:holder:$id.layoutRight layoutWidth="$data[widthRight]" />
      </td>
    </tr>
  </tbody>
</table>

</wbody>

</widget:siteLayout3>


<widget:siteContent
    category= 'Макет'
    name	= 'Содержимое страницы'
    title	= ''
    exec	= "display:[data.layoutName]"
 />
<cfg:data.layoutName		name = 'Название контейнера' default = 'body' />

</widget:siteContent>