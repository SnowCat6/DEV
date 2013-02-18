<?
function doc_read_menuTable(&$db, $val, &$search){
	if (!$db->rows()) return $search;
	$percent= floor(100/$db->rows());
	$ddb	= module('doc');
	module('script:menu');
?>
<table class="menu popup" cellpadding="0" cellspacing="0" width="100%">
<tr>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$class	= currentPage() == $id?' class="current"':'';
	$menu	= doc_menu($id, $data, true);
	$split	= $db->ndx == 1?' id="first"':'';
?>
<td {!$class}{!$split} width="{$percent}%">
{beginAdmin}<a href="{$url}" title="{$data[title]}">{$data[title]}</a><? endAdmin($menu, $val?false:true) ?>
<?
$ddb->open(doc2sql(array('parent' => $id)));
if ($ddb->rows()){
	echo '<ul>';
	while($data = $ddb->next()){
		$id		= $ddb->id();
		$title	= htmlspecialchars($data['title']);
		$url	= getURL($ddb->url());
		$split2	= $ddb->ndx == 1?' id="first"':'';
		echo "<li$split2><a href=\"$url\">$title</a></li>";
	}
	echo '</ul>';
}
?>
</td>
<? } ?>
</tr>
</table>
<? return $search; } ?>