<? function doc_read_catalog2(&$db, &$search, &$data)
{
	if (!$db->rows()) return $search;

	module('script:ajaxLink');
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
	$menu		= doc_menu($id, $data);
	$url		= getURL($db->url());
	$price		= docPriceFormat2($data);
?>
<th>{beginCompile:catalogThumb2}
<? if($id) displayThumbImage($title = docTitle($id), array(120, 150), '', '', $title); else echo '&nbsp;'; ?>
{endCompile:catalogThumb2}</th>
<td width="{$percent}%"><? if ($id){ ?>{beginAdmin}
<h3><a href="{$url}">{$data[title]}</a></h3>
{!$price}
{{bask:button:$id}}
{endAdminTop}
<? }else echo '&nbsp;'; ?></td>
<? }//	foreach ?></tr>
<? }while($d); ?>
</table>
<? return $search; } ?>
