<?
function doc_page_product(&$db, &$menu, &$data){
	$id		= $db->id();
	$folder	= $db->folder();
	$price	= docPriceFormat2($data);
?>
{beginAdmin}
<div class="product page">
{beginCompile:page}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <th width="250" valign="top">
<? displayThumbImage($title = docTitle($id), array(250, 350), ' class="thumb"', '', $title) ?>
{{gallery:small=src:$folder/Gallery;target:imageHolder}}
    </th>
    <td width="100%" valign="top">
    <h2>Характеристики</h2>
    {!$price}
    {{bask:button:$id}}
    {{prop:read=id:$id}}
    </td>
</tr>
</table>
<p>{document}</p>
{endCompile:page}
</div>
{endAdminTop}
<? } ?>