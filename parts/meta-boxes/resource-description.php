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
  ,'description' => 'What does this resource do or provide? Try to keep this brief and to-the-point 😉'
  // ,'value' => ''
  // ,'help' => 'This is help text.'
  ,'attributes' => array(
    'class' => 'large-text',
  )
));


