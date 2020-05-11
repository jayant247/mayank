<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @file form_validation.php
 */

$config = array(
    'purchase_post' => array(
        array('field' => 'user_id', 'label' => 'user_id', 'rules' => 'trim|required|max_length[20]'),
        array('field' => 'amount', 'label' => 'amount', 'rules' => 'trim|required|max_length[20]'),
    ),
    'user_post' => array(
        array('field' => 'name', 'label' => 'Name', 'rules' => 'trim|required|max_length[20]'),
       // array('field' => 'token', 'label' => 'token', 'rules' => 'trim|required'),
        array('field' => 'imei', 'label' => 'imei number', 'rules' => 'trim|required'),
        // array('field' => 'firebase_token', 'label' => 'firebase token ', 'rules' => 'trim|required'),
    
        // array('field' => 'status', 'label' => 'Status', 'rules' => 'trim|required'),
        // array('field' => 'role', 'label' => 'Role', 'rules' => 'trim|required'),
    ),
     'chatDevice_post' => array(
        array('field' => 'email', 'label' => 'Email', 'rules' => 'trim|required'), 
        array('field' => 'password', 'label' => 'password', 'rules' => 'trim|required'),
        array('field' => 'imei', 'label' => 'imei number', 'rules' => 'trim|required'),
    ), 
    'getChatDevice_post' => array( 
        array('field' => 'token', 'label' => 'token', 'rules' => 'trim|required'),
    ), 
    
    'user_put' => array(
                array('field' => 'name', 'label' => 'Name', 'rules' => 'trim|required|max_length[20]'),
      //          array('field' => 'token', 'label' => 'token', 'rules' => 'trim|required'),
                array('field' => 'imei', 'label' => 'imei number', 'rules' => 'trim|required'),
            //    array('field' => 'firebase_token', 'label' => 'firebase token ', 'rules' => 'trim|required'),
    ),
);

