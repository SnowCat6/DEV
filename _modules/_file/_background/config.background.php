<?
addUrl('admin_background',	'file:backgroundAdmin');
addEvent('admin.tools.edit','file:backgroundTools');

addAccess('background:(.*)',	'access:admin,developer,writer,manager');
?>