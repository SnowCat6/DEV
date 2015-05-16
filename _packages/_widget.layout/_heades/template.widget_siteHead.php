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
	$logoWidth	= (int)$data['logoSize'];
	$url		= getURL();
?>
  <link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
  <link rel="stylesheet" type="text/css" href="css/siteHead.css">
  
  <div class="siteHead clearfix" {!$data[style]|style}>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="{$data[class]}">
      <tbody>
        <tr>
          <td valign="top" class="siteLogo" style="width:{$logoWidth}px; padding-right:{$data[logoPadding]}px">
          <module:file:image 
                	size		= "$data[logoSize]"
                    uploadFolder= "$data[imageFolder]/Title"
                    hasAdmin	= "top"
                    property.href="$url"
                    />
           </td>
          <td valign="top" class="siteInfo" style="width:100%"><module:read +=":$data[folder]/info" /></td>
        </tr>
      </tbody>
    </table>
    <div class="menu inline clearfix">
      <div class="left">
        <module:doc:read:menu @!place="topMenu">
          <ul>
            <li id="first"><a href="/week_menu.htm" title="МЕНЮ НА НЕДЕЛЮ"> <span>МЕНЮ НА НЕДЕЛЮ</span></a> </li>
            <li> <a href="/catering.htm" title="БАНКЕТЫ И ФУРШЕТЫ"> <span>БАНКЕТЫ И ФУРШЕТЫ</span></a> </li>
            <li> <a href="/delivery.htm" title="ДОСТАВКА ОБЕДОВ"> <span>ДОСТАВКА ОБЕДОВ</span></a> </li>
            <li> <a href="/recepies.htm" title="РЕЦЕПТЫ"> <span>РЕЦЕПТЫ</span></a> </li>
          </ul>
        </module:doc:read:menu>
      </div>
      <div class="right">
        <module:doc:read:menu @!place="topMenu2">
          <ul>
            <li id="first"><a href="/about.htm" title="О КОМПАНИИ"> <span>О КОМПАНИИ</span></a> </li>
          </ul>
        </module:doc:read:menu>
      </div>
    </div>
  </div>
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
    <cfg:data.style.padding-top 	name = 'Отступ сверху' default="20px" />
    <cfg:data.class 		name = 'class' />
    <?
//	+function widget_siteBottom
function widget_siteBottom($id, $data)
{
	mkDir($data['imageFolder']);
	$url	= getURL();
?>

<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/siteBottom.css">

<div class="siteBottom clearfix" {!$data[style]|style}>
     <div class="menu inline clearfix">
        <module:doc:read:menu @!place="topMenu">
          <ul>
            <li id="first"><a href="/week_menu.htm" title="МЕНЮ НА НЕДЕЛЮ"> <span>МЕНЮ НА НЕДЕЛЮ</span></a> </li>
            <li> <a href="/catering.htm" title="БАНКЕТЫ И ФУРШЕТЫ"> <span>БАНКЕТЫ И ФУРШЕТЫ</span></a> </li>
            <li> <a href="/delivery.htm" title="ДОСТАВКА ОБЕДОВ"> <span>ДОСТАВКА ОБЕДОВ</span></a> </li>
            <li> <a href="/recepies.htm" title="РЕЦЕПТЫ"> <span>РЕЦЕПТЫ</span></a> </li>
          </ul>
        </module:doc:read:menu>
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="{$data[class]}">
      <tbody>
        <tr>
          <td valign="top" style="padding-right:{$data[logoPadding]}px">
          	<div class="siteBottomLogo">
               <module:file:image 
                        size		= "$data[logoSize]"
                        uploadFolder= "$data[imageFolder]/Title"
                        hasAdmin	= "top"
                        property.href="$url"
                        />
             </div>
            <div class="siteBottomLeft">
            	<module:read +=":$data[folder]/info2" />
             </div>
          </td>
          <td valign="top" class="siteBottomInfo" width="100%">
              <module:read += ":$data[folder]/info" />
          </td>
        </tr>
      </tbody>
    </table>
    <? } ?>
</div>
</widget:siteBottom>
