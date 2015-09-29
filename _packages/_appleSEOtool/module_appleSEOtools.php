<? function module_appleSEOtools($val, $data)
{
	extract($GLOBALS);
// ЧПУ ---
  if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/d-url-rewriter.php')) {
    include_once($_SERVER['DOCUMENT_ROOT'] . '/d-url-rewriter.php');
    durRun ();
  }
// --- ЧПУ
}?>