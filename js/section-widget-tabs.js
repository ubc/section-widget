!function($, w){ 
	
	w.OLTSWTInit = function( element ) {
		Section_Widget_Tabs.create_tabs( element );
	}
	
	var swtSortableOptions = {
	    axis:'x',
	    items: 'li:not(.olt-swt-designer-tabs-controls)',
	    update: function(){
	        var $ = jQuery;
	        
	        var field = $(this).parents('.olt-swt-designer-wrapper:first').siblings('.olt-swt-order:first');
	        field.val($(this).sortable('serialize',{'key':'order'}));
	    }
	}; 
	
	var Section_Widget_Tabs = {
		tabs: false,
		init:function(){
			
			
			$('#widgets-right')
			// add tabs
			.delegate('.olt-swt-designer-add-tab a','click','', Section_Widget_Tabs.add_tab )
			// delete tabs
			.delegate('.olt-swt-designer-delete-tab a','click','', Section_Widget_Tabs.delete_tab )
			// update tab title
			.delegate('.olt-swt-designer-tab-title','focusout','', Section_Widget_Tabs.update_tab_title );
			
			Section_Widget_Tabs.create_tabs();
			
		},
		
		create_tabs: function( element ){
			
			if( Section_Widget_Tabs.tab ){
				Section_Widget_Tabs.tabs = Section_Widget_Tabs.tab;
				
			} else {
				Section_Widget_Tabs.tabs = $( ".olt-swt-designer-wrapper" );
			}
		
			Section_Widget_Tabs.tabs.tabs().find('.ui-tabs-nav').sortable(swtSortableOptions);
			
			Section_Widget_Tabs.tabs.each(function(i,e){
				var tab = $(e);
				
				if(typeof(tab.data('inc')) == 'undefined') {
	            	var inc = tab.size();
	            	var counter = tab.find(".olt-swt-designer-tab").length;

	            	tab.data('inc',counter);
	        	}
	    	});
	    
		},
		add_tab: function(e){
			
			var tabs = $(this).parents('.olt-swt-designer-wrapper');
			
			Section_Widget_Tabs.create_tabs(tabs);
			
			var list = tabs.find('ul');
			var tabid = tabs.data('inc');
			
			tabid++;
			var idprefix = tabs.siblings('input[name=idprefix]:first').val();
	        var nameprefix = tabs.siblings('input[name=nameprefix]:first').val();
			
			var id = idprefix+'-id-'+tabid;
			
			var text = 'You may use HTML in this widget, and it is probably a good idea to wrap the content in your own <code>&lt;div&gt;</code> to aid styling. Shortcodes are also allowed, but please beware not all of them will function properly on archive pages.';
			
			$( '<li class="olt-swt-designer-tab" id="'+id+'-list"><a href="#'+id+'" id="'+id+'-title-link">New Tab</a></li>' ).appendTo( list );
			
			var panel =  '<div id="'+id+'" class="olt-swt-designer-panel">'
				panel += '<div class="olt-swt-designer-top">'
				panel += '<label for="">Title:</label> <input id="'+id+'-title" class="olt-swt-designer-tab-title" name="'+nameprefix+'['+tabid+'][title]" type="text" value="New Tab" />'
				panel += '<p class="olt-swt-designer-tabs-controls olt-swt-designer-delete-tab" ><a href="#"  id="'+id+'-delete"><span class="ui-icon ui-icon-trash"  style="float:left;margin-right:.3em;margin-top: -2px;"></span>Delete this tab</a></p>'
				panel += '</div>'
				panel += '<div class="olt-sw-body">'
				panel += '<p class="olt-sw-body-help"><strong>Formatting Help:</strong> '+text+'</p>'
				panel += '<textarea  rows="16" cols="20" name="'+nameprefix+'['+tabid+'][body]"></textarea>'
				panel += '</div>'
				
				$( panel ).appendTo( tabs );
			
			
			tabs.tabs( "refresh" );
			var counter = list.find("li").length;
			
			tabs.tabs( "option", "active", counter-2 );
			tabs.data('inc', tabid);
			
	    	e.preventDefault();
	    	
		},
		
		delete_tab: function( e ) {
			var confirm = w.confirm("Are you sure you want to delete this tab?");
    		
    		if(confirm) {
				var id = $( this ).attr('id').slice(0, -7); // remove delete at the end to get the id…
	  			var shell = $("#"+id);
	    		var list_shell = $("#"+id+"-list");
	    		var tabs = shell.parents('.olt-swt-designer-wrapper');
	    		shell.remove();
	    		list_shell.remove();
	    		tabs.tabs("refresh");
	    	}
			e.preventDefault();
		},
		
		update_tab_title: function ( e ){
			var el = $( this );
	        
	       	$('#'+el.attr('id')+'-link').text( el.val() );
	       	e.preventDefault();
		}
	}
	
	$(function(){ Section_Widget_Tabs.init() } );

}( jQuery, window );