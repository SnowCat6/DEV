<? function doc_read_comment($db, $val, $search)
{
	if (!$db->rows()) return;
?>
<div class="comment messages">
<h2>Комментарии</h2>
<? while($data = $db->next()){
	$id		= $db->id();
	$class	= $db->ndx == 1?' id="first"':'';
	$menu	= doc_menu($id, $data, false);
?>
<blockquote {!$class}>{beginAdmin}{document}{endAdmin}</blockquote>
<? } ?>
</div>
<? } ?>