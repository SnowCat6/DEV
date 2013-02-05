<?
function doc_read_catalog2(&$db, &$search, &$data){
	if (!$db->rows()) return;
	$maxCol	= 2;
	$percent= round(100/$maxCol);
?>
<table>
<? do{ ?>
<tr>
<?
	$table	= array();
	for($ix = 0; $ix < $maxCol; ++$ix)
		$table[$ix] = $d = $db->next();
?>
<? foreach($table as &$data){
	$db->data	= $data;
	$id			= $db->id();
?>
<th>{beginCompile:catalogThumb}
<? if($id) displayThumbImage($title = docTitle($id), array(120, 150), '', '', $title); else echo '&nbsp;'; ?>
{endCompile:catalogThumb}</th>
<td width="{$percent}%"><? if ($id){ ?>{beginAdmin}
{beginCompile:catalog}
<h3><a href="{$url}">{$data[title]}</a></h3>
<div>{{prop:read=id:$id;group:Свойства товара}}</div>
{endCompile:catalog}
{endAdminTop}
<? }else echo '&nbsp;'; ?></td>
<? }//	foreach ?></tr>
<? }while($d); ?>
</table>
<? } ?>