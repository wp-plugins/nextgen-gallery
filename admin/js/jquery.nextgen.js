/**
 * NextGEN Gallery Navigation - 
 *   http://alexrabe.boelinger.com/
 *
 * Copyright (c) 2007 Alex Rabe (http://alexrabe.boelinger.com)
 * Licensed under GPL (GPL-LICENSE.txt) licenses.
 *
 * Built on top of the jQuery library
 *   http://jquery.com
 *
 *  version 1.01 : Bugfix for title/alt name
 */

(function($) {
    /**
     * Creates a Gallery navigation
     *
     * @name NextGEN
     * @type jQuery
     * @param Hash o A set of key/value pairs to set as configuration properties.
     * @cat Plugins/NextGEN
     */
    $.fn.nggallery = function(o) {
        return this.each(function() {
            new $ngg(this, o);
        });
    };

    // Default configuration properties.
    var defaults = {
        imgarray: new Array(),
        name : 'gallery',
        galleryurl : '',
        thumbfolder : 'thumbs',
        maxelement: 0
    };

    /**
     * The NextGEN Gallery object.
     *
     * @constructor
     * @name $.nggallery
     * @param Object e The element to create the gallery
     * @param Hash o A set of key/value pairs to set as configuration properties.
     * @cat Plugins/NextGEN
     */
    $.nggallery = function(e,o) {
    	// get the parameter 
    	this.options    = $.extend({}, defaults, o || {});
    	this.container  = null;

        // set the gallery pointer
        this.gallery    = $(e); 

		// check if array is longer then page elements		
		var o           = this.options;		
		this.pagelimit = o.imgarray.length < o.maxelement ? o.imgarray.length : o.maxelement;
		
        // get to start point	
		this.container = this.gallery.append('<div class="ngg-galleryoverview"></div>').children();
		
		// create thumbnail path
		if (o.thumbfolder == 'tumbs')
			this.thumbpath = o.galleryurl + o.thumbfolder + '/' + 'tmb_';	
		else 
			this.thumbpath = o.galleryurl + o.thumbfolder + '/' + o.thumbfolder  + '_';
		
        for (var idx = 0; idx < this.pagelimit; idx++) {

			var imglink = $.A().attr({ href: o.galleryurl + o.imgarray[idx][0], title: o.imgarray[idx][2], rel: o.name });
			$(imglink).addClass("thickbox");
			var image = $.IMG().attr({ src: this.thumbpath + o.imgarray[idx][0], alt: o.imgarray[idx][1] , title: o.imgarray[idx][1] }); 
			$(imglink).append(image);
			$.tb_init(imglink); //apply thickbox
        				
	        this.pictures   = this.container.append(imglink).children();
	        //console.log(this.options.galleryurl);
		}
		// add the div container
		this.pictures.wrap('<div class="ngg-gallery-thumbnail-box"></div>');
		this.pictures.wrap('<div class="ngg-gallery-thumbnail"></div>');		
		// add the navigation
		this.navigation();
		
    };

    // Create shortcut for internal use
    var $ngg = $.nggallery;

    $ngg.fn = $ngg.prototype = {
        nggallery: '0.0.1'
    };
    
    $ngg.fn.extend = $ngg.extend = $.extend;
	
	// Internal functions
    $ngg.fn.extend({
    	
    	/**
         * Add the page navigation
         *
         * @name navigation
         * @type undefined
         * @cat Plugins/NextGEN
         */
        navigation: function(step, total) {

            var self  = this;
		    var step  = this.options.maxelement;      // how many pics per page ?
		    var total = this.options.imgarray.length; // how many elements ?
		    var start = 0; 
		    var end   = start + step - 1; 
            
			if (total > step) { 
		        var navigator = $.DIV({className: "ngg-navigation"}); 
		        var offset = 0, page = 1; 
		        while (offset < total) { 
		            var listelement = $.SPAN({className: "page-numbers"}, $.TEXT(page++)); 
		            $(navigator).append(listelement); 

		            var f = (function(offset) { 
		                return function() { 
		                    self.show(offset);
		                }; 
		            })(offset); 
		            
		            // bound click to SPAN class
		            $(listelement).css('cursor', 'pointer');
					$(listelement).click(f); 
		            offset += step; 
		        } 
		        this.gallery.append(navigator); 
		    } 
			
			this.gallery.append('<div style="clear:both;"></div>');
        },

         /**
         * show images
         *
         * @name next
         * @type undefined
         * @cat Plugins/NextGEN
         */
        show: function( offset ) {
        	
        	var imagecounter = $(this.gallery.children(".ngg-galleryoverview").children()).length;
        	
        	var imagelist = this.gallery.children(".ngg-galleryoverview").children();
        	
        	for (var i = 0; i < imagecounter; i++) {
        		// get the image div container 
				var imagecontainer = this.gallery.children(".ngg-galleryoverview").find(".ngg-gallery-thumbnail")[i];
				// delete the content
				if (imagecontainer != null) {
					$(imagecontainer).empty();
					
					// create a new image
					var idx = offset + i;
					if (idx < this.options.imgarray.length ) {
						var imglink = $.A().attr({ href: this.options.galleryurl + this.options.imgarray[idx][0], title: this.options.imgarray[idx][2], rel: this.options.name });
						$(imglink).addClass("thickbox");
						var image = $.IMG().attr({ src: this.thumbpath + this.options.imgarray[idx][0], alt: this.options.imgarray[idx][1] , title: this.options.imgarray[idx][1] }); 
						$(imglink).append(image);
						// add the new image to the div container
						$(imagecontainer).append(imglink);
						$.tb_init(imglink);//apply thickbox
						// show it				
						$(imagelist[i]).fadeIn("normal");
					}
				}
			};
         }
    	
    });
    
    /**
    * DOM element creator for jQuery and Prototype by Michael Geary
    * http://mg.to/topics/programming/javascript/jquery
    * Inspired by MochiKit.DOM by Bob Ippolito
	*
    * @method : $.DIV({ id: 'somethingNew'}).appendTo('#somethingOld').click(doSomething);  
    * @cat Plugins/NextGEN
    */

	$.defineTag = function( tag ) {
		$[tag.toUpperCase()] = function() {
			return $._createNode( tag, arguments );
		}
	};
	
	(function() {
		var tags = [
			'a', 'br', 'button', 'canvas', 'div', 'fieldset', 'form',
			'h1', 'h2', 'h3', 'hr', 'img', 'input', 'label', 'legend',
			'li', 'ol', 'optgroup', 'option', 'p', 'pre', 'select',
			'span', 'strong', 'table', 'tbody', 'td', 'textarea',
			'tfoot', 'th', 'thead', 'tr', 'tt', 'ul' ];
		for( var i = tags.length - 1;  i >= 0;  i-- ) {
			$.defineTag( tags[i] );
		}
	})();
	
	$.NBSP = '\u00a0';
	
	$._createNode = function( tag, args ) {
		var fix = { 'class':'className', 'Class':'className' };
		var e;
		try {
			var attrs = args[0] || {};
			e = document.createElement( tag );
			for( var attr in attrs ) {
				var a = fix[attr] || attr;
				e[a] = attrs[attr];
			}
			for( var i = 1;  i < args.length;  i++ ) {
				var arg = args[i];
				if( arg == null ) continue;
				if( arg.constructor != Array ) append( arg );
				else for( var j = 0;  j < arg.length;  j++ )
					append( arg[j] );
			}
		}
		catch( ex ) {
			alert( 'Cannot create <' + tag + '> element:\n' +
				args.toSource() + '\n' + args );
			e = null;
		}
		
		function append( arg ) {
			if( arg == null ) return;
			if (arg.get) arg = arg.get(0); 
			var c = arg.constructor;
			switch( typeof arg ) {
				case 'number': arg = '' + arg;  // fall through
				case 'string': arg = document.createTextNode( arg );
			}
			e.appendChild( arg );
		}
		
		return $(e); 
	};
	
	$.TEXT = function(s) {	
	    return document.createTextNode(s);	
	}; 

})(jQuery);
