<widget:landing1_3
    category= 'Лендинг'
    name	= 'Текст с фоном'
    desc	= 'Текст с фоном в 2ъ колонках'
    cap		= "landing1"
>
<cfg:data.style.size
	name	= "Размер фона (ШxВ)"
    default	= "1100"
    />
<cfg:data.style.margin
	name	= "Отсуп"
    default	= "10px auto"
    />

<? function widget_landing1_3($id, $data)
{
	$padding	= 40;
	$right		= 250;
	$left		= (int)$data['width'] - $right - 20 - 2*$padding;
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding.css">
<link rel="stylesheet" type="text/css" href="css/widgetLanding1_3.css">

<div {{file:background:$data[folder]}}>
<div class="widgetLanding1_3"{!$data[style]|style}>
    <div class="widgetLandingTitle"><module:read +=":$data[folder]/1" /></div>
    
    <div class="widgetLandingContent shadow">
    	<div class="left" style="width: {$left}px">
	        <module:read +=":$data[folder]/2" />
        </div>
    	<div class="right" style="width: {$right}px">
	        <module:read +=":$data[folder]/3" />
        </div>
    </div>
</div>
</div>
<? } ?>
</widget:landing1_3>
