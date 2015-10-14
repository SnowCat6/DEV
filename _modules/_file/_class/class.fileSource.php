<?
class fileSource
{
	var $files;
	function fileSource($filesArray)
	{
		$this->files 	= $filesArray;
	}
	function getItemCount()
	{
		return count($this->files);
	}
	function next()
	{
		list($ix, $path)	= each($this->files);
		return $path?array(
			'type'	=> 'file',
			'title'	=> basename($path),
			'path'	=> $path,
			'URL'	=> imagePath2local($path)
		):NULL;
	}
};
?>