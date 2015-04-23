<? function widget_landing2($id, $data)
{
	$search				= $data[':selector'];
	$search[':data']	= $data;
?>
{{doc:read:landing2=$search}}
<? } ?>

<?
//	+function doc_read_landing2
function doc_read_landing2($db, $val, $search)
{
	$data	= $search[':data'];
	$elmSize= $data['elmSize'];
	$elmStyle	= $data[':elmStyle'];

	$search[':sortable']	= array(
		'select'=> '.landing2',
	);
?>

<link rel="stylesheet" type="text/css" href="css/widgetLanding2.css">
<div class="landing2"{!$data[:style]}>
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data, '+sortable');
?>
<div class="landing2elm"{!$elmStyle}>
{{doc:titleImage:$id=clip:$elmSize;hasAdmin:bottom;adminMenu:$menu;property.href:$url}}
</div>
<? } ?>
</div>

<? return $search; } ?>
