<?
addUrl('feedback_all',					'feedback:all');
addUrl('feedback_edit_([a-zA-Z\d]+)',	'feedback:edit');
addUrl('feedback_edit',					'feedback:edit:new');
addUrl('feedback_([a-zA-Z\d]+)',		'feedback:display');

addEvent('admin.tools.settings','feedback:tools');
addAccess('feedback:(.*)',		'feedback_access');

addEvent('config.end',			'feedback:snippets');
?>