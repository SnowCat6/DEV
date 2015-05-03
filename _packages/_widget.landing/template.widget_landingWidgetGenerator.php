<?
//	+function widget_landingUpdate
function widget_landingUpdate($id, &$widget)
{
	$data			= $widget['data'];
	
	$style			= array();
	list($w, $h)	= explode('x', $data['elmSize']);
	if ($w) $style['width']	= $w . 'px';
	if ($h) $style['height']= $h . 'px';
	$widget['data'][':elmStyle']	= makeStyle($style);

	
	$style		= array();
	$size		= $data['size'];
	if ($size)
	{
		list($w, $h) = explode('x', $size);
		if ($w) $style['width']	= $w . 'px';
		if ($h) $style['height']= $h . 'px';
	}

	if (!is_array($data['style']))	$data['style'] = array();
	foreach($data['style'] as $name => $val)
	{
		if ($name == 'width') $val = (int)$val . 'px';
		$style[$name] = $val;
	}
	$widget['data'][':style'] = makeStyle($style);


	
	$folder			= "widgets/$widget[id]";
	$imageFolder	= images . "/$folder";
	$widget['data']['folder']		= $folder;
	$widget['data']['imageFolder']	= $imageFolder;
	makeDir($imageFolder);


	setDataValues($widget['data'][':selector'], $widget['data']['selector']);
}
//	+function widget_landingDelete
function widget_landingDelete($id, $data)
{
	m("file:unlink", $data['imageFolder']);
}
//	+function widget_landingPreview
function widget_landingPreview($id, $data)
{
/*
	$widget	= module("holderAdmin:getWidget:$id");
	$exec	= $widget[':exec'];
	if ($exec['code']) return module($exec['code'], $exec['data']);
*/	
	$image	= getSiteFile($data['image']);
	if ($image){
		$p	= array('src' => $image);
		return module("image:display", $p);
	}
}
?>