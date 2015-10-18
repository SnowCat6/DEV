<?
//	+function module_apacheFileExpires
function module_apacheFileExpires($val, &$inject)
{
	$gini	= getGlobalCacheValue('ini');
	if ($gini[':']['useExpires'] != 'yes') return;

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

<IfModule mod_headers.c>
	<FilesMatch "\.(ogg|ogv|svg|svgz|eot|otf|woff|mp4|ttf|rss|atom|jpg|jpeg|gif|png|ico|zip|tgz|gz|rar|bz2|doc|xls|exe|ppt|tar|mid|midi|wav|bmp|rtf)$">
		Header set Cache-Control "public, max-age=2592000, must-revalidate"
	</FilesMatch>
</IfModule>

## EXPIRES CACHING ##
';
}
?>
