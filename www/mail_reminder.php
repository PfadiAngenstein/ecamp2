<?php
	/*
	 * Reminds users of their todo's.
	 * This file is meant to be executed through cron-jobs.
	*/
	include("config.php");
	include("lib/mysql.php");
	include("lib/functions/mail.php");
	include("lib/functions/date.php");
	db_connect();

	// Get all open todo's with responsible leader and camp name
	$query = "
		SELECT todo.title, todo.short, todo.date, camp.short_name, user.id, user.mail, user.scoutname
		FROM todo
		LEFT JOIN todo_user_camp ON todo.id=todo_user_camp.todo_id
		LEFT JOIN user_camp ON todo_user_camp.user_camp_id=user_camp.id
		LEFT JOIN camp ON user_camp.camp_id=camp.id
		LEFT JOIN user ON user_camp.user_id=user.id
		WHERE todo.done <> 1;
	";

	$result = mysql_query( $query );
	
	if( ! mysql_num_rows( $result ) )
	{
		ecamp_send_mail($GLOBALS['error_mail'], "eCamp Fehler", "Notification für E-Mails konnten nicht abgefagt werden.<br>db error:<br>".mysql_error());
	}
	else
	{
		// get today's date
		$c_date = new c_date();
		$c_date->setUnix( time() );
		$today = $c_date->getValue();
		
		$arr = array();
		while ($d = mysql_fetch_array($result))
		{
			// If todo's date in next week (or a past date)
			$c_date->setDay2000( $d['date'] );
			$gap = $c_date->getValue() - $today;

			if ( in_array($gap, $GLOBALS['reminder-dates']) )
			{
				$a = array(
					'camp'		=> $d['short_name'],
					'title'		=> $d['title'],
					'desc'		=> $d['short'],
					'name'		=> $d['scoutname'],
					'mail'		=> $d['mail'],
					'date'		=> $c_date->getString( 'd.m.Y' ),
					'gap'		=> $gap
				);
				$arr[$d['id']][] = $a;
			}
		}

		foreach ($arr as $key => $v) {
			$text = "
				Hallo ".$v[0]['name']."<br>
				<br>
				Folgende Aufgaben müssen bald erledigt werden:<br>
				<br>
			";
			foreach ($v as $d) {
				$text .= '
					<table style="margin-left:20px;margin-bottom:20px;">
						<tr>
							<td style="padding-right:30px;">Lager</td>
							<td>'.$d['camp'].'</td>
						</tr>
						<tr>
							<td style="padding-right:30px;">Titel</td>
							<td>'.$d['title'].'</td>
						</tr>
						<tr>
							<td style="padding-right:30px;">Beschreibung</td>
							<td>'.$d['desc'].'</td>
						</tr>
						<tr>
							<td style="padding-right:30px;">Erledigen bis</td>
							<td>'.$d['date'].' (noch '.$d['gap'].' Tage Zeit)</td>
						</tr>
					</table>
				';
			}
			$text .= "
				<br>
				Bitte versuch, dem HLL zuliebe, diesen Aufgaben bis zum erwähnten Termin nachzukommen.<br>
				<br>
				Liebe Grüsse<br>
				eCamp
			";
			
			ecamp_send_mail($v[0]['mail'], "eCamp - Reminder", $text) . "<br>";
		}
	}
?>