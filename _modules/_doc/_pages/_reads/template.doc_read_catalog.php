<?
function doc_read_catalog_before(&$db, $val, &$search){
	m('page:display:sort', mEx('doc:sort', $search));
}
function doc_read_catalog(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;

	$max	= $db->max;
	if (!$max) $max = 15;
	$maxCol	= 2;
	$percent= round(100/$maxCol);
	$p		= dbSeek($db, $max*$maxCol, array('search' => getValue('search')));
?>
{!$p}
<table class="productTable">
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

	$price		= docPrice($data);	
	$price2		= docPriceFormat2($data);
?>
<th valign="top">
	{{doc:titleImage:$id=size:120x135;hasAdmin:top;adminMenu:$menu;property.href:$url}}
</th>
<td width="{$percent}%" valign="top">
<? if ($id){ ?>
{beginAdmin}

<h3><a href="{$url}">{$data[title]}</a></h3>
<? if ($price){ ?>
{!$price2}
{{bask:button:$id}}
<? } ?>

{endAdminTop}
<? }else echo '&nbsp;'; ?></td>
<? }//	foreach ?></tr>
<? }//	while ?>
</table>
{!$p}
<? return $search; } ?>
