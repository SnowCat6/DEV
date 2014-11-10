<? function admin_cache(&$val, &$data)
{
	$site	= siteFolder();
	if (testValue('clearCode')){
		$msg	= execPHP("index.php clearCacheCode $site");
		if ($msg) module('message', "Кеш кода очищен.<div>$msg</div>");
		else  module('message', "Ошибка");
		m('ajax:template', 'ajax_dialogMessage');
	}else
	if (testValue('clearCache')){
		module('doc:clear');
		$msg	= execPHP("index.php clearCache $site");
		if ($msg) module('message', "Кеш очищен. <div>$msg</div>");
		else  module('message', "Ошибка");
		m('ajax:template', 'ajax_dialogMessage');
	}else
	if (testValue('recompileDocuments')){
		module('doc:recompile');
		module('message', 'Документы скомпилированы');
		m('ajax:template', 'ajax_dialogMessage');
	}else
	if (testValue('clearThumb')){
		clearThumb(images);
		module('doc:clear');
		execPHP("index.php clearCache $site");
		module('message', 'Миниизображения удалены');
		m('ajax:template', 'ajax_dialogMessage');
	};
}
?>