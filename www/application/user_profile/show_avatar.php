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

	header( "Content-Type: image/jpeg" );
	
	$show_user_id = mysql_real_escape_string( $_REQUEST['show_user_id'] );
	
	$query = "	SELECT
					image
				FROM
					user
				WHERE
					id = $show_user_id";
	$result = mysql_query($query);
	$imgData = mysql_result( $result, 0, 'image' );
	
	if($imgData == "")
	{	die( file_get_contents( "public/global/img/no-avatar.gif" ) );	}
	else
	{	echo $imgData;	}
	
	die();
?>