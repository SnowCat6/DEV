<? function doc_read_docSelect_before(&$db, &$val, &$search){
}?>
<? function doc_read_docSelect(&$db, &$val, &$search){
	setTemplate('');
?>
{{page:title=Выберите родительский элемент}}
<a href="#" class="selectClose">Закрыть</a>
<div class="docSelect">
<? while($data = $db->next()){
	$id	= $db->id();
?>
<a href="#" rel="{$id}">{$data[title]}</a>
<? } ?>
</div>
<? } ?>