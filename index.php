<?php
/*
 * Plugin Name: WPDEFT POSTS AND FILTER
 * Version: 2.3
 * Plugin URI: https://wpdeft.com/
 * Description: Display posts with load more and category filter
 * Author: MOHAMMAD
 * Author URI: https://wpdeft.com/
 */

if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php"); 
}

add_action( 'wp_enqueue_scripts', 'wpdeft_script_and_styles');
 
function wpdeft_script_and_styles() {
    
 
    // when you use wp_localize_script(), do not enqueue the target script immediately
    wp_register_script( 'wpdeft_scripts', plugin_dir_url( __FILE__ ) . 'js/wpdeft_script.js', array('jquery') );
    wp_register_style('wpdeft_styles', plugin_dir_url( __FILE__ ) . 'css/style.css', array('lfb-estimationpopup-css'));
    wp_enqueue_style('wpdeft_styles');
 
    // passing parameters here
    // actually the <script> tag will be created and the object "wpdeft_loadmore_params" will be inside it 
    $published_posts = wp_count_posts()->publish;
    $posts_per_page = 10;
    $page_number_max = ceil($published_posts / $posts_per_page);

    wp_localize_script( 'wpdeft_scripts', 'wpdeft_loadmore_params', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
        'current_page' => 1,
        'max_page' => ''
    ) );
 
    wp_enqueue_script( 'wpdeft_scripts' );
}

add_action('wp_ajax_loadmorebutton', 'wpdeft_loadmore_ajax_handler');
add_action('wp_ajax_nopriv_loadmorebutton', 'wpdeft_loadmore_ajax_handler');
 
function wpdeft_loadmore_ajax_handler(){
 
    
    $paged= $_POST['page'] + 1; // we need next page to be loaded
    $cat  = implode(',', $_POST['categories']);
 
    // it is always better to use WP_Query but not here
    $params = array( 
        'post_type' => $_POST['post_type'],
        'posts_per_page' => 10,
        'paged'  => $paged,
        'cat'    => $cat,
        'post_status' => 'publish',
        'orderby' => 'date', 
        'order' => 'ASC' 
    );
 
 
    $query = new WP_Query( $params );
 
    if( $query->have_posts() ) : 
          while( $query->have_posts() ): $query->the_post();  $cats = get_the_category();
            $content .= '<article class="NewsList-article" >'; //print_r($query->post);
            $content .= '<p class="NewsList-metaData"><a href="'.get_category_link($cats[0]->cat_ID).'" >'.$cats[0]->name.'</a> '.date('F j, Y',strtotime($query->post->post_date)).'</p>';
            $content .= '<h2 class="NewsList-heading" style="margin-top:0px !important;" ><a href="'.get_permalink($query->post->ID).'" >' . $query->post->post_title . '</a></h2>';
            $content .= '<p style="margin-bottom:20px !important;">'.$query->post->post_excerpt.'</p>';
            $content .= '<a class="Button Button--wide Button--bordered" href="'.get_permalink($query->post->ID).'">Read news</a>';
            $content .= '</article>';
        endwhile;
        wp_reset_postdata();
    else :
        $content .= 'No posts found';
    endif;
 if (  $query->max_num_pages > $paged ) :
    $content .='<div id="wpdeft_loadmore">More posts</div>'; 
endif;

   echo $content; die();
}
 
 
 
add_action('wp_ajax_wpdeftfilter', 'wpdeft_filter_function'); 
add_action('wp_ajax_nopriv_wpdeftfilter', 'wpdeft_filter_function');
 
