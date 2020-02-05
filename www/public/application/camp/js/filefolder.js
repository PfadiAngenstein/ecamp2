document.addEventListener('DOMContentLoaded', function (){
	// feature detection for drag&drop upload
	var isAdvancedUpload = function()
		{
			var div = document.createElement( 'div' );
			return ( ( 'draggable' in div ) || ( 'ondragstart' in div && 'ondrop' in div ) ) && 'FormData' in window && 'FileReader' in window;
		}();

	// applying the effect for every form
	var form 	 = document.querySelector( 'form#file_box' );
	var load	 = document.querySelector( 'form#file-show' );
	var boxfiles = document.querySelector( 'div#files' );
	var fileinfo = document.querySelector( 'div#file-info' );
	var input	 = form.querySelector( 'input#file' ),
	label		 = form.querySelector( 'label' ),
	errorMsg	 = form.querySelector( '.file-error span' ),
	restart		 = form.querySelectorAll( '.file-restart' ),
	droppedFiles = false,
	files 		 = false,
	triggerFormSubmit = function( submitform )
	{
		var event = document.createEvent( 'HTMLEvents' );
		event.initEvent( 'submit', true, false );
		submitform.dispatchEvent( event );
	};

	// show files
	window.addEventListener('load', function(){
		triggerFormSubmit( load );
	}, false );

	// letting the server side to know we are going to make an Ajax request
	var ajaxFlag = document.createElement( 'input' );
	ajaxFlag.setAttribute( 'type', 'hidden' );
	ajaxFlag.setAttribute( 'name', 'ajax' );
	ajaxFlag.setAttribute( 'value', 1 );
	form.appendChild( ajaxFlag );
	// automatically submit the form on file select

	input.addEventListener( 'change', function( e )
	{
		form.classList.remove( 'is-error', 'is-success' );
		triggerFormSubmit( form );
	});

	// drag&drop files if the feature is available
	if( isAdvancedUpload )
	{
		form.classList.add( 'has-advanced-upload' ); // letting the CSS part to know drag&drop is supported by the browser
		[ 'drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop' ].forEach( function( event )
		{
			form.addEventListener( event, function( e )
			{
				// preventing the unwanted behaviours
				e.preventDefault();
				e.stopPropagation();
			});
		});
		[ 'dragover', 'dragenter' ].forEach( function( event )
		{
			form.addEventListener( event, function()
			{
				form.classList.add( 'is-dragover' );
			});
		});
		[ 'dragleave', 'dragend', 'drop' ].forEach( function( event )
		{
			form.addEventListener( event, function()
			{
				form.classList.remove( 'is-dragover' );
			});
		});
		form.addEventListener( 'drop', function( e )
		{
			droppedFiles = e.dataTransfer.files; // the files that were dropped
			triggerFormSubmit( form );
							});
	}

	// if the form was submitted
	form.addEventListener( 'submit', function( e )
	{
		// preventing the duplicate submissions if the current one is in progress
		if( form.classList.contains( 'is-uploading' ) ) return false;
		form.classList.add( 'is-uploading' );
		form.classList.remove( 'is-error' );
		if( isAdvancedUpload ) // ajax file upload for modern browsers
		{
			e.preventDefault();
			// gathering the form data
			var ajaxData = new FormData( form );
			if( droppedFiles )
			{
				Array.prototype.forEach.call( droppedFiles, function( file )
				{
					ajaxData.append( input.getAttribute( 'name' ), file );
				});
			}
			// ajax request
			var ajax = new XMLHttpRequest();
			ajax.open( form.getAttribute( 'method' ), form.getAttribute( 'action' ), true );
			ajax.onload = function()
			{
				console.log( ajax.responseText );
				form.classList.remove( 'is-uploading' );
				if( ajax.status >= 200 && ajax.status < 400 )
				{
					var data = JSON.parse( ajax.responseText );
					form.classList.add( data.error == false ? 'is-success' : 'is-error' );
					if( data.error ) errorMsg.textContent = data.msg;
					triggerFormSubmit( load );
				}
				else alert( 'Error. Please, contact the webmaster!' );
			};
			ajax.onerror = function()
			{
				form.classList.remove( 'is-uploading' );
				alert( 'Error. Please, try again!' );
			};
			ajax.send( ajaxData );
		}
		else // fallback Ajax solution upload for older browsers
		{
			var iframeName	= 'uploadiframe' + new Date().getTime(),
				iframe		= document.createElement( 'iframe' );
				$iframe		= $( '<iframe name="' + iframeName + '" style="display: none;"></iframe>' );
			iframe.setAttribute( 'name', iframeName );
			iframe.style.display = 'none';
			document.body.appendChild( iframe );
			form.setAttribute( 'target', iframeName );
			iframe.addEventListener( 'load', function()
			{
				var data = JSON.parse( iframe.contentDocument.body.innerHTML );
				form.classList.remove( 'is-uploading' )
				form.classList.add( data.error == false ? 'is-success' : 'is-error' )
				form.removeAttribute( 'target' );
				if( data.error ) errorMsg.textContent = data.msg;
				iframe.parentNode.removeChild( iframe );
			});
		}
		
	});

	// show files if form-show is submitted
	load.addEventListener( 'submit', function( e ) {
		e.preventDefault();
		e.stopPropagation();
		
		if( form.classList.contains( 'is-loadingfiles' ) ) return false;
		form.classList.add( 'is-loadingfiles' );
		form.classList.remove( 'is-error' );
		
		// ajax request
		var ajax = new XMLHttpRequest();
		ajax.open( load.getAttribute( 'method' ), load.getAttribute( 'action' ), true );
		ajax.onload = function()
		{
			form.classList.remove( 'is-loadingfiles' );
			if( ajax.status >= 200 && ajax.status < 400 )
			{
				var data = JSON.parse( ajax.responseText );

				if( data.error ) {
					form.classList.add( 'is-error' );
					errorMsg.textContent = data.msg;
				}

				while(boxfiles.firstChild) {
					boxfiles.removeChild(boxfiles.firstChild);
				}
				files = JSON.parse( data.msg );
				if( files != false ) {
					files.forEach(function( el,i ) {
						var file = document.createElement( 'div' );
						file.classList.add( 'file-file', 'file-'+el.ext );
						file.setAttribute('data-id', i);
						file.onclick = function() { window.open('/www/files/'+el.camp+'/'+el.name, '_blank').focus(); };
						file.innerHTML = '<div class="icon"></div><div class="name">'+el.name+'</div><div class="task__actions"><i class="fa fa-info"></i><i class="fa fa-delete"></i></div>';
						boxfiles.appendChild(file);
					});
				} else {
					var msg = document.createElement( 'div' );
					msg.style.marginTop = '55px';
					msg.innerHTML = '<i>Keine Dateien gefunden<i>';
					boxfiles.appendChild(msg);
				}
			}
			else alert( 'Error. Please, contact the webmaster!' );
			
		};
		ajax.onerror = function()
		{
			form.classList.remove( 'is-loadingfiles' );
			alert( 'Error. Please, try again!' );
		};
		ajax.send();
		
	});

	// context-menu

	  //////////////////////////////////////////////////////////////////////////////
	  //////////////////////////////////////////////////////////////////////////////
	  //
	  // H E L P E R    F U N C T I O N S
	  //
	  //////////////////////////////////////////////////////////////////////////////
	  //////////////////////////////////////////////////////////////////////////////
	
	  /**
	   * Function to check if we clicked inside an element with a particular class
	   * name.
	   * 
	   * @param {Object} e The event
	   * @param {String} className The class name to check against
	   * @return {Boolean}
	   */
	  function clickInsideElement( e, className ) {
	    var el = e.srcElement || e.target;
	    
	    if ( el.classList.contains(className) ) {
	      return el;
	    } else {
	      while ( el = el.parentNode ) {
	        if ( el.classList && el.classList.contains(className) ) {
	          return el;
	        }
	      }
	    }
	
	    return false;
	  }
	
	  /**
	   * Get's exact position of event.
	   * 
	   * @param {Object} e The event passed in
	   * @return {Object} Returns the x and y position
	   */
	  function getPosition(e) {
	    var posx = 0;
	    var posy = 0;
	
	    if (!e) var e = window.event;
	    
	    if (e.pageX || e.pageY) {
	      posx = e.pageX;
	      posy = e.pageY;
	    } else if (e.clientX || e.clientY) {
	      posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
	      posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	    }
	
	    return {
	      x: posx,
	      y: posy
	    }
	  }
	
	  //////////////////////////////////////////////////////////////////////////////
	  //////////////////////////////////////////////////////////////////////////////
	  //
	  // C O R E    F U N C T I O N S
	  //
	  //////////////////////////////////////////////////////////////////////////////
	  //////////////////////////////////////////////////////////////////////////////
	  
	  /**
	   * Variables.
	   */
	  var contextMenuClassName = "context-menu";
	  var contextMenuItemClassName = "context-menu__item";
	  var contextMenuLinkClassName = "context-menu__link";
	  var contextMenuActive = "context-menu--active";
	
	  var taskItemClassName = "file-file";
	  var taskItemInContext;
	
	  var clickCoords;
	  var clickCoordsX;
	  var clickCoordsY;
	
	  var menu = document.querySelector("#context-menu");
	  var menuItems = menu.querySelectorAll(".context-menu__item");
	  var menuState = 0;
	  var menuWidth;
	  var menuHeight;
	  var menuPosition;
	  var menuPositionX;
	  var menuPositionY;
	
	  var windowWidth;
	  var windowHeight;
	
	  /**
	   * Initialise our application's code.
	   */
	  function init() {
	    contextListener();
	    clickListener();
	    keyupListener();
	    resizeListener();
	  }
	
	  /**
	   * Listens for contextmenu events.
	   */
	  function contextListener() {
	    document.addEventListener( "contextmenu", function(e) {
	      taskItemInContext = clickInsideElement( e, taskItemClassName );
	
	      if ( taskItemInContext ) {
	        e.preventDefault();
	        toggleMenuOn();
	        positionMenu(e);
	      } else {
	        taskItemInContext = null;
	        toggleMenuOff();
	      }
	    });
	  }
	
	  /**
	   * Listens for click events.
	   */
	  function clickListener() {
	    document.addEventListener( "click", function(e) {
	      var clickeElIsLink = clickInsideElement( e, contextMenuLinkClassName );
	
	      if ( clickeElIsLink ) {
	        e.preventDefault();
	        menuItemListener( clickeElIsLink );
	      } else {
	        var button = e.which || e.button;
	        if ( button === 1 ) {
	          toggleMenuOff();
	        }
	      }
	    });
	  }
	
	  /**
	   * Listens for keyup events.
	   */
	  function keyupListener() {
	    window.onkeyup = function(e) {
	      if ( e.keyCode === 27 ) {
	        toggleMenuOff();
	      }
	    }
	  }
	
	  /**
	   * Window resize event listener
	   */
	  function resizeListener() {
	    window.onresize = function(e) {
	      toggleMenuOff();
	    };
	  }
	
	  /**
	   * Turns the custom context menu on.
	   */
	  function toggleMenuOn() {
	    if ( menuState !== 1 ) {
	      menuState = 1;
	      menu.classList.add( contextMenuActive );
	    }
	  }
	
	  /**
	   * Turns the custom context menu off.
	   */
	  function toggleMenuOff() {
	    if ( menuState !== 0 ) {
	      menuState = 0;
	      menu.classList.remove( contextMenuActive );
	    }
	  }
	
	  /**
	   * Positions the menu properly.
	   * 
	   * @param {Object} e The event
	   */
	  function positionMenu(e) {
	    clickCoords = getPosition(e);
	    clickCoordsX = clickCoords.x;
	    clickCoordsY = clickCoords.y;

	    contentWidth = document.getElementsByClassName("content_box_fit")[0].offsetWidth;
	    contentHeight = document.getElementsByClassName("content_box_fit")[0].offsetHeight;

	    boxWidth = document.getElementById("file_box").offsetWidth;
	    boxHeight = document.getElementById("file_box").offsetHeight;

	    menu.style.left = clickCoordsX - ((contentWidth - boxWidth) / 2) - 170 + "px";
	    menu.style.top = clickCoordsY - 702 + "px";
	  }
	
	  /**
	   * Dummy action function that logs an action when a menu item link is clicked
	   * 
	   * @param {HTMLElement} link The link that was clicked
	   */
	  function menuItemListener( link ) {
		id = taskItemInContext.getAttribute("data-id");
		action = link.getAttribute("data-action");

		if ( action == "info" )
		{

			file = files[id];
			// filesize
			sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
			if (file.size == 0) size = 'n/a';
			var i = parseInt(Math.floor(Math.log(file.size) / Math.log(1024)));
			if (i == 0) size = file.size + ' ' + sizes[i];
			size = (file.size / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
			// last edited
			ts = new Date(file.edited*1000);
			var year = ts.getFullYear();
			var month = ('0'+(ts.getMonth()+1)).substr(-2);
			var day = ('0'+ts.getDate()).substr(-2);
			var hours = ('0'+ts.getHours()).substr(-2);
			var minutes = ('0'+ts.getMinutes()).substr(-2);
			var seconds = ('0'+ts.getSeconds()).substr(-2);
			var date = day+'.'+month+'.'+year+' '+hours+':'+minutes+':'+seconds;
			fileinfo.getElementById("name").innerHTML = file.name;
			fileinfo.getElementById("size").innerHTML = size;
			fileinfo.getElementById("edited").innerHTML = date;
			fileinfo.classList.add('show');
			document.getElementsByClassName('file-overlay')[0].addEventListener( "click", function(e) {
				fileinfo.classList.remove('show');
			});

		}
	    else if ( action == "delete" )
	    {
	    	if ( confirm('Bist du sicher, dass du die Datei "'+files[id].name+'" löschen möchtest? Diese Aktion kann nicht rückgängig gemacht werden.') ) {
	    		if( form.classList.contains( 'is-deleting' ) ) return false;
				form.classList.add( 'is-deleting' );
				form.classList.remove( 'is-error' );
	
				// ajax request
				var ajax = new XMLHttpRequest();
				ajax.open( 'GET', '?app=camp&cmd=action_del_file&file='+files[id].name, true );
				ajax.onload = function()
				{
					form.classList.remove( 'is-deleting' );
					if( ajax.status >= 200 && ajax.status < 400 )
					{
						var data = JSON.parse( ajax.responseText );
	
						if( data.error ) {
							form.classList.add( 'is-error' );
							errorMsg.textContent = data.msg;
						} else {
							form.classList.add( 'is-success' );
							triggerFormSubmit( load );
						}
					}
					else alert( 'Error. Please, contact the webmaster!' );
					
				};
				ajax.onerror = function()
				{
					form.classList.remove( 'is-deleting' );
					alert( 'Error. Please, try again!' );
				};
				ajax.send();
			}
	    }

	    toggleMenuOff();
	  }
	
	  /**
	   * Run the app.
	   */
	  init();

}, false);



