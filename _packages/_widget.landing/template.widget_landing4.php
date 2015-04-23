<? function widget_landing4($id, $data)
{
	$search				= $data[':selector'];
	$search[':data']	= $data;
?>
{{doc:read:landing4=$search}}
<? } ?>

<?
//	+function doc_read_landing4
function doc_read_landing4($db, $val, $search)
{
	$data	= $search[':data'];
	$size	= $data['size'];
?>
{{script:landing4}}
<link rel="stylesheet" type="text/css" href="css/widgetLanding4.css">

<div class="landing4"{!$data[:style]}>
<? while($data = $db->next())
{
	$id		= $db->id();
	$menu	= doc_menu($id, $data);
	$class	= $db->ndx > 1?'':' current';
	$url	= getURL($db->url());
?>
<div class="item {$class}">
	<h2>{$data[title]}</h2>
    {{doc:titleImage:$id=size:$size;hasAdmin:top;adminMenu:$menu;property.href:$url}}
</div>
<? } ?>
</div>

<? return $search; } ?>

<? function script_landing4(){ ?>
{{script:CrossSlide}}
<script>
$(function(){
	$(".landing4").CrossSlideEx();
});
</script>
<? } ?>