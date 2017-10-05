<?
addEvent('document.gallery',	'doc:gallery');
addSnippet('gallery', 			'{{doc:gallery}}');
addUrl("gallery_adminImageMask(\d+)",		'gallery:adminImageMask');

$galleryTypes = getCacheValue(':galleryTypes', array());
$galleryTypes['default']	= 'gallery_default';
$galleryTypes['plain']		= 'gallery_plain';
setCacheValue(':galleryTypes', $galleryTypes);
?>