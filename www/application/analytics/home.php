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

	$_page->html->set('main_macro', $GLOBALS['tpl_dir'].'/global/content_box_fit.tpl/predefine');
	$_page->html->set('box_content', $GLOBALS['tpl_dir'].'/application/analytics/home.tpl/home');
	
	$_page->html->set('box_title', 'Analyse');



	// J+S Konformität
	$query = "SELECT id, camp_id, name, short_name FROM category WHERE camp_id = " . $_camp->id;
	$result = mysql_query($query);

	$category_ls = 0;
	$category_la = 0;
	$category_es = 0;

	while ( $category = mysql_fetch_assoc( $result )) {
		if($category['name'] == "Lagersport" || $category['short_name'] == "LS") {
			$category_ls = $category['id'];
		}
		if($category['name'] == "Lageraktivität" || $category['short_name'] == "LA") {
			$category_la = $category['id'];
		}
		if($category['name'] == "Essen" || $category['short_name'] == "ES") {
			$category_es = $category['id'];
		}
	}

	if($category_ls == 0 || $category_la == 0) {
		$ans = array( "error" => true, "msg" => "Keine Kategorien 'LS' und/oder 'LA' gefunden!" );
		echo json_encode( $ans );
		die();
	}

	$days = array();
	$query = "	SELECT
					day.id,
    				(day.day_offset + subcamp.start) as date,
    				(
    					SELECT
    						IFNULL( SUM( s.length ), 0 )
    					FROM
    						subcamp s
    					WHERE
    						s.camp_id = subcamp.camp_id AND
    						s.start < subcamp.start
    				) + day.day_offset + 1 as day_offset 
    			FROM
    				day,
    				subcamp
    			WHERE
    				day.subcamp_id = subcamp.id AND
    				subcamp.camp_id = " . $_camp->id . "
    			ORDER BY day_offset
    			";
    $result = mysql_query($query);
    $allOk = true;

    while( $day = mysql_fetch_assoc($result) ) {
    	$query = "	SELECT
    					event.id,
    					event.name,
    					event.category_id,
    					instance.starttime,
    					instance.length
    				FROM
    					event INNER JOIN event_instance as instance
					ON
						event.id = instance.event_id
					WHERE
						event.camp_id = " . $_camp->id . " AND
						instance.day_id = " . $day['id'] . " AND
						event.category_id IN ($category_es, $category_ls, $category_la)
					ORDER BY instance.starttime ASC
					";
		$result_event = mysql_query($query);
		$start_lunch = 0;
		$length_lunch = 0;
		$start_dinner = 0;
		$length_dinner = 0;
		$day_events = array();

		while( $event = mysql_fetch_assoc($result_event) ) {
			if( $event['category_id'] == $category_es ) {
				$start_lunch = $start_dinner;
				$length_lunch = $length_dinner;
				$start_dinner = $event['starttime'];
				$length_dinner = $event['length'];
			} else {
				if( 
					// LS und LS min. 30min laut J+S
					($event['category_id'] == $category_ls && $event['length'] >= 30) ||
					($event['category_id'] == $category_la && $event['length'] >= 30) ) {
					$event['link'] = '$event.edit(' . $event['id'] . ')';
					$day_events[] = $event;
				}
			}
		}

		$total_ls = 0;
		$total_la = 0;
		$num_events_morning = 0;
		$num_events_afternoon = 0;
		$num_events_evening = 0;

		foreach ($day_events as $event) {
			$start_event = $event['starttime'];
			$length_event = $event['length'];

			$total_ls += ($event['category_id'] == $category_ls) ? $event['length'] : 0;
			$total_la += ($event['category_id'] == $category_la) ? $event['length'] : 0;

			$num_events_morning += ($start_event < $start_lunch) ? 1 : 0;
			$num_events_afternoon += (	($start_event > $start_lunch && $start_event < $start_dinner) ||
										($start_event < $start_lunch && ($start_event+$length_event > $start_lunch+$length_lunch))
									) ? 1 : 0;
			$num_events_evening += (	($start_event > $start_dinner) ||
										($start_event < $start_dinner && ($start_event+$length_event > $start_dinner+$length_dinner))
									) ? 1 : 0;
		}

    	$validJsProgram = $total_ls + (($total_la >= 120) ? 120 : $total_la);
    	$minFourValidHours = ($validJsProgram >= 240);
    	$minTwoTimesOfDay = (	($num_events_morning && $num_events_afternoon) ||
    							($num_events_morning && $num_events_evening) ||
    							($num_events_afternoon && $num_events_evening));
    	$dayOk = ($minFourValidHours && $minTwoTimesOfDay);
        $allOk = (!$dayOk) ? false : $allOk;

		$date = new c_date();
		$date->setDay2000( $day['date'] );

    	$days[] = array(
    		"id" 		=> $day['id'],
    		"offset" 	=> $day['day_offset'],
    		"date" 		=> $date->getString( 'd.m.Y' ),
    		"link"		=> "index.php?app=day&cmd=home&day_id=".$day['id'],
    		"fourHours" => $minFourValidHours,
    		"twoTimes"	=> $minTwoTimesOfDay,
    		"dayOk"		=> $dayOk,
    		"events" 	=> $day_events
    	);
    }
    $jsAnalytics = array(
        "allOk"     => $allOk,
        "days"      => $days
    );

    $_page->html->set('jsAnalytics', $jsAnalytics);

?>
