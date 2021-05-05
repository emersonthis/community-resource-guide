<?php
/*
Title: Public Contact Info
Post Type: rg_resource
Order: 20
*/

$prefix = 'rg_';
$textdomain = 'resourceguide';

piklist('field', array(
  'type' => 'url',
  'field' => $prefix.'website',
  'label' => 'Website',
  'attributes'=>[
    'placeholder' => 'https://example.com'
  ]
));

piklist('field', array(
  'type' => 'tel'
  ,'field' => $prefix.'phone'
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
  'field' => $prefix.'email',
  'label' => 'Email',
  'attributes'=>[
    'placeholder' => 'info@example.com'
  ]
));


piklist('field', array(
  'type' => 'group'
  // ,'field' => 'address_group' // removing this parameter saves all fields as separate meta
  ,'label' => __('Address', 'resourceguide')
  ,'list' => false
  ,'description' => __('The physical location of this resource.', $textdomain)
  ,'fields' => array(
    array(
      'type' => 'text'
      ,'field' => $prefix . 'address_1'
      ,'label' => __('Street Address', $textdomain)
      // ,'required' => true
      ,'columns' => 12
      ,'attributes' => array(
        'placeholder' => 'Street Address'
      )
    )
    ,array(
      'type' => 'text'
      ,'field' => $prefix . 'address_2'
      ,'label' => __('PO Box, Suite, etc.', $textdomain)
      ,'columns' => 12
      ,'attributes' => array(
        'placeholder' => 'PO Box, Suite, etc.'
      )
    )
    ,array(
      'type' => 'text'
      ,'field' => $prefix . 'city'
      ,'label' => __('City', $textdomain)
      ,'columns' => 5
      ,'attributes' => array(
        'placeholder' => 'City'
      )
    )
    ,array(
      'type' => 'text'
      ,'field' => $prefix . 'state'
      ,'label' => __('State', $textdomain)
      ,'columns' => 4
      ,'attributes' => [
        'placeholder' => 'OR'
      ]
    )
    ,array(
      'type' => 'text'
      ,'field' => $prefix . 'postal_code'
      ,'label' => __('Postal Code', $textdomain)
      ,'columns' => 3
      ,'attributes' => array(
        'placeholder' => 'Postal Code'
      )
    )
  )
));

