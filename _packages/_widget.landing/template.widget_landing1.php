<? function widget_landing1($id, $data)
{
	$folder	= $data['uploadFolder'];
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding.css">
<div class="widgetLanding1"{!$data[:style]}>
	<div class="image">
		{{file:image=size:$data[imageSize];uploadFolder:$folder;hasAdmin:top}}
    </div>
    
    <div class="widgetLandingHolder">
        <div class="widgetLandingTitle">{{read:$id/1}}</div>
        <div class="widgetLandingContent">{{read:$id/2}}</div>
    </div>
</div>

<? } ?>
