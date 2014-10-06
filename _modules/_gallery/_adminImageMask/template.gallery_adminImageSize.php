<? function gallery_adminImageSize($id, &$data)
{
	$db		= module('doc');
	
	$folder	= $db->folder($id);
	$folder	= str_replace(localRootPath.'/', globalRootURL, $folder);

	$menu	= is_array($data['adminMenu'])?$data['adminMenu']:array();
	$menu['Загрузить']	= array(
		'class' => 'adminImageSizeUpload',
		'rel' => json_encode(array('uploadFolder' => "$folder/Title")),
		'href' => getURL('#')
	);
	
	$style	= array();
	if ($data['width'])		$style[]= "width: $data[width]px";
	if ($data['height'])	$style[]= "height: $data[height]px";
	$style	= implode('; ', $style);
	
	imageBeginAdmin($menu);
	m('script:adminImageSize');
	
	$thisStyle	= array();
	if ($data['width']) $thisStyle[]	= "max-width: $data[width]px";
	if ($data['height']) $thisStyle[]	= "max-height: $data[height]px";
	$thisStyle	= implode(';', $thisStyle);

	echo "<div class=\"adminImageSize\" style=\"$thisStyle\">";
	
	if ($data['title']){
		module("doc:titleImage:$id:size", $data);
	}else{
		echo "<div style=\"$style\"></div>";
	};
	echo '</div>';
	imageEndAdmin();
}?>
<? function script_adminImageSize(&$val){
	m('script:jq_ui');
	m('script:fileUpload');
?>
<script>
$(function(){
	$(".adminImageSizeUpload")
	.fileUpload(fnSizeFileUpload)
	.each(function(){
		$(this).parents(".adminEditArea")
			.find(".adminImageSize")
			.attr("rel", $(this).attr("rel"))
			.fileUpload("d&d", fnSizeFileUpload);
	});
});
function fnSizeFileUpload(ev)
{
	var image = $(this).parents(".adminEditArea").find(".adminImageSize");

	for(name in ev){
		var path = ev[name]['path'];
		var size = ev[name]['dimension'].split(' x ');
		
		var w = parseInt(image.css('max-width'));
		var h = parseInt(image.css('max-height'));
		if (h){
			var r1 = w / h;
			var r2 = size[0] / size[1];
			if (r1 < r2)	image.html('<img width="'+w+'px" src="'+ path + '" />');
			else	image.html('<img height="'+h+'px" src="'+ path + '" />');
		}else{
			image.html('<img width="'+w+'px" src="'+ path + '" />');
		}
		break;
	}
}
</script>
<? } ?>