<? function widget_landing3($id, $data)
{
	$search				= $data[':selector'];
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
	$height	= $data['height'];
	
	$style			= $data['style'];
	$style['width']	= (int)$width . 'px';
	$style['margin']= 'auto';
	$style			= makeStyle($style);
	
	$w		= floor($width / 4);
	$h		= floor($height / 2); 
	
	//	0-v, 1-sh, 2-wh
	$sz			= array(
		$w . 'x' . $h*2,
		$w . 'x' . $h,
		$w*2 .'x'. $h
	);
	
	$w2	= "1:margin:$h"."px 0 0 -$w"."px";
	
	$szType		= array(
		0, 1, $w2, 2, 1, 1,    
		1, $w2, 0, 1, $w2, 0);

	$search[':sortable']	= array(
		'select'=> '.landing3',
	);
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding3.css">
<div class="landing3"{!$style}>
<?
$bSplit	= '';
while($data = $db->next())
{
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data, '+sortable');
	
	$ix		= ($db->ndx - 1) % count($szType);
	
	$s1		= '';
	list($szIx, $s1)	= explode(':', $szType[$ix], 2);
	$size	= $sz[$szIx];

	$s		= explode('x', $size);
	$style	= array(
		'width'		=> $s[0] . 'px',
		'height'	=> $s[1] . 'px'
		);
	foreach(explode(';', $s1) as $s2){
		list($n, $v) = explode(':', $s2);
		if ($n) $style[$n]	= $v;
	}
	$style	= makeStyle($style);
	
	$s[0]	-= $padding; $s[1]	-= $padding;
	$s		= implode('x', $s);
?>
<div class="landing3Elm elm{$ix}"{!$style}>
{{doc:titleImage:$id=clip:$s;hasAdmin:bottom;adminMenu:$menu;property.href:$url}}
</div>
<? } ?>
</div>

<? return $search; } ?>