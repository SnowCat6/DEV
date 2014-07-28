<? function doc_read_docAll_before(&$db, $val, &$search)
{
	$search[':sort']	= 'sort';
}
function doc_read_docAll(&$db, $val, &$search)
{
	$type	= $search['type'];
	$db2	= module('doc');
	
	$s		= array();
	$s['search']	= getValue('search');
	$s['template']	= getValue('template');
	removeEmpty($s);
	if ($db->rows() == 0) return;
	m('script:preview');
?>
<?= $p = dbSeek($db, 15, $s); ?>
<table class="table all" cellpadding="0" cellspacing="0" width="100%">
<tr>
  <th>&nbsp;</th>
  <th><input type="checkbox" name="documentSelectAll" value="all" title="Применить ко всем документам" /></th>
  <th>&nbsp;</th>
  <th>Заголовок</th>
</tr>
<tbody id="sortable">
<?	
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
		$drag	= docDraggableID($id, $data);
?>
<tr>
  <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td>
<input type="hidden" name="documentOrder[]" value= "{$id}" />
<input type="checkbox" name="documentDelete[]" value="{$id}" />
    </td>
    <td><a href="{{getURL:page_edit_$id}}" id="ajax_edit"><b>{$id}</b></a></td>
    <td width="100%">
    <a href="{!$url}"{!$drag} class="preview">{$data[title]}</a>
    <div><small><?
$split	= '';
$parents = getPageParents($id);
foreach($parents as $iid){
	$d		= $db2->openID($iid);
	$s2		= $s;
	$s2['search']['parent*']	= $iid;
	$url	= getURL('#', makeQueryString($s2));
?>
{!$split}<a href="{!$url}" class="seekLink">{$d[title]}</a>
<? $split = ' &gt; '; } ?></small></div>
    </td>
</tr>
<?	} ?>
</tbody>
</table>
{!$p}
<? } ?>
