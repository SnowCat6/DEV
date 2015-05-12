<widget:landing1_2
    category= 'Лендинг'
    name	= 'Текст с таблицей'
    desc	= 'Текст с иконками и текстом'
    cap		= "landing1"
>
<cfg:data.style.width
	name	= "Ширина"
    default	= "1100"
    />
<cfg:data.elmCount
	name	= "Количество колонок"
    default	= "4"
    />
<cfg:data.style.background
	name	= "Цвет фона"
    default	= ""
    />
<cfg:data.style.margin
	name	= "Отсуп"
    default	= "10px auto"
    />

<? function widget_landing1_2($id, $data)
{
	$elmCount	= (int)$data['elmCount'];
	if ($elmCount){
		$elmWidth	= floor($data['width'] / $elmCount);
		$imageSize	= $elmWidth . 'x' . $elmWidth;
	}
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding.css">
<link rel="stylesheet" type="text/css" href="css/widgetLanding1_2.css">

<div class="widgetLanding1_2"{!$data[style]}>
    <div class="widgetLandingTitle"><module:read +=":$data[folder]/1" /></div>
    
    <div class="landing1_2elm">
<? for($i=0; $i<$elmCount; ++$i){ ?>
<div class="item" style="width: {$elmWidth}px">
	<div class="image">
		<module:file:image uploadFolder = "$data[imageFolder]/Title$i" hasAdmin="top" />
    </div>
	<div class="content">
		<module:read +=":$data[folder]/ctx$i" />
    </div>
</div>
<? } ?>
    </div>
    
    <div class="widgetLandingContent"><module:read +=":$data[folder]/2" /></div>
	<div class="image">
    	<module:file:image clip="$data[width]" uploadFolder = "$data[imageFolder]/Title" hasAdmin = "top" />
    </div>
</div>

<? } ?>
</widget:landing1_2>
