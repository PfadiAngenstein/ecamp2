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

	$ans = null;

	$query = "	SELECT type
				FROM event
				WHERE id = $event_id";

	$result = mysql_query( $query );

	if( !mysql_error() && mysql_num_rows( $result ) )
	{
		$result = mysql_fetch_assoc( $result );
		if( $result['type'] != null )
		{
			$ans = array(
				"id" => $result['type'],
				"child" => null
			);
			
			$child = $result['type'];
			while( get_parent($child) >= 0 ) {
				$parent = get_parent($child);
				$ans = array(
					"id" =>$parent,
					"child" => $ans
				);
				$child = $parent;
			}
		}
	}

	$_js_env->add( 'type_list', $ans );




	function get_parent( $id )
	{
		$query = "	SELECT child_of
					FROM event_types
					WHERE id = $id";

		$result = mysql_query( $query );
		$type = mysql_fetch_assoc( $result );

		if( is_null( $type['child_of'] ) )
		{
			return -1;
		} else {
			return $type['child_of'];
		}
	}
?>