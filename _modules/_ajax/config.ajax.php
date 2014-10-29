<?
addUrl('ajax_read_([\da-z]+)', 	'ajax:read');
addUrl('ajax_edit_(\d+)', 		'ajax:edit');
addUrl('ajax_edit', 			'ajax:edit');
addUrl('ajax_property', 		'ajax:property');

addEvent('site.renderStart',	'script_ajax');
?>