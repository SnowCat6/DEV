<? function admin_cache(&$val, &$data)
{
	$site	= siteFolder();
	if (testValue('clearCode')){
		m('ajax:template', 'ajax_dialogMessage');

		$msg	= nl2br(execPHP("index.php clearCacheCode $site"));
		if ($msg) module('message', $msg);
		else  module('message', "Ошибка");
	}else
	if (testValue('clearCache')){
		m('ajax:template', 'ajax_dialogMessage');
		module('doc:clear');
		$msg	= nl2br(execPHP("index.php clearCache $site"));
		if ($msg) module('message', $msg);
		else  module('message', "Ошибка");
	}else
	if (testValue('recompileDocuments')){
		m('ajax:template', 'ajax_dialogMessage');
		module('doc:recompile');
		module('message', 'Документы скомпилированы');
	}else
	if (testValue('clearThumb')){
		m('ajax:template', 'ajax_dialogMessage');
		clearThumb(images);
		module('doc:clear');
		execPHP("index.php clearCache $site");
		module('message', 'Миниизображения удалены');
	};
}
?>