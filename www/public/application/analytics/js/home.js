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

window.addEvent( 'load', function()
{
	// Blocktypes
	var ctx = $('chart_blocktypes').getContext('2d');
	var chart_blocktypes = new Chart(ctx, {
		type: 'pie',
		data: {
			labels: [],
			datasets: [{
				backgroundColor: [],
				borderWidth: 0,
				borderAlign: 'inner',
				hoverBorderColor: '#E6EFFF',
				hoverBorderWidth: 2,
				hoverBackgroundColor: [],
				data: []
			}]
		},
		options: {
			legend: {
				display: true,
				position: 'bottom'
			}
		}
	});

	if( $_var_from_php.events_blocktype_tree.length > 0 ) {
		updatePie(
			chart_blocktypes,
			$_var_from_php.events_blocktype_tree,
			null,
			false,
			$('chart_blocktypes_breadcrumbs'),
			$('chart_blocktypes_eventlist'),
			$('chart_blocktypes_eventlist_type')
		);
	} else {
		$('chart_blocktypes_empty').removeClass('hidden');
	}




	// Empty Types
	var ctx = $('chart_emptytype').getContext('2d');
	var chart_emptytype = new Chart(ctx, {
		type: 'pie',
		data: {
			labels: [],
			datasets: [{
				backgroundColor: [],
				borderWidth: 0,
				borderAlign: 'inner',
				hoverBorderColor: '#E6EFFF',
				hoverBorderWidth: 2,
				hoverBackgroundColor: [],
				data: []
			}]
		},
		options: {
			legend: {
				display: true,
				position: 'bottom'
			}
		}
	});

	if( $_var_from_php.events_emptytype.length > 0 ) {
		updatePie(
			chart_emptytype,
			$_var_from_php.events_emptytype,
			null,
			false,
			$('chart_emptytypes_breadcrumbs'),
			$('chart_emptytypes_eventlist'),
			$('chart_emptytypes_eventlist_type')
		);
	}




	function updatePie( chart, array, parent_array, back_to_parent, breadcrumbs, eventlist, eventlist_type ) {
		var colorcodes = [
			'#E88650',
			'#FCBD52',
			'#E6E196',
			'#4BCAB7',
			'#294353',
			'#7F618E',
			'#ABB546'
		];

		var labels = [];
		var values = [];
		var colors = [];

		$each( array, function( item, key ) {
			labels.push( item.name );
			values.push( item.count );
			var color = colorcodes.getRandom();
			colors.push( color );
			colorcodes.erase( color );
		});

		chart.data.labels = labels;
		chart.data.datasets[0].data = values;
		chart.data.datasets[0].backgroundColor = colors;
		chart.data.datasets[0].hoverBackgroundColor = colors;
		chart.options.onClick = function( e, args ) {
			if( args.length > 0 ) {
				var id = args[0]._index;
				if( array[id].children.length > 0 ) {
					updatePie( chart, array[id].children, array, false, breadcrumbs, eventlist, eventlist_type );
				} else {
					showEvents( array[id].children, array, eventlist, eventlist_type );
				}
			}
		};
		chart.update();

		// Show events
		showEvents( array, parent_array, eventlist, eventlist_type );

		// Breadcrumbs / Buttons
		if( ! back_to_parent ) {
			var text = 'parent';
			if( parent_array == null ) {
				text = 'Home';
			} else {
				$each( parent_array, function( item ) {
					if( item.children == array ) {
						text = item.name;
					}
				});
			}

			var btn = new Element('button');
			btn.set( 'text', text );
			btn.addEvent( 'click', function() {
				var finished = false;
				while( !finished ) {
					if( this === breadcrumbs.getLast('button') ) {
						finished = true;
					} else {
						breadcrumbs.getLast('button').dispose();
					}
				}
				updatePie( chart, array, parent_array, true, breadcrumbs, eventlist, eventlist_type );
			});
			btn.inject( breadcrumbs );
		}
	}




	function showEvents( array, parent, eventlist, eventlist_type ) {
		eventlist.empty();
		eventlist_type.set( 'text', '-' );
		$each( parent, function( type ) {
			if( type.children == array ) {
				eventlist_type.set( 'text', type.name );
				$each( type.events, function( event ) {
					console.log( event );
					var li = new Element('li');
					var a = new Element('a');
					a.set( 'html', '<b style="background-color:#' + event.color + '">' + event.short_name + '</b> ' + event.name );
					a.set( 'href', '#' );
					a.set( 'onClick', '$event.edit(' + event.id + ')' );
					a.inject( li, 'bottom' );
					li.inject( eventlist, 'bottom' );
				});
			}
		});
	}




	function getRandomColor( colors ) {
		var key = Math.floor(Math.random() * Math.floor(colors.length));
		return colors[key];
	}
	
});