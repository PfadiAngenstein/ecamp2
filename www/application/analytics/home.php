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

		if($start_lunch == 0) { $start_lunch = 720; $length_lunch = 60; }		// 12:00 - 13:00
		if($start_dinner == 0) { $start_dinner = 720; $length_dinner = 60; }	// 18:00 - 19:00

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




    $query = "	SELECT
    				e.id,
    				e.name,
    				e.type,
    				c.short_name,
    				c.color
    			FROM
    				event as e
    			INNER JOIN
    				category as c ON e.category_id=c.id
    			WHERE
    				e.camp_id=" . $_camp->id;

    $results = mysql_query( $query );

	$events_blocktype = array();
	$events_emptytype = array(
		array("name" => "Leer", "count" => 0, "children" => array()),
		array("name" => "Mit Typ", "count" => 0, "children" => array())
	);
    
    while( $event = mysql_fetch_assoc( $results ) ) {
    	// Array für Blocktypen
    	if( !is_null( $event['type'] ) ) {
	    	$events_blocktype[] = array(
	    		"event" => $event,
	    		"tree" => getParents( $event['type'], null )
	    	);
    	}


    	// Array für leerer Typ
    	$empty = ( is_null( $event['type'] ) ) ? 0 : 1;
    	$key = array_search( $event['short_name'], array_column( $events_emptytype[$empty]["children"], 'name' ) );
    	
    	if( $key === false ) {
    		$events_emptytype[$empty]["children"][] = array(
				"name" => $event['short_name'],
				"count" => 0,
				"children" => array(),
				"events" => array()
			);
    	}
    	
    	$key = array_search( $event['short_name'], array_column( $events_emptytype[$empty]["children"], 'name' ) );

    	$events_emptytype[$empty]["children"][$key]["events"][] = $event;
    	$events_emptytype[$empty]["count"] += 1;
    	$events_emptytype[$empty]["children"][$key]["count"] += 1;
    }




    // Auswertung Blocktypen
    $events_blocktype_tree = array();

    foreach ($events_blocktype as $event) {
    	if( !is_null( $event['event']['type'] ) ) {
    		$events_blocktype_tree = addTreeToArray( $event['tree'], $event['event'], $events_blocktype_tree );
    	}
    }

	$_js_env->add( 'events_blocktype_tree', $events_blocktype_tree );




	// Auswertung leerer Typ
	$_js_env->add( 'events_emptytype', $events_emptytype );




	function getParents( $type, $children ) {
		$arr = array(
			"id" => $type,
			"children" => $children
		);
		
		$query = "SELECT * FROM event_types WHERE id=$type";
		$type = mysql_fetch_assoc( mysql_query( $query ) );

		if( is_null( $type['child_of'] ) ) {
			return $arr;
		} else {
			return getParents( $type['child_of'], $arr );
		}
	}




	function addTreeToArray( $tree, $event, $array ) {
		$key = array_search( $tree['id'], array_column( $array, 'id' ) );
		if( $key === false ) {
			$query = "SELECT name FROM event_types WHERE id=" . $tree['id'];
			$result = mysql_fetch_assoc( mysql_query( $query ) );

			$array[] = array(
				"id" => $tree['id'],
				"name" => $result['name'],
				"count" => 0,
				"children" => array(),
				"events" => array()
			);
			$key = array_search( $tree['id'], array_column( $array, 'id' ) );
		}

		$array[$key]['count'] += 1;

		if( is_null( $tree['children'] ) ) {
			$array[$key]['events'][] = $event;
			return $array;
		} else {
			$array[$key]['children'] = addTreeToArray( $tree['children'], $event, $array[$key]['children'] );
			return $array;
		}
	}


?>
