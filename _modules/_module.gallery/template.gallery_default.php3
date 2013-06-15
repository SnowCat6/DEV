<?
function gallery_default($val, &$data)
{
	$files = getFiles($data['src']);
	if (!$files) return;

	$row = 0; $cols = 4;
	for($ix = 0; $ix < count($files); ++$row){
		for($iix = 0; $iix < $cols; ++$iix){
			$path			= '';
			@list(,$path)	= each($files); ++$ix;
			$table[$row][]	= $path;
		}
	}
	$class = ' id="first"';
?>
<link rel="stylesheet" type="text/css" href="gallery.css"/>
<table border="0" cellspacing="0" cellpadding="0" class="gallery" align="center">
<? foreach($table as $row){ ?>
<tr {!$class}>
<? $class2 = ' id="first"'; foreach($row as $path){?>
    <td {!$class2}><a href="<?= htmlspecialchars($path)?>" rel="lightbox"><? displayThumbImage($path, array(150, 150))?></a></td>
<? $class2 = NULL; } ?>
</tr>
<? $class = NULL; } ?>
</table>
<? } ?>