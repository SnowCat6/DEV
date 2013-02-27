<?
function doc_page_product(&$db, &$menu, &$data){
	$id		= $db->id();
	$folder	= $db->folder();
	$price	= docPriceFormat2($data);
	module('script:scroll');
?>
{beginAdmin}
<div class="product page">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <th width="250" valign="top">
{beginCompile:productPageImage}
<? displayThumbImage($title = docTitle($id), array(250, 350), ' class="thumb"', '', $title) ?>
{{gallery:small=src:$folder/Gallery;target:imageHolder}}
{endCompile:productPageImage}
    </th>
    <td width="100%" valign="top">
    {!$price}
    {{bask:button:$id}}<br />
    <h2>Характеристики</h2>
{beginCompile:productPageProp}
    {{prop:read=id:$id}}
{endCompile:productPageProp}
    </td>
</tr>
</table>
<p>{document}</p>
</div>
{endAdminTop}
<? } ?>