<? function doc_read_catalog(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;

	module('script:lightbox');
	module('script:ajaxLink');
	$maxCol	= 2;
	$percent= round(100/$maxCol);
?>
<table>
<? while(true){
	$table	= array();
	for($ix = 0; $ix < $maxCol; ++$ix){
		if ($table[$ix] = $db->next()) continue;
		if ($ix == 0) break;
	}
	if ($ix == 0) break;
?>
<tr>
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
<? }//	while ?>
</table>
<? return $search; } ?>
