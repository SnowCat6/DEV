<?
//	+function docConfigUndo
function doc_docConfigUndo($db, $type, $data)
{
	if (!access('write', 'undo')) return;
	if (!$type) return;

	docConfig::setTemplate($type, $data);
	return true;
}
?>