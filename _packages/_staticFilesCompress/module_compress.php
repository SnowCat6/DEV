<? function module_compress($val, &$data)
{
	if ($_SERVER['QUERY_STRING'] != 'URL=pageNotFound404') return;
	
	$file	= cacheRootPath . $_SERVER['REQUEST_URI'];
	if (!preg_match('#^(_cache/[^/]+/siteFiles/.*\.(css|js))#', $file, $v)) return;

	$fileIn	= $v[1];
	$fileOut= "$fileIn.gz";

	if (!file_exists($fileIn)) return;

	$ctx	= file_get_contents($fileIn);
	file_put_contents($fileOut, gzencode($ctx));

	$mime		= array();
	$mime['css']= 'text/css';
	$mime['js']	= 'text/javascript';
	
	$ext		= strtolower($v[2]);
	$mime		= $mime[$ext];

	header("HTTP/1.1 200 OK");
	if ($mime) header("Content-Type: $mime");
	
	moduleEx('gzip', $ctx);
	echo $ctx;

	die;
}
?>