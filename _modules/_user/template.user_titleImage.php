<?
//	+function user_titleImage
function user_titleImage($id, &$data)
{
	$db	= user::db();
	if (!is_array($data)) $data = array();
	
	if (access("write", "user:$id"))
	{
		$d		= $db->openID($id);
		if (!$d) return;
		
		$folder					= $db->folder($id);
		$data['uploadFolder']	= array("$folder/Title");
		moduleEx("file:image:user$id", $data);
	}else{

		$hash	= hashData($data);
		if (beginCache("titleImage$hash", "user$id"))
		{
			$d	= $db->openID($id);
			if ($d){
				$folder	= $db->folder($id);
		
				$data['uploadFolder']	= array("$folder/Title");
				moduleEx("file:image:user$id", $data);
			}
			
			endCache();
		}
	}

	echo $cache;
}

?>