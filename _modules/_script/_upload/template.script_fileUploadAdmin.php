<? function script_fileUploadAdmin($val){
	m('script:jq');
	m('script:overlay');
	m('script:ajaxForm');

	m('script:editorFCK4finder');
	
	m('fileLoad', 	'css/fileUploadAdmin.css');
	m('scriptLoad', 'script/fileUploadAdmin.js');
}?>