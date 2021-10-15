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
                if (mutation.type === 'childList' && mutation.target.classList.contains('wp-block-legacy-widget') ) {
                    jQuery(mutation.target).find('.olt-checklist-wrapper').tabs();
                }
            }
        };
    
        // Create an observer instance linked to the callback function
        const observer = new MutationObserver(callback);
        // Start observing the target node for configured mutations
        observer.observe(targetNode, config);
    });
}())