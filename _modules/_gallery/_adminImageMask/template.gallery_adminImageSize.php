<? function gallery_adminImageSize($id, &$data)
{
	$menu	= array();
	$db		= module('doc');
	
	$folder	= $db->folder($id);
	$folder	= str_replace(localRootPath.'/', globalRootURL, $folder);
	$menu['Загрузить']	= array('class' => 'adminImageSizeUpload', 'rel' => "$folder/Title", 'href' => getURL('#'));
	
	$style	= array();
	if ($data['width'])		$style[]= "width: $data[width]px";
	if ($data['height'])	$style[]= "height: $data[height]px";
	$style	= implode('; ', $style);
	
	imageBeginAdmin($menu);
	m('script:adminImageSize');
	echo "<div class=\"adminImageSize\" rel=\"$data[width]x$data[height]\">";
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
	$(".adminImageSizeUpload").fileUpload(function(ev){
		for(name in ev){
			var path = ev[name]['path'];
			var size = ev[name]['dimension'].split(' x ');
			var image = $($(this).parent().parent().find(".adminImageSize"));
			
			var sz = image.attr("rel").split('x');
			if (sz[1]){
				var r1 = sz[0] / sz[1];
				var r2 = size[0] / size[1];
				if (r1 < r2)	image.html('<img width="'+sz[0]+'px" src="'+ path + '" />');
				else	image.html('<img height="'+sz[1]+'px" src="'+ path + '" />');
			}else{
				image.html('<img width="'+sz[0]+'px" src="'+ path + '" />');
			}
			break;
		}
//		document.location = document.location;
	});
});
</script>
<? } ?>