function wpdeft_filter_function(){
   $cat  = implode(',', $_POST['categories']);
   
    $params = array( 
        'post_type' => $_POST['post_type'],
        'posts_per_page' => 10, 
        'cat' => $cat,
        'post_status' => 'publish',
        'orderby' => 'date', 
        'order' => 'ASC' 
    );
    

 
    $query = new WP_Query( $params );
 
    if( $query->have_posts() ) :
         while( $query->have_posts() ): $query->the_post();  $cats = get_the_category();
            $content .= '<article class="NewsList-article" >'; //print_r($query->post);
            $content .= '<p class="NewsList-metaData"><a href="'.get_category_link($cats[0]->cat_ID).'" >'.$cats[0]->name.'</a> '.date('F j, Y',strtotime($query->post->post_date)).'</p>';
            $content .= '<h2 class="NewsList-heading" style="margin-top:0px !important;" ><a href="'.get_permalink($query->post->ID).'" >' . $query->post->post_title . '</a></h2>';
            $content .= '<p style="margin-bottom:20px !important;">'.$query->post->post_excerpt.'</p>';
            $content .= '<a class="Button Button--wide Button--bordered" href="'.get_permalink($query->post->ID).'">Read news</a>';
            $content .= '</article>';
        endwhile;
        wp_reset_postdata();
    else :
        $content .= 'No posts found';
    endif;
 if (  $query->max_num_pages > 1 ) :
    $content .='<div id="wpdeft_loadmore">More posts</div>'; 
 endif;
   echo $content; die();
    
}

add_shortcode( 'wpdeft_posts', 'wpdeft_post_display' );
function wpdeft_post_display($atts){
    $category = '';
   $a = shortcode_atts( array(
      'post_type' => 'post'
   ), $atts );
    
    if($a['post_type']=='portfolio'){
        $category = 'portfolio_category';
    }
    if($a['post_type']=='page'){
        $category = 'page_category';
    }
    
    if($a['post_type']=='post'){
        $category = 'category';
    }
    if( $terms = get_terms( array('taxonomy' => $category ) ) ) {
    $content = ''; 
    $content .='<form id="wpdeft_filters" >';
    $content .='Filter By : ';
    foreach ( $terms as $term ) :
       $content .='<label><input type="checkbox" name="categories[]" value="' . $term->term_id . '" style="-webkit-appearance:checkbox;" > ' . $term->name . ' &nbsp;&nbsp;</label>';
    endforeach;
   $content .='</form>';
  }  
 
    $params = array( 
        'post_type' => $a['post_type'],
        'posts_per_page' => 10, 
        'orderby' => 'date', 
        'order' => 'ASC'
    );
 
 
    $query = new WP_Query( $params );
    $content .= '<div id="wpLoader" style="background: url(http://staging.wpdeft.com/wp-content/plugins/wpdeft-posts/js/35.gif) no-repeat top;position: absolute;z-index: 267;
width: 100%;height: 100%;display: block;vertical-align: top;text-align: center;display:none;" ></div>';
   $content .= '<div id="wpdeft_posts_wrap" data-post-type="'.$a['post_type'].'">';
    if( $query->have_posts() ) :
        while( $query->have_posts() ): $query->the_post();  $cats = get_the_category();
            $content .= '<article class="NewsList-article" >'; //print_r($query->post);
            $content .= '<p class="NewsList-metaData"><a href="'.get_category_link($cats[0]->cat_ID).'" >'.$cats[0]->name.'</a> '.date('F j, Y',strtotime($query->post->post_date)).'</p>';
            $content .= '<h2 class="NewsList-heading" style="margin-top:0px !important;" ><a href="'.get_permalink($query->post->ID).'" >' . $query->post->post_title . '</a></h2>';
            $content .= '<p style="margin-bottom:20px !important;">'.$query->post->post_excerpt.'</p>';
            $content .= '<a class="Button Button--wide Button--bordered" href="'.get_permalink($query->post->ID).'">Read news</a>';
            $content .= '</article>';
        endwhile;
        wp_reset_postdata();
    else :
        $content.='No posts found';
    endif;
 if (  $query->max_num_pages > 1 ) :
    $content .='<div id="wpdeft_loadmore">More posts</div>'; 
endif;
   $content .='</div>';
   return $content;


}

?>
