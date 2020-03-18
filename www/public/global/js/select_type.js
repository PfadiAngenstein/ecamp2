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

var global_counter = 0;

select_class = new Class(
{
	left_select:	null,
	right_select:	null,
	select_element:	null,
	wait:			null,
	startlist: 		null,
	
	value:			null,
	counter:	      0,
	
	initialize: function( pid, left_select, cnt, startlist )
	{
	    this.counter = cnt + 1;
	    global_counter = this.counter;
		this.startlist = startlist;
	    
		this.left_select = left_select;

		this.select_element = new Element('select');
		this.select_element.set('size', 8 );
		this.select_element.addClass('hidden');
		this.select_element.inject( $('event_type_selects'), 'bottom' );
		
		//if( left_select == null ){	this.select_element.focus();	}
		
		//this.select_element.addEvent('click', this.change.bind(this) );
		this.select_element.addEvent('change', this.change.bind(this) );
		this.select_element.addEvent('keydown', this.keyhandler.bind(this) );
		
		this.wait = new Element('option').set( 'text', 'laden...' );
		this.wait.inject( this.select_element );
		
		
		args = new Hash(
		{
			"app": "event",
			"cmd": "load_select_type",
			"pid": pid
		});

		new Request.JSON(
		{
			method: 'get',
			url: "index.php",
			data: args.toQueryString(),
			onComplete: this.build_options.bind(this)
		}).send();
	},
	
	keyhandler: function( event )
	{
		if( event.key == "right" )
		{
			this.right_select.select_element.focus();
			
			if( this.right_select.select_element.getSelected() == "" )
			{	this.right_select.select_first();	}
		}
		if( event.key == "left" )
		{
			this.left_select.select_element.focus();
			this.left_select.change();
		}
	},
	
	select_first: function()
	{
		this.select_element.getFirst('option').set('selected', 'selected');
		this.update();
	},
	
	build_options: function( ans )
	{
		this.select_element.empty();
		
		if( ans.num_values == 0 )
		{	this.select_element.destroy();	}
		else
		{
			ans.values.each( function( o ){
				option = new Element('option');
				option.set( 'value', o.id );
				option.set( 'text', o.name );
				option.store( 'o', o );
				
				option.inject( this.select_element );
			}.bind(this));
			
			this.select_element.removeClass('hidden');
			if( this.left_select == null ){	this.select_element.focus();	}

			if( this.startlist != null )
			{
				this.select_element.value = this.startlist.id;
				this.select_element.focus();
				if( this.startlist.child != null )
				{
					this.startlist = this.startlist.child;
				} else {
					this.startlist = null;
				}
				this.update();
			}
		}
	},
	
	change: function()
	{
		$('event_type_load').removeClass('hidden');

		args = new Hash(
		{
			"app": "event",
			"cmd": "action_change_type",
			"event": $event.id,
			"type": this.select_element.get('value')
		});

		new Request.JSON(
		{
			method: 'get',
			url: "index.php",
			data: args.toQueryString(),
			onComplete: this.update.bind(this)
		}).send();
	},

	update: function()
	{
		if( this.value != this.select_element.get('value') )
		{
			if( this.right_select )
			{	this.right_select.remove(); }
			
			pid = this.select_element.getSelected().getLast().get('value');
			this.right_select = new select_class( pid, this, this.counter, this.startlist );
			
			this.value = this.select_element.get('value');
		}
		else
		{
			if( this.right_select )
			{
				if( this.right_select.right_select )
				{	this.right_select.right_select.remove();	}
				
				this.right_select.select_element.getSelected().removeProperty( 'selected' );
				this.right_select.value = 0;
			}
			
			
			pid = this.select_element.getSelected().getLast().get('value');
		}
					
		global_counter = this.counter+1;

		$('event_type_load').addClass('hidden');
	},
	
	remove: function()
	{
		if( this.right_select )
		{	this.right_select.remove(); }
		
		this.select_element.destroy();
	}
	
});
