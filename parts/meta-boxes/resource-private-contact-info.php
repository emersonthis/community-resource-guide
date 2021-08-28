<?php
/*
Title: Private Contact Info
Post Type: rg_resource
Order: 21
*/

$prefix = 'rg_';
$textdomain = 'resourceguide';

piklist('field', array(
  'type' => 'text',
  'field' => $prefix.'private_contact_person',
  'label' => 'Contact person',
  'description' => 'This name is not displayed to the public',
  'attributes'=>[
    'placeholder' => 'John Snow'
  ]
));

piklist('field', array(
  'type' => 'tel'
  ,'field' => $prefix.'private_phone'
  ,'label' => 'Phone number'
  ,'description' => '10-digit phone number w/o spaces or punctuation'
  // ,'value' => ''
  // ,'help' => 'This is help text.'
  ,'attributes' => array(
    'class' => 'text',
    'placeholder' => '4445556666'
  )
));

piklist('field', array(
  'type' => 'email',
  'field' => $prefix.'private_email',
  'label' => 'Email',
  'attributes'=>[
    'placeholder' => 'info@example.com'
  ]
));
