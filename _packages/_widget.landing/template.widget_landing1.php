<widget:landing1
    category= 'Лендинг'
    name	= 'Фон с информацией'
    desc	= 'Фоновая картинка я заголовком и текстом'
    cap		= "landing1"
>
<cfg:data.style.size
	name	= "Размер фона (ШxВ)"
    default	= "1100"
    />
<cfg:data.style.background
	name	= "Цвет фона"
    default	= ""
    />
<cfg:data.style.margin
	name	= "Отсуп"
    default	= "10px auto"
    />

<? function widget_landing1($id, $data){?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding.css">

<div class="widgetLanding1"{!$data[style]|style}>
	<div class="image">
    	<module:file:image clip="$data[size]" uploadFolder = "$data[imageFolder]/Title" hasAdmin = "top" />
    </div>
    
    <div class="widgetLandingHolder">
        <div class="widgetLandingTitle"><module:read +=":$data[folder]/1" /></div>
        <div class="widgetLandingContent"><module:read +=":$data[folder]/2" /></div>
    </div>
</div>

<? } ?>
</widget:landing1>
