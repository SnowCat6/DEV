<? function widget_landing3($id, $data)
{
	$search				= array();
	$search['@!place']	= $id;
	$search[':data']	= $data;
?>
{{doc:read:landing3=$search}}
<? } ?>

<?
//	+function doc_read_landing3
function doc_read_landing3($db, $val, $search)
{
	$data	= $search[':data'];
	$padding= $data['padding'];
	$width	= $data['width'];
	
	$h		= floor($width / 8); 
	$w		= $h * 2;
	
	//	0-v, 1-sh, 2-wh
	$sz			= array(
		$w . 'x' . $h*2,
		$w . 'x' . $h,
		$w*2 .'x'. $h
	);
	$szType		= array(0, 1, 1, 2, 1, 1,    1, 1, 0, 1, 1, 0);
/*
	$search[':sortable']	= array(
		'select'=> '.landing3, .landing3 > div',
		'axis'	=> 'y'
	);
*/
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding3.css">
<div class="landing3">
<?
$bSplit	= '';
while($data = $db->next())
{
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data/*, '+sortable'*/);
	
	$ix		= $db->ndx%12 - 1;
	$size	= $sz[$szType[$ix]];
	
	$s		= explode('x', $size);
	$style	= makeStyle(array(
		'width'		=> $s[0] . 'px',
		'height'	=> $s[1] . 'px'
		));
	if ($ix % 3 == 0){
		echo $bSplit; $bSplit = '</div>';
		$style2	= makeStyle(array('width' => $w*2 . 'px', 'float' => 'left'));
		echo "<div $style2>";
	}
	
	$s[0]	-= $padding; $s[1]	-= $padding;
	$s		= implode('x', $s);
?>
<div class="landing3Eml elm{$ix}"{!$style}>
{{doc:titleImage:$id=clip:$s;hasAdmin:bottom;adminMenu:$menu;property.href:$url}}
</div>
<? }; echo $bSplit; ?>
</div>

<? return $search; } ?>
