<? function admin_cache(&$val, &$data)
{
	$site	= siteFolder();
	if (testValue('clearCode')){
		m('ajax:template', 'ajax_dialogMessage');
		$msg	= execPHP("index.php clearCacheCode $site");
		if ($msg) module('message', "Кеш кода очищен.<div>$msg</div>");
		else  module('message', "Ошибка");
	}else
	if (testValue('clearCache')){
		m('ajax:template', 'ajax_dialogMessage');
		module('doc:clear');
		$msg	= execPHP("index.php clearCache $site");
		if ($msg) module('message', "Кеш очищен. <div>$msg</div>");
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