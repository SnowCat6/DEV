<?
function doc_page_product(&$db, &$menu, &$data){
	$id = $db->id();
?>
{beginAdmin}
<div class="product page">
{beginCompile:page}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td valign="top">
<? displayThumbImage($title = docTitle($id), array(250, 250), ' class="thumb"', '', $title) ?>
    </td>
    <td width="100%" valign="top">
    <h2>Характеристики</h2>
    {{prop:read=id:$id}}
    </td>
</tr>
</table>
<p>{document}</p>
{endCompile:page}
</div>
{endAdminTop}
<? } ?>