jQuery(document).ready(function($){
         
    // Option page
    $('head').append('<link id="swt-preview-style" rel="stylesheet" href="" type="text/css" media="all" />');

    var stylesheet = $('#swt-preview-style');
    
    $('#swt-theme-preview-wrapper').tabs();
    
    $('#swt-theme').change(function(){
        if($(this).val() == 'none')
            stylesheet.attr('href', '');
        else
            stylesheet.attr('href', stylesheet_url + '?theme=' + $(this).val() + '&scope=%23swt-theme-preview');
    }).change();
    
    $('#swt-theme-preview-link').click(function(){
        $('#swt-theme-preview').slideDown();
        return false;
    });
    
    $('#swt-theme-preview-hide-link').click(function(){
        $('#swt-theme-preview').slideUp();
        return false;
    });
    
    $('#swt-scope-help-link').click(function(){
        $('#swt-scope-help').slideDown();
        return false;
    });
    
    $('#swt-scope-help-hide-link').click(function(){
        $('#swt-scope-help').slideUp();
        return false;
    });
    
    $('#swt-scope-detect').click(function(){
        // Hide the button
        $(this).hide();
        
        // Start scope detect
        $('body').append('<iframe id="swt-scope-detect-iframe" style="display:none" />');
        
        // Grab iframe and message box
        var iframe = $('#swt-scope-detect-iframe');
        var output = $(this).siblings('#swt-scope-detect-message').empty().show();
        
        var scopes;
        var links_copy;
        
        var scopeTest = function(){            
            output.append('<div class="message">Initializing scope detect...</div>');
            
            if(typeof(links) == 'undefined' || links.length < 1) {
                output.append('<div class="error">Cannot find JavaScript variable "links"...</div>');
                getResult();
                return false;
            }
            
            // Reset vars
            scopes = [];
            links_copy = links.slice();
            iframe.unbind('load');
            
            // Kick off the test
            testNext();
        };
        
        var testNext = function(event){
            if(typeof(event) != 'undefined') {
                var items = $(this).contents().find('.swt-wrapper');
                var safe = false;
                
                if(items.length < 1) {
                    output.append('<div class="warning">No widget instances found, skipping...</div>');
                } else {
                    if(items.length < 2) {
                        output.append('<div class="warning">Only one widget instance found, this might lead to inaccurate result...</div>');
                        safe = true;
                    }
                    
                    items.each(function() {
                        var parents = $(this).parents();
                        var chain = [];
                        
                        parents.each(function() {
                            var id = $(this).attr('id');
                            if(id != '') chain.push(id);
                        });
                        
                        if(safe) chain.shift();
                        
                        scopes.push(chain);
                    });
                }
            }
                        
            if(links_copy.length < 1) {
                getResult();
            } else {
                link = links_copy.pop();
                output.append('<div class="message">Trying '+link+' ...</div>');
                iframe.attr('src',link).load(testNext);
            }
        };
        
        var getResult = function() {
            if(scopes.length < 1) {
                output.append('<div class="error">Scope detection has failed. Your settings are unchanged.</div>');
            } else {        
                var seed = scopes.shift();
                var result = [];
                
                for(var i=0;i<seed.length;i++){
                    var common = true;
                    var element = seed[i];
                    
                    for(var j=0;j<scopes.length;j++){
                        if($.inArray(element, scopes[j]) == -1){
                            common = false;
                            break;
                        }
                    }
                    
                    if(common){
                        result.push('#'+element);
                    }
                }
                
                result.reverse().push('.swt-outter');
                
                $('#swt-scope').val(result.join(' '));
                
                output.append('<div class="success">Scope detection has completed. Your optimal CSS scope is <strong>&quot;'+result.join(' ')+'&quot;</strong> and it has been filled in for you.<br /><strong>Don\'t forget to save your changes!</strong></div>');
            }
            
            $('#swt-scope-detect').show();
        };
        
        scopeTest(iframe, output);
        
        return false;
    });
});