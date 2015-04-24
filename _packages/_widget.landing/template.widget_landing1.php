<? function widget_landing1($id, $data)
{
	$folder			= $data['folder'];
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding.css">
<div class="widgetLanding1"{!$data[:style]}>
	<div class="image">
		{{file:image=clip:$data[size];uploadFolder:$data[imageFolder]/Title;hasAdmin:top}}
    </div>
    
    <div class="widgetLandingHolder">
        <div class="widgetLandingTitle">{{read:$folder/1}}</div>
        <div class="widgetLandingContent">{{read:$folder/2}}</div>
    </div>
</div>

<? } ?>
