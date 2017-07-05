<?
addUrl('feedback_all',					'feedback:all');
addUrl('feedback_edit_([a-zA-Z\d]+)',	'feedback:edit');
addUrl('feedback_edit',					'feedback:edit:new');
addUrl('feedback_([a-zA-Z\d]+)',		'feedback:display');

addEvent('admin.tools.edit',	'feedback:tools');
addAccess('feedback:(.*)',		'feedback_access');

addEvent('config.end',			'feedback:snippets');
//addEvent('config.end',			'feedback_end');

function module_feedback_end($val, $cacheRoot)
{
	$fileName = "design/politikaconf.rtf";
	system_init::addExcludeRegExp('#politikaconf#');
	
	$base= dirname(__FILE__);
	$ctx = file_get_contents("$base/$fileName");

	$replace = array(
		'%SITE_NAME%' 		=> '',
		'%SITE_COMPANY%' 	=> '',
		'%SITE_HOST%' 		=> '',
		'%SITE_MAIL%' 		=> '',
	);
	
	foreach($replace as $get => $set){
		$ctx = str_replace($get, $set, $ctx);
	}
	
	$file = $cacheRoot.'/'.localSiteFiles.'/'.$fileName;
	file_put_contents($file, $ctx);
}
?>