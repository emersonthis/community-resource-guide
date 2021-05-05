<?php
/*
Title: Availability
Post Type: rg_resource
Order: 30
*/

$prefix = 'rg_';
$textdomain = 'resourceguide';

function rg_day_hour_fields() {
  global $prefix;
  $days = [
    __('Monday', $texdomain),
    __('Tuesday', $texdomain),
    __('Wednesday', $texdomain),
    __('Thursday', $texdomain),
    __('Friday', $texdomain),
    __('Saturday', $texdomain),
    __('Sunday', $texdomain),
  ];

  $fields = [];
  foreach($days as $day) {
    $fields[] = array(
      'type' => 'time'
      ,'field' => $prefix . strtolower($day).'_open'
      ,'label' => __("$day opening hour", $textdomain)
      ,'columns' => 6
      // ,'attributes' => array(
      //   'placeholder' => '06:00'
      // )
    );
    $fields[] = array(
      'type' => 'time'
      ,'field' => $prefix . strtolower($day).'_close'
      ,'label' => __("$day closing hour", $textdomain)
      ,'columns' => 6
      // ,'attributes' => array(
      //   'placeholder' => '18:00'
      // )
    );
  }
  return $fields;
}

piklist('field', array(
  'type' => 'group'
  // ,'field' => 'address_group' // removing this parameter saves all fields as separate meta
  ,'label' => __('Hours', 'resourceguide')
  ,'list' => false
  ,'description' => __('When is this resource available?', $textdomain)
  ,'fields' => rg_day_hour_fields()
));

// // @TODO This shows a year by default, which is confusing
// piklist('field', array(
//   'type' => 'group'
//   ,'label' => __('Seasonal availability', $textdomain)
//   ,'list' => false
//   ,'description' => __('Is this resource only available certain parts of the year? Leave this blank for year-round services.', $textdomain)
//   ,'fields' => array(
//     array(
//       'type' => 'date'
//       ,'field' => $prefix . 'seasonal_start'
//       ,'label' => __('Seasonal start day', $textdomain)
//       ,'columns' => 6
//     )
//     ,array(
//       'type' => 'date'
//       ,'field' => $prefix . 'seasonal_stop'
//       ,'label' => __('Seasonal end day', $textdomain)
//       ,'columns' => 6
//     )
//   )
// ));