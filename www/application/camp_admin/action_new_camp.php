<?php
/*
 * Copyright (C) 2010 Urban Suppiger, Pirmin Mattmann
 *
 * This file is part of eCamp.
 *
 * eCamp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * eCamp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with eCamp.  If not, see <http://www.gnu.org/licenses/>.
 */

	$group_id		= mysql_real_escape_string($_REQUEST['groups']);
	$name 			= mysql_real_escape_string($_REQUEST['camp_name']);
	$short_name		= mysql_real_escape_string($_REQUEST['camp_short_name']);
	$group_name		= mysql_real_escape_string($_REQUEST['scout']);
	$function		= mysql_real_escape_string($_REQUEST['function_id']);
	$jstype			= mysql_real_escape_string($_REQUEST['jstype']);
	$is_course		= mysql_real_escape_string($_REQUEST['is_course']);
	$camp_type		= mysql_real_escape_string($_REQUEST['camp_type']);
	$course_type	= mysql_real_escape_string($_REQUEST['course_type']);
	$course_type_text = mysql_real_escape_string($_REQUEST['course_type_text']);
	
	
	$start		= mysql_real_escape_string($_REQUEST['camp_start']);
	$end		= mysql_real_escape_string($_REQUEST['camp_end']);
	
	$start = ereg("([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})", $start, $regs);
	$start = gmmktime(0, 0, 0, $regs[2], $regs[1], $regs[3]);
	
	$end = ereg("([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})", $end, $regs);
	$end = gmmktime(0, 0, 0, $regs[2], $regs[1], $regs[3]);
	
	$c_start = new c_date;
	$c_end = new c_date;
	
	$c_start->setUnix($start);
	$c_end->setUnix($end);
	
	
	$length = $c_end->getValue() - $c_start->getValue() + 1;
	$start = $c_start->getValue();
	$ende = $c_end->getValue();
	
	
	$is_course = ($is_course ==1) ? 1 : 0;
	if( !$is_course )	{	$type = 0;	}
	else				{	$type = $course_type;	}
	
	
	if($length <= 0 )
	{
		echo "Das Enddatum darf nicht vor dem Startdatum liegen!";
		echo "<br /><a href='javascript:history.back()'>Zur&uuml;ck</a>";
		die();
	}
	else if( $length > 40 )
	{
		echo "Die maximale L&auml;nge eines Lagers betr&auml;gt 40 Tage.";
		echo "<br /><a href='javascript:history.back()'>Zur&uuml;ck</a>";
		die();
	}
	
	
	// Lager hinzufügen
	$query = "INSERT INTO camp (group_id, name ,group_name, short_name, is_course, jstype, type, type_text, creator_user_id)
						VALUES ('$group_id', '$name', '$group_name', '$short_name', $is_course, '$jstype', '$type', '$course_type_text', '$_user->id')";
	mysql_query($query);
	
	
	$last_camp_id = mysql_insert_id();
	
	// Kateogiren hinzufügen
	if( $is_course )
	{
		$query = "INSERT INTO category (camp_id, name, short_name, color, form_type)
					VALUES 
						('$last_camp_id', 'Ausbildung', 'A', '548dd4' , 4), 
						('$last_camp_id', 'Pfadi erleben', 'P', 'ffa200' , 4), 
						('$last_camp_id', 'Roter Faden', 'RF', '14dd33' , 4),
						('$last_camp_id', 'Gruppestunde', 'GS', '99ccff' , 4),
						('$last_camp_id', 'Essen', '', 'bbbbbb' , 0),
						('$last_camp_id', 'Sonstiges', '', 'FFFFFF' , 0)";
	}
	else
	{
		$query = "INSERT INTO category (camp_id, name, short_name, color, form_type)
					VALUES 
						('$last_camp_id', 'Essen', 'ES', 'bbbbbb' , 0),
						('$last_camp_id', 'Lagerprogramm', 'LP', '99ccff' , 3), 
						('$last_camp_id', 'Lageraktivität', 'LA', 'ffa200' , 2), 
						('$last_camp_id', 'Lagersport', 'LS', '14dd33' , 1)";
	}
	mysql_query($query);
	
	
	// ToDo std. einfüllen
	if( $is_course )
	{
		$query = "INSERT INTO todo (camp_id, title, short, date)
					VALUES
						('$last_camp_id', 'Kursanmeldung',  					'Anmeldung an LKB (Picasso, Blockübersicht, Checklisten)', " . ( $start - 8 * 7 ) . "),
						('$last_camp_id', 'Detailprogramm einreichen', 			'Definitives Detailprogramm an LKB.', " . ( $start - 2 * 7 ) . "),
						('$last_camp_id', 'Kursabschluss', 						'TN-Liste, Kursbericht', " . ( $ende + 3 * 7 ) . "),
						('$last_camp_id', 'J+S-Material/Landeskarten', 			'J+S-Material und Landeskarten bestellen.', " . ( $start - 6 * 7 ) . ")";

	}
	// Wolfsstufe
	else if ( $group_id == 6 )
	{
		$query = "INSERT INTO todo (camp_id, title, short, date)
					VALUES
						('$last_camp_id', 'Lagerhaus reservieren', 				'Das Lagerhaus definitiv reservieren.', " . ( $start - 8 * 30 ) . "),
						('$last_camp_id', 'Küche suchen', 						'Das Küchenteam zusammenstellen.', " . ( $start - 6 * 30 ) . "),
						('$last_camp_id', 'Lagerplanung', 						'Hock machen und Picasso und Roter Faden ausplanen', " . ( $start - 10 * 7 ) . "),
						('$last_camp_id', 'Lageranmeldung', 					'Picasso an Coach schicken für Lageranmeldung', " . ( $start - 7 * 7 ) . "),
						('$last_camp_id', 'J+S Materialbestellung', 			'J+S Materialbestellung ausfüllen und an Coach schicken', " . ( $start - 6 * 7 ) . "),
						('$last_camp_id', 'Materialbestellung', 				'Materialbedarf zusammentragen und Bestellung an Hobbes', " . ( $start - 6 * 7 ) . "),
						('$last_camp_id', 'Lageranmeldung', 					'Sicherstellen, dass Anmeldetalon mit Versand versandt wurde', " . ( $start - 6 * 7 ) . "),
						('$last_camp_id', 'Lagersiko erstellen', 				'Sicherheitskonzept für ganzes Lager (+ evtl. für Aufbau/Abbau) schreiben', " . ( $start - 5 * 7 ) . "),
						('$last_camp_id', 'Programmabgabe', 					'Alle Blöcke sind ausgeplant und auf 100% im ecamp', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Angaben für Budget', 				'Preise für Eintritte, Reise etc. an Verantwortlichen', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Landeskartenbestellung', 			'Landeskartenbestellung ausfüllen und an Coach schicken', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Reservation Zugreisen', 				'Alle Züge mit Formular reservieren + Stempel von Sportamt', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Broschüre erstellen',				'Infos einholen und mit neisem Disein versehen', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'J+S Lageranmeldung', 				'Sicherstellen, dass Coach das Lager unter J+S anmeldet (SPORTdb)', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Bettelbriefe',						'Optional: Bettelbriefe an lokale Firmen schicken (z.B. Jenzer)', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Lagerdossier einreichen', 			'Fertiges Lagerdossier an Coach & AL abgeben + Termin Feedbackhock', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'Broschüre versenden', 				'kla soweit?', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'Budget einreichen', 					'Budget mit Vorlage erstellen und an AL einreichen', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'TN-Liste', 							'Definitive TN-Liste in MiData und Coach informieren', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'Kollektiv holen', 					'Kollektiv für reservierte Reisen am Schalter holen und bezahlen', " . ( $start - 2 * 7 ) . "),
						('$last_camp_id', 'Siebdruck anfertigen', 				'Siebdruck / Lagerdruck anfertigen', " . ( $start - 2 * 7 ) . ")";
	}
	// Pfadistufe
	else if ( $group_id == 7 )
	{
		$query = "INSERT INTO todo (camp_id, title, short, date)
					VALUES
						('$last_camp_id', 'Lagerplatz reservieren', 			'Den Lagerplatz definitiv reservieren.', " . ( $start - 8 * 30 ) . "),
						('$last_camp_id', 'Fouriere suchen', 					'Fourierteam zusammenstellen.', " . ( $start - 6 * 30 ) . "),
						('$last_camp_id', 'Lagerplanung', 						'2 Höcke machen und Picasso und Roter Faden ausplanen', " . ( $start - 6 * 30 ) . "),
						('$last_camp_id', 'Lageranmeldung', 					'Picasso an Coach schicken für Lageranmeldung', " . ( $start - 7 * 7 ) . "),
						('$last_camp_id', 'J+S Materialbestellung', 			'J+S Materialbestellung ausfüllen und an Coach schicken', " . ( $start - 6 * 7 ) . "),
						('$last_camp_id', 'Mattransport', 						'An- & Abtransport mit Formular (in Dropbox) organisieren', " . ( $start - 6 * 7 ) . "),
						('$last_camp_id', 'Materialbestellung', 				'Materialbedarf zusammentragen und Bestellung an Hobbes', " . ( $start - 6 * 7 ) . "),
						('$last_camp_id', 'Lageranmeldung', 					'Sicherstellen, dass Anmeldetalon mit Versand versandt wurde', " . ( $start - 6 * 7 ) . "),
						('$last_camp_id', 'Programmabgabe', 					'Alle Blöcke sind ausgeplant und auf 100% im ecamp', " . ( $start - 5 * 7 ) . "),
						('$last_camp_id', 'Angaben für Budget', 				'Preise für Eintritte, Reise, Holz etc. an Verantwortlichen', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Landeskartenbestellung', 			'Landeskartenbestellung ausfüllen und an Coach schicken', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Holz organisieren', 					'Schwarten bei Sägerei bestellen und Transport organisieren', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Reservation Zugreisen', 				'Alle Züge mit Formular reservieren + Stempel von Sportamt', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Broschüre erstellen',				'Infos einholen und mit neisem Disein versehen', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'J+S Lageranmeldung', 				'Sicherstellen, dass Coach das Lager unter J+S anmeldet (SPORTdb)', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Bettelbriefe',						'Optional: Bettelbriefe an lokale Firmen schicken (z.B. Jenzer)', " . ( $start - 4 * 7 ) . "),
						('$last_camp_id', 'Lagerdossier einreichen', 			'Fertiges Lagerdossier an Coach & AL abgeben + Termin Feedbackhock', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'Broschüre versenden', 				'kla soweit?', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'Budget einreichen', 					'Budget mit Vorlage erstellen und an AL einreichen', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'TN-Liste', 							'Definitive TN-Liste in MiData und Coach informieren', " . ( $start - 3 * 7 ) . "),
						('$last_camp_id', 'Kollektiv holen', 					'Kollektiv für reservierte Reisen am Schalter holen und bezahlen', " . ( $start - 2 * 7 ) . "),
						('$last_camp_id', 'Siebdruck anfertigen', 				'Siebdruck / Lagerdruck anfertigen', " . ( $start - 2 * 7 ) . "),
						('$last_camp_id', 'Auf-/Abbau planen', 					'Plan für Aufbau erstellen (wer? wann? was? wo?)', " . ( $start - 1 * 7 ) . ")";
	}
	else
	{

	}
	mysql_query( $query );
	
	
	// Tages-chef hinzufügen
	$query = "INSERT INTO job (camp_id, job_name, show_gp)
							VALUES ('$last_camp_id', 'Tageschef', '1')";
	mysql_query($query);
	
	
	// Materiallisten hinzufügen:
	$query = "INSERT INTO mat_list ( camp_id, name )
							VALUES( '$last_camp_id', 'Organisieren' )";
	mysql_query( $query );
	
	$query = "INSERT INTO mat_list ( camp_id, name )
							VALUES( '$last_camp_id', 'Mitnehmen' )";
	mysql_query( $query );

	$query = "INSERT INTO mat_list ( camp_id, name )
							VALUES( '$last_camp_id', 'Verkleidungen' )";
	mysql_query( $query );
	
	
	
	// Eigenen User hinzufügen
	$query = "INSERT INTO user_camp (user_id, camp_id, function_id, active)
							VALUES	($_user->id, $last_camp_id, $function, '1')";
	mysql_query($query);
	
	
	// Subcamp hinzufügen
	$query = "INSERT INTO subcamp 	(camp_id, start, length)
						VALUES	($last_camp_id, $start, $length)";
	mysql_query($query);
	$last_subcamp_id = mysql_insert_id();
	
	// Days hinzufügen
	$days = array();
	
	for($i=0; $i < $length; $i++)
	{	$days[] = "('$last_subcamp_id', '$i')";	}
	
	$query = "INSERT INTO day (subcamp_id, day_offset) VALUES ";
	$query .= implode( ", ", $days );
	
	mysql_query($query);

	
	
	
	
	$result = mysql_query("SELECT id FROM user_camp WHERE user_id='$_user->id' AND camp_id='$last_camp_id'");
	if( mysql_num_rows($result) == 0 )
	{
		$_SESSION[camp_id] = 0;
		header("Location: index.php?app=home");
		die();
	}

	$_SESSION[camp_id] = $last_camp_id;
	
	$query = "UPDATE user SET last_camp = '$last_camp_id' WHERE id = '" . $_user->id . "'";
	mysql_query($query);
	
	
	header("Location: index.php?app=camp&cmd=home&show=firsttime");
	die();

?>