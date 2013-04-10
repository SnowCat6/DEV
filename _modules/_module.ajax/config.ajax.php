<?
addUrl('ajax_read_([\da-z]+)', 	'ajax:read');
addUrl('ajax_edit_(\d+)', 		'ajax:edit');

addEvent('site.renderStart','script_ajax');
?>