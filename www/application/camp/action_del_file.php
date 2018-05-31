<?php
	if( $_user_camp->auth_level < 50 )
	{	
		$ans = array("error" => true, "msg" => "Keine Berechtigung für diese Aktion!");
		echo json_encode($ans);
	}
	else
	{
		$ans = array("error" => false, "msg" => "");
		if( ! empty($_GET['file']) ) {
			$fn = $_GET['file'];
			$path = utf8_decode(getcwd().'/files/'.$_camp->id.'/'.pathinfo($_GET['file'], PATHINFO_FILENAME).'.'.strtolower(pathinfo($_GET['file'], PATHINFO_EXTENSION)));
			$ans = (unlink($path)) ? array("error" => false, "msg" => "Datei wurde gelöscht.") : array("error" => true, "msg" => "Datei konnte nicht gelöscht werden.");
		}
	}

	echo json_encode($ans);
	die();
?>