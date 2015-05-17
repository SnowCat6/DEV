<? function admin_panel_cookie()
{
	$clipboard	= module('clipboard:get');
?>
<script src="script/adminClipboard.js"></script>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="50%" valign="top">
<h2 class="ui-state-default">Посещенные странцы</h2>
<? showClipboardDocuments($clipboard['doc_visit']) ?>
</td>

<td width="50%" valign="top">
<h2 class="ui-state-default">Редактированные страницы</h2>
<? showClipboardDocuments($clipboard['doc_edit']) ?>
</td>

</tr>
</table>


<? return "9999-clipboard"; }?>

<? function showClipboardDocuments($visit)
{
	if (!is_array($visit)) return;
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
<tr>
    <td><a href="{{url:page_edit_$id}}" id="ajax_edit">{$id}</a></td>
    <td><a href="{{url:$url}}" {!$dragID}>{$data[title]}</a></td>
</tr>
<? $pass[$id] = ob_get_clean(); } ?>

<table>
<?
$count	= count($pass);
foreach($visit as $id){
	echo $pass[$id];
} ?>
</table>

<? } ?>
