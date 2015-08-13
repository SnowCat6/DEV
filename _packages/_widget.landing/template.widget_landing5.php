<widget:landing5
    category= 'Лендинг'
    name	= 'Фото с текстом'
    desc	= 'Три фото с текстом'
    cap		= "landing1"
>
<cfg:data.style.width
	name	= "Ширина"
    default	= "1100"
    />
<?
function widget_landing5($id, $data)
{
	$width	= (int)$data['width'];
	$height	= 275;
	$space	= 10;
	
	$w1		= floor(($width - $space)/3) * 2;
	$w2		= $width - $w1 - $space;
	$w3		= $w1 - 65;
	
	$sz1	= $w1 . "x$height";
	$sz2	= $w2 . "x$height";
?>
<link rel="stylesheet" type="text/css" href="css/widgetLanding5.css">

<div class="widgetLanding5" {!$data[style]|style}>

	<div class="widgetHeader">
       	<module:read:$data[folder]/header />
    </div>

	<div class="imageHolder clearfix" style="margin-bottom: {$space}px">
        <div class="image1" style="width: {$w1}px; height: {$height}px">
            <module:file:image
                uploadFolder = "$data[imageFolder]/Title1"
                clip = "$sz1"
                hasAdmin="top"
             />
        </div>
        <div class="image2" style="width: {$w2}px; height: {$height}px">
            <module:file:image
                uploadFolder = "$data[imageFolder]/Title2"
                clip = "$sz2"
                hasAdmin="top"
             />
        </div>
    </div>
    
    <div class="contentHolder clearfix">
        <div class="image3" style="width: {$w2}px">
            <module:file:image
                uploadFolder = "$data[imageFolder]/Title3"
                clip = "$sz2"
                hasAdmin="top"
             />
        </div>
        <div class="content" style="width: {$w3}px">
        	<module:read:$data[folder]/content />
        </div>
    </div>
</div>
<? } ?>
</widget:landing5>