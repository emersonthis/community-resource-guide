<?php

$resourcePostType = 'rg_resource';
$prefix = 'rg_';
$textdomain = 'resourceguide'; // This should match style.css

// Check and warn if piklist library plugin isn't active
add_action('init', 'rg_init_function');
function rg_init_function(){
  if(is_admin()){
   include_once(__DIR__ . '/piklist-checker.php');
 
   if (!piklist_checker::check(__FILE__, 'theme')){ //use 'theme' parameter when included in a theme
     return;
   }
  }
}

// enqueue styles
add_action( 'wp_enqueue_scripts', 'rg_enqueue_styles' );
function rg_enqueue_styles() {
    wp_enqueue_style( 'rg-style', get_stylesheet_uri(),
        array( 'twenty-twenty-one-style' ), 
        wp_get_theme()->get('Version') // this only works if you have Version in the style header
    );
}

// post types
add_filter('piklist_post_types', 'rg_post_types');

  function rg_post_types($post_types) {

  	global $resourcePostType;

    $post_types[$resourcePostType] = array(
      'labels' => piklist('post_type_labels', 'Resource')
      ,'title' => __('Enter the name of this resource')
      ,'menu_icon' => 'dashicons-sos'
      ,'page_icon' => 'dashicons-sos'
      ,'supports' => array(
        'title'
      )
      ,'public' => true
      ,'has_archive' => true
      ,'rewrite' => array(
        'slug' => 'resource'
      )
      ,'capability_type' => 'page'
    );
    return $post_types;
  }


// register taxonomies
add_filter('piklist_taxonomies', 'rg_resource_tax');
  function rg_resource_tax($taxonomies) {

  	  global $resourcePostType;

     $taxonomies[] = array(
        'post_type' => $resourcePostType
        ,'name' => 'Location'
        ,'show_admin_column' => true
        ,'configuration' => array(
          'hierarchical' => true
          ,'labels' => piklist('taxonomy_labels', 'Location')
          ,'hide_meta_box' => false
          ,'show_ui' => true
          ,'query_var' => true
          ,'rewrite' => array(
            'slug' => 'resource-location'
          )
        )
      );

     $taxonomies[] = array(
        'post_type' => $resourcePostType
        ,'name' => 'Category'
        ,'show_admin_column' => true
        ,'configuration' => array(
          'hierarchical' => true
          ,'labels' => piklist('taxonomy_labels', 'Category')
          ,'hide_meta_box' => false
          ,'show_ui' => true
          ,'query_var' => true
          ,'rewrite' => array(
            'slug' => 'resource-category'
          )
        )
      );

    return $taxonomies;
  }

function rg_pretty_phone($data) {
  // https://stackoverflow.com/questions/4708248/formatting-phone-numbers-in-php

  if(  preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $data,  $matches ) )
  {
      $result = $matches[1] . '-' .$matches[2] . '-' . $matches[3];
      return $result;
  } else {
    return $data;
  }
}

function rg_show_terms($id) {
    $termNames = [];
    $termObjects = get_terms(
      array( 
        'taxonomy' => 'Category', 
        'object_ids' => $id,
        //'childless' => true // this will ignore top level categories that have unchecked children
      )
    );
    if ($termObjects) {
      echo '<div class="resource-terms">';
      foreach ($termObjects as $term) {
        $termNames[] = '<span class="resource-term">' . $term->name . '</span>';
      } 
      echo implode('', $termNames);
      echo '</div>';
    }
}

function rg_list_of_resources() {
  $paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;

  // build the query
	$args = [
		'post_type' => 'rg_resource',
    'posts_per_page' => 10,
    'paged' => $paged
	];
  $filters = [];
  // @TODO SECURITY CHECKS!?!
  if ($_GET['resource-searchterm']){
    $args['s'] = $_GET['resource-searchterm'];
  }
  if ($_GET['resource-category']){
    $filters[] = [
      'taxonomy' => 'Category',
      'field' => 'term_id',
      'terms' => $_GET['resource-category']
    ];
  }

  if ($_GET['resource-location']){
    $filters[] = [
      'taxonomy' => 'Location',
      'field' => 'term_id',
      'terms' => $_GET['resource-location']
    ];
  }

  if (!empty($filters)) {
    $tag_query = [];

    foreach ($filters as $filter) {
      $tax_query[] = $filter;
    }

    if (count($filters) > 1) {
      $tax_query['relationship'] = 'AND';
    }
    $args['tax_query'] = ['relationship' => 'AND'];
    $args['tax_query'] = $tax_query;

  }

	$the_query = new WP_Query( $args );

  echo '<div class="rg-resource-list">';

	// The Loop
	if ( $the_query->have_posts() ) {
	    while ( $the_query->have_posts() ) {
	        $the_query->the_post();
	        $meta = get_post_meta(get_the_ID());
	        echo '<article class="resource-list-item">';
	        echo '<h2>' . get_the_title() . '</h2>';
          echo rg_show_terms(get_the_ID());
	        echo wpautop($meta['rg_services'][0]);
          echo (!empty($meta['rg_address_1'][0])) ? '<p class="resource-list-item__address"><strong>' . __('Address:', 'resourceguide') . '</strong> ' . rg_build_address($meta) . '</p>' : null;
          echo (!empty($meta['rg_website'][0])) ? "<p><strong>" . __('Website:', 'resourceguide') . "</strong> <a href='{$meta['rg_website'][0]}'>" .$meta['rg_website'][0] . '</a></p>' : null;
          echo (!empty($meta['rg_phone'][0])) ? "<p><strong>" . __('Phone:', 'resourceguide') . "</strong> <a href='tel:{$meta['rg_phone'][0]}'>" . rg_pretty_phone($meta['rg_phone'][0]) . '</a></p>' : null ;
          echo (!empty($meta['rg_email'][0])) ? "<p><strong>" . __('Email:', 'resourceguide') . "</strong> <a href='mailto:{$meta['rg_email'][0]}'>" .$meta['rg_email'][0] . '</a></p>' : null ;
          echo "<p>" . rg_show_hours($meta) . "</p>";
          echo "<p>" . rg_show_seasonality($meta) . "</p>";
	        echo '</article>';
	    }
	} else {
	    echo '<strong>' . __('No matching results') . '</strong>';
	}
  echo '</div>';

  echo '<div class="pagination">';
    echo paginate_links( array(
        'current' => max( 1, get_query_var('page') ),
        'total' => $the_query->max_num_pages
    ) );  
  echo '</div><!-- .pagination -->';
  
  # Restore original Post Data is good practice
  wp_reset_postdata();

}

