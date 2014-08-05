<? function gallery_adminImageMask(&$id, &$data)
{
	if ($id){
		$db		= module('doc');
		$mask	= $data['mask'];
		$d		= $db->openID($id);
		$offset	= $d['fields']['any'];
		$offset	= $offset['maskPosition'][$mask];
		$topOffset	= (int)$offset['top'];

		$menu	= array();
		
		$image	= module("doc:titleImage:$id");
		$m		= urlencode($mask);
		$menu['Кадрировать']	= getURL("gallery_adminImageMask$id", "mask=$m");
		
		$folder	= $db->folder($id);
		$folder	= str_replace(localRootPath.'/', globalRootURL, $folder);
		$menu['Загрузить']	= array('class' => 'adminImageUpload', 'rel' => "$folder/Title", 'href' => getURL('#'));
		
		$maskFile	= cacheRootPath."/$mask";
		list($w, $h) = getimagesize($maskFile);

		m('script:adminImageMask');
		imageBeginAdmin($menu);
		echo "<div class=\"adminImage\" style=\"width: $w"."px; height: $h"."px\">";
		if (!displayThumbImage($image, $w, " class=\"adminImageImage\" style=\"top:$topOffset"."px\"")){
			echo '<img class="adminImageImage" />';
		};
		echo "<img src=\"$mask\" class=\"adminImageMask\" />";
		echo '</div>';
		imageEndAdmin();
		return;
	}
	
	$id = (int)$data[1];
	if (!access("write", "doc:$id")) return;

	$mask	= getValue('mask');
	$top	= getValue('top');
	if (!$mask) return;

	$db		= module('doc');
	$data	= $db->openID($id);
	$data['fields']['any']['maskPosition'][$mask]	= array('top'=>$top);

	$d		= array();
	$d['fields']['any']['maskPosition']	= $data['fields']['any']['maskPosition'];
	m("doc:update:$id:edit", $d);
	
	clearThumb($db->folder($id));
	m("doc:cacheClear:$id");
	setTemplate('');
}?>
<? function script_adminImageMask(&$val){
	m('script:jq_ui');
	m('script:fileUpload');
?>
<script>
$(function(){
	$("a[href*=gallery_adminImageMask]").click(function()
	{
		var image = $($(this).parent().parent().find(".adminImage .adminImageImage"));
		
		if ($(this).attr("oldEditLabel"))
		{
			$(this).parent().parent().removeClass("adminImageActive");
			$(this).html($(this).attr("oldEditLabel"));
			$(this).attr("oldEditLabel", '');
			image.draggable("destroy");
			var top = parseInt(image.css("top"));
			var url = $(this).attr("href") + "&top=" + top;
			console.log("AJAX: " + url);
			$.ajax(url).fail(function(){
				alert("Error");
			});
		}else{
			$(this).parent().parent().addClass("adminImageActive");
			$(this).attr("oldEditLabel", $(this).html());
			$(this).text("Сохранить");
			var maxTop = image.height() - image.parent().height();
			if (image.position().top < -maxTop) image.css("top", 0);
			image.draggable({
				axis: "y",
				drag: function(event, ui){
					if (ui.position.top < -maxTop) ui.position.top = -maxTop;
					if (ui.position.top > 0) ui.position.top = 0;
					return true;
				}
				});
		}
		return false;
	});
	$(".adminImageUpload").fileUpload(function(ev){
		for(name in ev){
			var path = ev[name]['path'];
			var image = $($(this).parents().parent().find(".adminImage .adminImageImage"));
			image.attr("src", path).css({width: "100%", height: "auto"});
			break;
		}
//		document.location = document.location;
	});
});
</script>
<? } ?>
<? function style_adminImageMask(&$val){ ?>
<style>
div.adminImage{
	overflow:hidden;
	position:relative;
	margin:0;
	padding:0;
}
div.adminImage .adminImageImage{
	position:relative;
}
div..adminImage .adminImageMask{
	position:absolute;
	top:0; left:0;
}
.adminImageActive .adminImageMask{
	visibility:hidden;
}
div.adminImage .ui-draggable{
	cursor:move;
}
</style>
<? } ?>