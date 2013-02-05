<?
//	Корреткировка ссылок на рексурсы для использования в редактировании и обратно
function module_prepare($val, &$data)
{
	switch($val){
	case '2public':
			return local2public(&$data);
	case '2local':
			return public2local(&$data);
	}
	
}
//	Скорректировать ссыли так, чтобы указывали на абсолютный путь к файлу на сайте
//	images/image.jpg => /dev/_sires/localhost/images/image.jpg
function local2public(&$data)
{
	if (is_array($data)){
		foreach($data as $name => &$v) local2public($v);
	}else{
		$publicPath = globalRootURL.'/'.localHostPath.'/';
		
		$data = preg_replace('%(src\s*=\s*[\'\"])(?!\w+://)([\w\d])%i', "\\1$publicPath\\2", $data);
	}
}
//	Скорректировать ссылки так, чтобы абсолютный путь к файлу, стал относительным
//	/dev/_sires/localhost/images/image.jpg => images/image.jpg
function public2local(&$data)
{
	if (is_array($data)){
		foreach($data as $name => &$v) public2local($v);
	}else{
		$publicPath	= preg_quote(globalRootURL.'/'.localHostPath.'/', '#');
		$publicPath2= preg_quote(globalRootURL.'/', '#');
		$serverURL	= preg_quote("http://$_SERVER[HTTP_HOST]", '#');
		
		$data	= preg_replace("#([\'\"])$serverURL#i",			"\\1", 	$data);
		$data	= preg_replace("%([\'\"])[/]?$publicPath%i",	"\\1", 	$data);
		$data	= preg_replace("#([\'\"])[/]?$publicPath2#i",	"\\1", 	$data);
		$data	= preg_replace("#([\'\"])/([^\'\"]*)#i",		"\\1\\2", $data);
		//	Сделать, автоматически копировать ресурсы с внешнего источника
//		module("contentCopy:$baseFolder", &$data));
	}
}
?>