# wp_terms_checklist() function is only included automatically for the admin panel
if ( ! function_exists( 'wp_terms_checklist' ) ) {
  include ABSPATH . 'wp-admin/includes/template.php';
}
function rg_print_filters_from_terms($inputName, $taxonomyName) {

  $output = '<ul class="resource-filter-list">';

  $selectedCats = $_GET['tax_input'][$taxonomyName];

  $output .= wp_terms_checklist(null, [
    'selected_cats' => $selectedCats,
    'checked_ontop' => false,
    'taxonomy' => $taxonomyName,
    'echo' => false,
    'walker' => new ResourceFilterWalker()
  ]);

  $output .= '</ul>';

  echo $output;
}

class ResourceFilterWalker extends Walker_Category_Checklist {
  function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
    // Skip "Uncategorized" default category
    // @TODO This should become a filterable option
    if ($element->term_id === 1) { return; }
    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
}

function rg_show_resource_filters() {

  // get page without pagination 
  $obj_id = get_queried_object_id();
  $current_url = get_permalink( $obj_id );

  // @TODO This action path should be dynamic in case the page lives somewhere else!
  echo "<form class='resource-filters' action='" . $current_url . "'>";

  echo '<div class="resource-search">';
  echo  '<input type="search" name="resource-searchterm" value="'.($_GET['resource-searchterm']).'" />';
  echo  '<input type="submit" value="search" />';
  echo '</div>';

  echo '<div class="resource-filter">';
  echo '<strong>Filter by Category</strong>';
  rg_print_filters_from_terms('resource-category', 'Category');
  echo '</div>';

  echo '<div class="resource-filter">';
  echo '<strong>Filter by Location</strong>';
  rg_print_filters_from_terms('resource-location', 'Location');
  echo '</div>';

  echo '<div class="resource-filter">';
  echo '<strong>Filter by Availability</strong><br>';
  echo '<input type="checkbox" disabled value="1" name="open-today" '. ($_GET['open-today'] ? 'checked' : null) .'>';
  echo '<label>Open today (Coming soon!)</label>';
  echo '</div>';

  echo "</form>";

}

function rg_build_address($meta) {
  $parts = [
    $meta['rg_address_1'][0],
    $meta['rg_address_2'][0],
    $meta['rg_city'][0],
    $meta['rg_state'][0],
    $meta['rg_postal_code'][0]
  ];

  $filtered = array_filter($parts, function($k){
    return !empty($k);
  });

  return implode(', ', $filtered);

}

function rg_show_hours($meta) {
  
  global $prefix;
  $days = [
    __('Monday', 'resourceguide'),
    __('Tuesday', 'resourceguide'),
    __('Wednesday', 'resourceguide'),
    __('Thursday', 'resourceguide'),
    __('Friday', 'resourceguide'),
    __('Saturday', 'resourceguide'),
    __('Sunday', 'resourceguide')
  ];

  $intervals = [];

  foreach ($days as $day) {
    $openFieldName = $prefix . strtolower($day) . '_open';
    $closeFieldName = $prefix . strtolower($day) . '_close';
    $openValue = ( !empty($meta[$openFieldName]) ) ? $meta[$openFieldName][0] : false;
    $closeValue = ( !empty($meta[$closeFieldName]) ) ? $meta[$closeFieldName][0] : false;
    if ($openValue && $closeValue) {
      $intervals[] = '<span class="resource-hours__day">' . $day . ':</span> <span class="resource-hours__time">' . "$openValue - $closeValue" . '</span>';
    } elseif ($openValue) {
      $intervals[] = '<span class="resource-hours__day">' . $day . ':</span> <span class="resource-hours__time">' . $openValue . '</span>';
    }
  }

  if (!empty($intervals)) {
    return 
       '<strong>' . __('Hours', 'resourceguide') . '</strong><br />'
      .'<span class="resource-hours">'
        . implode('', $intervals)
      .'</span>';
  }

}

function rg_show_seasonality($meta) {
  global $prefix;
  $startFieldName = $prefix . 'seasonal_start';
  $stopFieldName = $prefix . 'seasonal_stop';
  // @TODO This should be localized
  $format = "j M";
  $startValue = ( !empty($meta[$startFieldName]) ) ? date_format(date_create($meta[$startFieldName][0]), $format) : false;
  $stopValue = ( !empty($meta[$stopFieldName]) ) ? date_format(date_create($meta[$stopFieldName][0]), $format) : false;
  //@TODO This should be localized
  if ($startValue && $stopValue) {
    return "<strong>" . __('Seasonal:', 'resourceguide') . "</strong> $startValue - $stopValue";
  } elseif ($startValue) {
    return "<strong>" . __('Seasonal:', 'resourceguide') . "</strong> $startValue";
  }

}