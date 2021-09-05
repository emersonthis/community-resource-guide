<?php

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

function rg_terms($id) {
    $output = '';
    $termNames = [];
    $termObjects = get_terms(
      array( 
        'taxonomy' => 'Category', 
        'object_ids' => $id,
        //'childless' => true // this will ignore top level categories that have unchecked children
      )
    );
    if ($termObjects) {
      $output .= '<p class="resource-terms">';
      foreach ($termObjects as $term) {
        $termNames[] = '<span class="resource-term">' . $term->name . '</span>';
      } 
      $output  .= implode('', $termNames);
      $output .= '</p>';
    }
    return $output;
}

function rg_list_of_resources() {
  $output = '';
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
  if ($_GET['tax_input']['Category']){
    $filters[] = [
      'taxonomy' => 'Category',
      'field' => 'term_id',
      'terms' => $_GET['tax_input']['Category']
    ];
  }

  if ($_GET['tax_input']['Location']){
    $filters[] = [
      'taxonomy' => 'Location',
      'field' => 'term_id',
      'terms' => $_GET['tax_input']['Location']
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

  $output .= '<div class="rg-resource-list">';

	// The Loop
	if ( $the_query->have_posts() ) {
	    while ( $the_query->have_posts() ) {
	        $the_query->the_post();
            $output .= rg_resource(get_post());
	    }
	} else {
	    $output .= '<strong>' . __('No matching results') . '</strong>';
	}
  $output .= '</div>';

  $output .= '<div class="pagination">';
    $output .= paginate_links( array(
        'current' => max( 1, get_query_var('page') ),
        'total' => $the_query->max_num_pages
    ) );  
  $output .= '</div><!-- .pagination -->';

  return $output;
  
  # Restore original Post Data is good practice
  wp_reset_postdata();

}

function rg_resource($resource) {

    $output = '';
    $meta = get_post_meta($resource->ID);
    $output .= '<article id="post-' . get_the_ID() . '" class="'. implode(get_post_class('resource-list-item'), ' ') . '">';
    $output .= '<header class="entry-header alignwide">';
    $output .= rg_terms($resource->ID);
    $output .= (is_single()) ? '<h1 class="resource-title entry-title">' : '<h2 class="resource-title">';
    if (is_single()) {
        $output .= $resource->post_title;
    } else {
        // $output .= '<a href="' . get_permalink($resource) . '">';
        $output .= $resource->post_title;
        // $output .= '</a>';
    } 
    $output .= (is_single()) ? '</h1>' : '</h2>';
    $output .= '</header>';
    $output .= (is_single()) ? '<div class="entry-content">' : null;
    if (is_single()) {
        $output .= wpautop($meta['rg_services'][0]);
        $output .= (!empty($meta['rg_address_1'][0])) ? '<p class="resource-list-item__address"><strong>' . __('Address:', 'resourceguide') . '</strong> ' . rg_build_address($meta) . '</p>' : null;
        $output .= (!empty($meta['rg_website'][0])) ? "<p><strong>" . __('Website:', 'resourceguide') . "</strong> <a href='{$meta['rg_website'][0]}'>" .$meta['rg_website'][0] . '</a></p>' : null;
        $output .= (!empty($meta['rg_phone'][0])) ? "<p><strong>" . __('Phone:', 'resourceguide') . "</strong> <a href='tel:{$meta['rg_phone'][0]}'>" . rg_pretty_phone($meta['rg_phone'][0]) . '</a></p>' : null ;
        $output .= (!empty($meta['rg_email'][0])) ? "<p><strong>" . __('Email:', 'resourceguide') . "</strong> <a href='mailto:{$meta['rg_email'][0]}'>" .$meta['rg_email'][0] . '</a></p>' : null ;
        $output .= "<p class='resource-hours-p'>" . rg_show_hours($meta) . "</p>";
        $output .= "<p>" . rg_show_seasonality($meta) . "</p>";
    } else {
        $excerptLength = 200;
        $output .= '<p>'. substr($meta['rg_services'][0], 0, $excerptLength);
        $output .= ( strlen($meta['rg_services'][0]) > $excerptLength ) ? __('…') : null;
        $output .= '</p>';

        $output .= '<a class="resource-more-info" href="'. get_permalink($resource) .'">' . __('More info', 'resourceguide') . '</a>';
    }
    $output .= (is_single()) ? '</div>' : null;
    $output .= '</article>';
    return $output;
}

# wp_terms_checklist() function is only included automatically for the admin panel
if ( ! function_exists( 'wp_terms_checklist' ) ) {
  include ABSPATH . 'wp-admin/includes/template.php';
}
function rg_filters_from_terms($inputName, $taxonomyName) {

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

  return $output;
}

class ResourceFilterWalker extends Walker_Category_Checklist {
  function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output) {
    // Skip "Uncategorized" default category
    // @TODO This should become a filterable option
    if ($element->term_id === 1) { return; }
    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
}

function rg_resource_filters() {

  $output  = '';

  // get page without pagination 
  $obj_id = get_queried_object_id();
  $current_url = get_permalink( $obj_id );

  // @TODO This action path should be dynamic in case the page lives somewhere else!
  $output .= "<form class='resource-list-controls' action='" . $current_url . "'>";

  $output .= '<div class="resource-search">';
  $output .=  '<input type="search" name="resource-searchterm" value="'.($_GET['resource-searchterm']).'" />';
  $output .=  '<input type="submit" value="search" />';
  $output .= '</div>';

  $output .= '<div class="resource-filters">';

  $output .= '<div class="resource-filter">';
  $output .= '<strong>Filter by Category</strong>';
  $output .= rg_filters_from_terms('resource-category', 'Category');
  $output .= '</div>';

  $output .= '<div class="resource-filter">';
  $output .= '<strong>Filter by Location</strong>';
  $output .= rg_filters_from_terms('resource-location', 'Location');
  $output .= '</div>';

  $output .= '<div class="resource-filter">';
  $output .= '<strong>Filter by Availability</strong><br>';
  $output .= '<input type="checkbox" disabled value="1" name="open-today" '. ($_GET['open-today'] ? 'checked' : null) .'>';
  $output .= '<label>Open today (Coming soon!)</label>';
  $output .= '</div>';

  $output .= '</div>'; // .resource-filters

  $output .= "</form>";

  return $output;

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
      $intervals[] = '<span class="resource-hours__day">' . $day . '</span> <span class="resource-hours__time">' . "$openValue - $closeValue" . '</span>';
    } elseif ($openValue) {
      $intervals[] = '<span class="resource-hours__day">' . $day . '</span> <span class="resource-hours__time">' . $openValue . '</span>';
    }
  }

  if (!empty($intervals)) {
    return 
       '<strong>' . __('Hours:', 'resourceguide') . '</strong> '
      .'<span class="resource-hours">'
        . implode('<span class="hours-separator"></span>', $intervals)
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