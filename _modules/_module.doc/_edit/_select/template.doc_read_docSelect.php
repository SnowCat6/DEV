<? function doc_read_docSelect_before(&$db, &$val, &$search){
	$search[':order']	= 'name';
}?>
<? function doc_read_docSelect(&$db, &$val, &$search){
	setTemplate('');
?>
{{page:title=Выберите родительский элемент}}
<div class="holder">
<? while($data = $db->next()){
	$id	= $db->id();
?>
<a href="#" rel="{$id}">{$data[title]}</a>
<? } ?>
</div>
<? } ?>