<?
function doc_page_product(&$db, &$menu, &$data){
	$id		= $db->id();
	$folder	= $db->folder();
	$price	= docPriceFormat2($data);
	m('script:scroll');
	m('script:lightbox');
?>
{beginAdmin}
<div class="product page">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <th width="250" valign="top">
{beginCompile:productPageImage}
<? displayThumbImage($title = docTitleImage($id), array(250, 350), ' class="thumb"', '', $title) ?>
{{gallery:small=src:$folder/Gallery}}
{endCompile:productPageImage}
    </th>
    <td width="100%" valign="top">
    {!$price}
    {{bask:button:$id}}<br />
<? if ($p = m('prop:read', array('id'=>$id))){ ?>
    <h2>Характеристики</h2>
    {!$p}
<? } ?>
    </td>
</tr>
</table>
<p>{document}</p>
</div>
{endAdminTop}
<? event('document.comment',	$id)?>
<? } ?>