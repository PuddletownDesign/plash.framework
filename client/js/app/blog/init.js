    $(function(){ 
		//Blog.infinitescroll();
		Blog.colorCodeSnippets();
    }); 
    
    Blog = {
    	colorCodeSnippets: function(){
    		//src: http://tomayko.com/writings/javascript-prettification
    		var domain = window.location.host;
    
    		var prettify = false;
			var $codeblock = $("pre code");
    		//add the pretty print class to all pre tags with code tag inside
    		$codeblock.parent().each(function() {
    	        $(this).addClass('prettyprint');
    	        prettify = true;
    	    });
    
    	    // if code blocks were found, bring in the prettifier ...
    	    if ( prettify ) {
				var prettifySrc = 'http://'+domain+'/js/lib/prettify/prettify.js';
				$.getScript(prettifySrc, function() { prettyPrint() });
                
    			prettifyCSS = "http://"+domain+"/css/prettify.css";
    			if (document.createStyleSheet){
    				document.createStyleSheet(prettifyCSS);
    			}
    			else{
    			    $('<link rel="stylesheet" type="text/css" href="' + prettifyCSS + '" />').appendTo('head'); 
    			}
    			
    	    }
    	},
		infinitescroll: function () {
			var $listContainer = $('.posts');
			
			if ($listContainer.length > 0) {
				var domain = window.location.host;
				var src = 'http://'+domain+'/js/lib/infinite-scroll/jquery.infinitescroll.js';
				$.getScript(src, function(){
					$listContainer.infinitescroll({

					  navSelector  : "div.paging",            
					                 // selector for the paged navigation (it will be hidden)

					  nextSelector : ".paging div.page-nav .next",    
					                 // selector for the NEXT link (to page 2)

					  itemSelector : ".posts li",          
					                 // selector for all items you'll retrieve

					  debug        : false,                        
					                 // enable debug messaging ( to console.log )

					  loadingImg   : false,
									//"/img/loading.gif",          
					                 // loading image.
					                 // default: "http://www.infinite-scroll.com/loading.gif"

					  loadingText  : false,
									 //"Loading new posts...",      
					                 // text accompanying loading image
					                 // default: "<em>Loading the next set of posts...</em>"

					  animate      : false,      
					                 // boolean, if the page will do an animated scroll when new content loads
					                 // default: false

					  extraScrollPx: 0,      
					                 // number of additonal pixels that the page will scroll 
					                 // (in addition to the height of the loading div)
					                 // animate must be true for this to matter
					                 // default: 150

					  donetext     : "" ,
					                 // text displayed when all items have been retrieved
					                 // default: "<em>Congratulations, you've reached the end of the internet.</em>"

					  bufferPx     : 300,
					                 // increase this number if you want infscroll to fire quicker
					                 // (a high number means a user will not see the loading message)
					                 // new in 1.2
					                 // default: 40

					  errorCallback: function(){},
					                 // called when a requested page 404's or when there is no more content
					                 // new in 1.2                   

					  localMode    : true
					                 // enable an overflow:auto box to have the same functionality
					                 // demo: http://paulirish.com/demo/infscr
					                 // instead of watching the entire window scrolling the element this plugin
					                 //   was called on will be watched
					                 // new in 1.2
					                 // default: false


					    },function(arrayOfNewElems){

					     // optional callback when new content is successfully loaded in.

					     // keyword `this` will refer to the new DOM content that was just added.
					     // as of 1.5, `this` matches the element you called the plugin on (e.g. #content)
					     //                   all the new elements that were found are passed in as an array

					});
				});
			}
		}
    }