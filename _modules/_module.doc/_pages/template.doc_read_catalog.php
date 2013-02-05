<?
function doc_read_catalog(&$db, &$search, &$data){
	if (!$db->rows()) return;
?>
<table>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
?>
<tr>
<th>
{beginCompile:catalogThumb}
<? displayThumbImage($title = docTitle($id), array(120, 150), '', '', $title) ?></th>
{endCompile:catalogThumb}
<td width="100%">
{beginAdmin}
{beginCompile:catalog}
<h3><a href="{$url}">{$data[title]}</a></h3>
<div>{{prop:read=id:$id;group:Свойства товара}}</div>
{endCompile:catalog}
{endAdminTop}
</td>
</tr>
<? } ?>
</table>
<? } ?>