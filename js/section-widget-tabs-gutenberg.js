(function(){
    document.addEventListener("DOMContentLoaded", function() {
        // Select the node that will be observed for mutations
        const targetNode = document.getElementById('widgets-editor');
    
        // Options for the observer (which mutations to observe)
        const config = { childList: true, subtree: true };
    
        // Callback function to execute when mutations are observed
        const callback = function(mutationsList, observer) {
            // Use traditional 'for loops' for IE 11
            for(const mutation of mutationsList) {
                // For section widget that is newly added to current widget screen.
                if( mutation.target.classList.contains('widget-content')){
                    // Initiate tabs for the location roles tabs
                    jQuery(mutation.target).find('.olt-checklist-wrapper').tabs();

                    // Initiate tabs for Section Widget(tabbed) inner tab
                    jQuery(mutation.target).find('.olt-swt-designer-main').tabs();
                }

                // For section widget that is already added previously.
                if (mutation.type === 'childList' && ( mutation.target.classList.contains('wp-block-legacy-widget') || mutation.target.classList.contains('wp-block-legacy-widget__edit-form') ) ) {
                    // Initiate tabs for the location roles tabs
                    jQuery(mutation.target).find('.olt-checklist-wrapper').tabs();

                    // Initiate tabs for Section Widget(tabbed) inner tab
                    jQuery(mutation.target).find('.olt-swt-designer-main').tabs();
                }
            }
        };
    
        // Create an observer instance linked to the callback function
        const observer = new MutationObserver(callback);
        // Start observing the target node for configured mutations
        observer.observe(targetNode, config);
    });
}())