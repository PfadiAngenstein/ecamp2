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

	$_page->html->set('main_macro', $GLOBALS[tpl_dir].'/global/content_box_fit.tpl/predefine');
	$_page->html->set('box_content', $GLOBALS[tpl_dir].'/application/story/home.tpl/home');
	
	$_page->html->set('box_title', 'Vollständiger Roter Faden');
	
	
	
    // Authentifizierung überprüfen
	// read & write --> Ab Lagerleiter (level: 50)
	// read         --> Ab Coach       (level: 20)
	$story_info = array();

	$days = array();
	$query = "SELECT d.* FROM day as d INNER JOIN subcamp as s ON d.subcamp_id = s.id WHERE s.camp_id = '$_camp->id' ORDER BY d.day_offset ASC ";
	$result = mysql_query($query);
	while($row = mysql_fetch_assoc($result))
	{
		$days[] = array(
								"id"			=> $row['id'],
								"day_offset"	=> $row['day_offset'],
								"t_edited"		=> $row['t_edited'],
								"story"			=> $row['story'],
								"notes"			=> $row['notes']
							);
	}

	$story_info['days'] = $days;
	
	$_page->html->set('story_info', $story_info);
	
?>