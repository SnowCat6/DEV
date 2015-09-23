<?
//	Сгенерировать страницу по пути сайта
function module_siteRender($val, &$content)
{
	//	ссылка запроса вида /page12345.htm без протокола и параметров
	$url		= meta::get(':URL');	//getRequestURL();

	//	Конфигурационный файл
	$ini		= getCacheValue('ini');
	//	Смотрим настройки щаблона из конфигурационного файла
	$template	= $ini[$url]['template'];
	//	Если не задано, смотрим шаблон для всех
	if (!$template) $template	= $ini[':']['template'];
	//	Если не задано, установть стандартный
	if (!$template) $template	= 'default';
	//	Собственно задать имя шаблона страницы
	setTemplate($template);

	//	Запуск сайта
	event('site.start', $url);
	//	Проверить наличие полностраничного кеша, если есть такой
	$ev	= array('url' => $url, 'content' => &$content);
	event('site.fullPageCache', $ev);
	//	Если страница не получена, сгенерировать
	if (is_null($content))	renderPage($url, $content);

	//	Завершить все выводы на экран
	//	Возможна постобработка страницы
	event('site.end',	$content);
}

///	Обработать страницу по заданному URL
function renderPage($requestURL, &$content)
{
	//	Событие для предобработки настроек, перезаписи ссылки, еще чего
	event('site.renderStart', $requestURL);

	//	Создать динамический контент страницы
	renderURL($requestURL, $content);

	//	Загрузка страницы
	$pageTemplate	= getTemplatePage(getTemplate());
	if (is_null($pageTemplate))
	{
		$pageTemplate	= getTemplatePage('default');
		module('message:url:error', "Template not found '$template'");
	}

	//	Если шаблон страницы есть, загрузить и подставить сгенерированный контент
	if ($pageTemplate)
	{
		//	Поместить контент в хранилище по умолчанию для отображения на странице
		if (!is_null($content)) moduleEx('page:display', $content);
		
		ob_start();
		include($pageTemplate);
		$content	= ob_get_clean();
		m("message:trace", "Included $pageTemplate file");
	}else{
		if (is_null($pageTemplate)){
			event('site.noTemplateFound', $renderedPage);
			module('message:url:error', "Template not found '$template'");
		}
	}
	//	Возможна постобработка
	event('site.renderEnd', $content);
}
//	Вызвать обработчик URL и вернуть результат как строку
function renderURL($requestURL, &$content)
{
	renderURLbase($requestURL, $content);
	//	Если все получилось, возыращаем результат
	if (!is_null($content)) return;

	//	Страница не найдена, но не все потеряно, возможно есть событийный обработчик
	$ev	= array('url' => $requestURL, 'content' => &$content);
	event('site.noUrlFound', $ev);
	//	Если все получилось, вовращаем результат
	if (!is_null($content)) return;

	$template	= getTemplate();
	if ($template != 'default' || m('page:title')) return;

	//	Увы, действительно страницы не  найдена
	event('site.noPageFound', $ev);
	if (!is_null($content)) return;
	
	module('message:url:error', "Page not found '$requestURL'");
}
//	Найти обработчик URL и вернуть страницу
function renderURLbase($requestURL, &$content)
{
	global $_CACHE;
	$parseRules	= $_CACHE['localURLparse'];

	//	Поищем обработчик URL
	foreach($parseRules as $parseRule => $parseModule)
	{
		if (!preg_match($parseRule, $requestURL, $parseResult)) continue;
		//	Если найден, то выполняем
//		unset($parseResult[count($parseResult)-1]);
		$content	= mEx($parseModule, $parseResult);
		//	Если все получилось, возвращаем результат
		if ($content) return;
	}
}
//	Получить реальный габлон страницы, возможно для специфического устройства
function getTemplatePage($template)
{
	if (!$template) return '';
	
	$pages	= getCacheValue('pages');
	if (isPhone())		$pageTemplate	= $pages["phone.page.$template"];
	else if(isTablet())	$pageTemplate	= $pages["tablet.page.$template"];
	if (!$pageTemplate)	$pageTemplate	= $pages["page.$template"];
	
	return $pageTemplate;
}
//	FullpageCache and fullpage module call @moduleName:param
function module_siteRenderEnd($val, &$renderedPage)
{
	$renderedPage	= preg_replace_callback('#{@([^}]+)}#',	
	function($val){
		return m($val[1]);
	}, $renderedPage);
}
?>