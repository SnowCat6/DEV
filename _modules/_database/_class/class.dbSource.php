<?
/*****************************/
class dbSource
{
	function getItemCount()
	{
	}
	// Data access
	function getSeek()
	{
	}
	function setSeek($ix)
	{
	}
	function nextItem()
	{
	}
	//	Get current item
	function getData()
	{
	}
	//	Update
};

/*****************************/
class dbSourceItem implements ArrayAccess
{
	//	Identity item
	function itemId()
	{
	}
	//	URL for site navigate
	function itemURL($param = NULL)
	{
	}
	//	Files store folder
	function itemFolder()
	{
	}
	//	Get current item data
	function getData()
	{
	}
/*****************************/
    public function offsetSet($offset, $value) {
    }

    public function offsetExists($offset) {
		$data	= $this->getData();
        return isset($data[$offset]);
    }

    public function offsetUnset($offset) {
    }

    public function offsetGet($offset) {
		$data	= $this->getData();
        return $data[$offset];
    }
};

/*****************************/
class dbItem
{
	var $id;
	var $data;
	var $link;
	var $folder;
	//
	function dbItem($db, $data)
	{
		$this->data = $data;
		$this->id 	= $data[$db->key];
		$this->link = $db->url($this->itemId());
		$this->folder= $db->folder($this->itemId());
	}
	//	Identity item
	function itemId()
	{
		return $this->id;
	}
	//	URL for site navigate
	function itemURL($param = NULL)
	{
		return getURL($this->link, $param);
	}
	//	Files store folder
	function itemFolder()
	{
		return $this->folder;
	}
	//	Get current item data
	function getData()
	{
		return $this->data;
	}
/******************************/
    public function offsetSet($offset, $value) {
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }
};
?>