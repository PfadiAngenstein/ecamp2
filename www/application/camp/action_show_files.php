<?php
	if( $_user_camp->auth_level < 50 )
	{	
		$ans = array("error" => true, "msg" => "Keine Berechtigung für diese Aktion!");
		echo json_encode($ans);
	} 
	else
	{
		$ans = array("error" => false, "msg" => "foo");
		$raw = array_diff(scandir( getcwd().'/files/'.$_camp->id ), array('..', '.'));
		$files = array();
		foreach($raw as $file) {
			$path = getcwd().'/files/'.$_camp->id.'/'.$file;
			$ext = pathinfo($path, PATHINFO_EXTENSION);
			$ext = str_replace('jpeg', 'jpg', $ext);
			$ext = str_replace(array('xlsx','xlsm','xltx','csv'), 'xls', $ext);
			$ext = str_replace(array('docx','docm','dotx','dotm','odt'), 'doc', $ext);
			$ext = (!glob(getcwd().'/public/global/img/file_'.$ext.'.svg')) ? 'default' : $ext;
			$tmp = array(
				"name"		=> utf8_encode($file),
				"ext"		=> $ext,
				"size"		=> filesize($path),
				"edited"	=> filemtime($path),
				"camp"		=> $_camp->id
			);
			array_push($files, $tmp);
		}
		$ans["msg"] = json_encode($files);
	}

	echo json_encode($ans);
	die();
?>