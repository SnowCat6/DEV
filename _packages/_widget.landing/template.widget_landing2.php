<? function widget_landing2($id, $data)
{
	$search				= array();
	$search['@!place']	= $id;
	$search[':data']	= $data;
?>
<link rel="stylesheet" type="text/css" href="css/widgetLanding2.css">
{{doc:read:landing2=$search}}
<? } ?>

<?
//	+function doc_read_landing2
function doc_read_landing2($db, $val, $search)
{
	$data	= $search[':data'];
	$elmSize= $data['elmSize'];
	
	$style			= array();
	list($w, $h)	= explode('x', $elmSize);
	if ($w) $style['width']	= $w . 'px';
	if ($h) $style['height']= $h . 'px';
	$style	= makeStyle($style);
?>

<div class="landing2">
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
?>
<div class="landing2Eml"{!$style}>
{{doc:titleImage:$id=clip:$elmSize;hasAdmin:top;adminMenu:$menu;property.href:$url}}
</div>
<? } ?>
</div>

<? return $search; } ?>
