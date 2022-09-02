jQuery(function($){

    jQuery('input[name="categories[]"]').attr('checked',false)
 
    /*
     * Load More
     */
    $('#wpdeft_loadmore').live('click',function(){
 
        $.ajax({
            url : wpdeft_loadmore_params.ajaxurl, // AJAX handler
            data : {
                'action': 'loadmorebutton', // the parameter for admin-ajax.php
                //'query': wpdeft_loadmore_params.posts, // loop parameters passed by wp_localize_script()
                'page' : wpdeft_loadmore_params.current_page, // current page
                'post_type' : jQuery('#wpdeft_posts_wrap').data('post-type')
            },
            type : 'POST',
            beforeSend : function ( xhr ) {
                $('#wpdeft_loadmore').text('Loading...'); // some type of preloader
            },
            success : function( posts ){
                if( posts ) {
 
                    //$('#wpdeft_loadmore').text( 'More posts' );
                    $('#wpdeft_loadmore').remove();
                    $('#wpdeft_posts_wrap').append( posts ); // insert new posts
                    wpdeft_loadmore_params.current_page++;
 
                    if ( wpdeft_loadmore_params.current_page == wpdeft_loadmore_params.max_page ) 
                        $('#wpdeft_loadmore').hide(); // if last page, HIDE the button
 
                } else {
                    $('#wpdeft_loadmore').hide(); // if no data, HIDE the button as well
                }
            }
        });
        return false; 
    });
 
    /*
     * Filter
     */
    $('#wpdeft_filters input[type="checkbox"]').on('click',function(){
     $('#wpLoader').fadeIn();
     $('#wpdeft_posts_wrap').fadeOut();
     $('#wpdeft_filters').submit();
    });

    $('#wpdeft_filters').submit(function(){
       
        
        $.ajax({
            url : wpdeft_loadmore_params.ajaxurl,
            data : $('#wpdeft_filters').serialize()+'&action=wpdeftfilter&post_type='+jQuery('#wpdeft_posts_wrap').data('post-type'),
            type : 'POST',
            beforeSend : function(xhr){
                $('#wpdeft_filters').find('button').text('Filtering...');
            },
            success : function( data ){ 
                $('#wpLoader').fadeOut(); 
 
                
                wpdeft_loadmore_params.current_page = 1;
 
                
                // set the new max page parameter
                //wpdeft_loadmore_params.max_page = data.max_page;
 
                // change the button label back
                
                  $('#wpdeft_loadmore').remove();
                // insert the posts to the container
                $('#wpdeft_posts_wrap').html(data); 
                $('#wpdeft_posts_wrap').fadeIn();
 
                
        
            }
        });
 
        // do not submit the form
        return false;
 
    });
 
});