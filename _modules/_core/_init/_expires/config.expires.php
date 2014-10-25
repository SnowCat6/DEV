<?
$gini	= getGlobalCacheValue('ini');
if ($gini[':']['useExpires'] == 'yes'){
	addEvent('htaccess.inject',		'apacheFileExpires');
}
addEvent('admin.settings.global',	'admin:expires');

function module_apacheFileExpires($val, &$inject)
{
	$htaccess	= &$inject['before'];
	$htaccess	.= '
## EXPIRES CACHING ##
<IfModule mod_expires.c>
#ExpiresActive On
ExpiresByType image/jpg "access 1 year"
ExpiresByType image/jpeg "access 1 year"
ExpiresByType image/gif "access 1 year"
ExpiresByType image/png "access 1 year"
ExpiresByType text/css "access 1 month"
ExpiresByType application/pdf "access 1 month"
ExpiresByType text/x-javascript "access 1 month"
ExpiresByType application/x-shockwave-flash "access 1 month"
ExpiresByType image/x-icon "access 1 year"
</IfModule>
## EXPIRES CACHING ##
';
}
?>