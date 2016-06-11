<?
//	+function snippets_undo_add
function snippets_undo_add($snippetName, $data)
{
	if (!access('write', 'undo')) return;
	snippetsWrite::add($snippetName, $data);
	return true;
}
//	+function snippets_undo_delete
function snippets_undo_delete($snippetName, $data)
{
	if (!access('write', 'undo')) return;
	snippetsWrite::delete($snippetName);
	return true;
}

?>