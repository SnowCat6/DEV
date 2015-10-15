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
	function nextItem()
	{
		list($ix, $path)	= each($this->files);
		return $path?new fileItem($path):NULL;
	}
};

class fileItem extends dbSourceItem
{
	var $filePath;
	function fileItem($filePath)
	{
		$this->filePath = $filePath;
	}
	//	Identity item
	function itemId()
	{
	}
	//	URL for site navigate
	function itemURL($param = NULL)
	{
		return imagePath2local($this->filePath);
	}
	//	Files store folder
	function itemFolder()
	{
		return dirname($this->filePath);
	}
	//	Get current item data
	function getData()
	{
		return array(
			'type'	=> 'file',
			'title'	=> basename($this->filePath),
			'path'	=> $this->filePath,
			'URL'	=> $this->itemURL()
		);
	}
};
?>