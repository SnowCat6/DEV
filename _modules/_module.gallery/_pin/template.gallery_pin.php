<? function gallery_pin(&$id, &$data)
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
		$menu['Кадрировать']	= getURL("gallery_pin$id", "mask=$m");
		
		$folder	= $db->folder($id);
		$folder	= str_replace(localRootPath.'/', globalRootURL, $folder);
		$menu['Загрузить']	= array('class' => 'adminImageUpload', 'rel' => "$folder/Title", 'href' => getURL('#'));
		
		$maskFile	= cacheRootPath."/$mask";
		list($w, $h) = getimagesize($maskFile);

		m('script:galleryPin');
		imageBeginAdmin($menu);
		echo "<div class=\"adminImage\" style=\"width: $w"."px; height: $h"."px\">";
		displayThumbImage($image, $w, " class=\"adminImageImage\" style=\"top:$topOffset"."px\"");
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

	$d	= array();
	$d['fields']['any']['maskPosition'][$mask]	= array(
		'top'=>$top
	);
	m("doc:update:$id:edit", $d);
	
	$db	= module('doc');
	clearThumb($db->folder($id));
	m("doc:cacheClear:$id");
	setTemplate('');
}?>