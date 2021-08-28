<?php
/*
Title: Description
Post Type: rg_resource
Order: 5
*/

$prefix = 'rg_';
$textdomain = 'resourceguide';

piklist('field', array(
  'type' => 'textarea'
  ,'field' => $prefix.'services'
  ,'label' => 'Services'
  // ,'value' => ''
  // ,'help' => 'This is help text.'
  ,'attributes' => array(
    'class' => 'large-text',
  )
));


