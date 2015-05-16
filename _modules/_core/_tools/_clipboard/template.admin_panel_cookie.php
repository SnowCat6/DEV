<? function admin_panel_cookie()
{
	$clipboard	= module('clipboard:get');
	$visit		= $clipboard['doc_visit'];
	if (!is_array($visit)) $visit = array();
	$visit		= array_reverse($visit);
	
?>
<?
	$s		= array();
	$s['id']= $visit;
	
	$pass	= array();
	$db		= module("doc:find", $s);
	while($data = $db->next())
	{
		$id		= $db->id();
		$url	= $db->url();
		$dragID	= docDraggableID($id, $data);
		ob_start();
?>
<div><a href="{{url:$url}}" {!$dragID}>{$data[title]}</a></div>
<? $pass[$id] = ob_get_clean(); } ?>

<?
$count	= count($pass);
foreach($visit as $id){
	echo $pass[$id];
} ?>

<? return "9999-clipboard"; }?>