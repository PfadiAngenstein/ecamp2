<?php
	if( $_user_camp->auth_level < 50 )
	{	
		$ans = array("error" => true, "msg" => "Keine Berechtigung für diese Aktion!");
		echo json_encode($ans);
	}
	else
	{
		if (!empty($_FILES['files'])) {
			$ans = array("error" => false, "msg" => "");
			if (!file_exists(getcwd().'/files/'.$_camp->id)) {
				mkdir(getcwd().'/files/'.$_camp->id);
			}
			foreach($_FILES['files']['name'] as $i=>$n) {
				try {
					move_uploaded_file($_FILES['files']['tmp_name'][$i], utf8_decode(getcwd().'/files/'.$_camp->id.'/'.pathinfo($n, PATHINFO_FILENAME).'.'.strtolower(pathinfo($n, PATHINFO_EXTENSION))));
				} catch (Exception $e) {
					$ans = array("error" => true, "msg" => "Verschieben der Datei fehlgeschlagen: ".$e);
					echo json_encode($ans);
					die();
				}
			}
		}
	}

	echo json_encode($ans);
	die();
?>