<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Users extends REST_Controller {
    
    function __construct() {
        parent::__construct();
        $this->load->helper('api');
        
               $this->load->helper('inflector');

        // $this->_fetch_table();

        $this->_database = $this->db;
        
        //Configure limits on our controller methods
        //Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        //$this->methods['users_get']['limit'] = 100; // 100 requests per hour per user/key
        //$this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
        //$this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key 
    }
    
    
        /**
     * 
     */
     
     public function getChatDevice_post(){   
        
        $this->load->library('form_validation');
        // $this->post('token')  = 'asd';
        $data = remove_unknown_fields($this->post(), $this->form_validation->get_field_names('getChatDevice_post'));
        
        $this->form_validation->set_data($data);
        
        if($this->form_validation->run('getChatDevice_post') !=false){
            $this->load->model('Model_users');
             
            $exists = $this->Model_users->get_by(array('token' => $this->post('token')));

            if($exists){
                 
                $this->db->select(['sno','access_token','mesibo_uid','name','user_address']);
                $this->db->from('chat_device_details');
                $query1 = $this->db->get();
                $aa  = $query1->result_array();
                 
                $this->response(array('status'=>200, 'message'=>'success', 'data'=> $aa), REST_Controller::HTTP_CREATED);
            
                
            }
            else
            {
                $this->response(array('status'=>200, 'message'=>'An unexpected Token error' ,'date'=> null), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
              
        }
        else{
            // BAD_REQUEST (400) being the HTTP response code
            $this->response(array('status'=>400,'message'=>$this->form_validation->get_errors_as_array(), 'data'=>null ), REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function chatDevice_post(){   
        
        $this->load->library('form_validation');
        // $this->post('token')  = 'asd';
        $data = remove_unknown_fields($this->post(), $this->form_validation->get_field_names('chatDevice_post'));
          
           
        $this->form_validation->set_data($data);
        
        if($this->form_validation->run('chatDevice_post') !=false){
            $this->load->model('Model_chat');
             
            $exists = $this->Model_chat->get_by(array('email' => $this->post('email'),'password' => $this->post('password')));

            if($exists){
                $exists_copy = $this->Model_chat->get_by(array('imei' => $this->post('imei')));  
                if($exists['imei'] == "")
                {
                     $data1 = [ 'imei' => $this->post('imei')];
                    $updated = $this->Model_chat->update($exists['sno'], $data1);
                    $exists_n = $this->Model_chat->get_by(array('sno' => $exists['sno']));
                    unset($exists_n['email']);
                    unset($exists_n['password']);
                    $this->response(array('status'=>200, 'message'=>'success', 'data'=> $exists_n), REST_Controller::HTTP_CREATED);    
                 
                }
                else
                {
                    
                    $this->response(array('status'=>400, 'message'=>'This imei is already logged in from different device' ,'date'=> null), REST_Controller::HTTP_OK);       
                    
                }
                
            
                
            }
            else
            {
                $this->response(array('status'=>400, 'message'=>'An unexpected error while trying to create the user' ,'date'=> null), REST_Controller::HTTP_OK);
            }
              
        }
        else{
            // BAD_REQUEST (400) being the HTTP response code
            $this->response(array('status'=>400,'message'=>$this->form_validation->get_errors_as_array(), 'data'=>null ), REST_Controller::HTTP_OK);
        }
    }
    
    
    
    public function profileChat_post()
    {
        
        $token = $this->post('imei');
         $proileID = $this->post('id');
        
        $whereToken = "imei=$token";
        if($proileID === NULL){
         return $this->response(array('status' =>422 ,'message'=>'Invalid ID','data'=>null), REST_Controller::HTTP_OK); //
        }
        if($token === NULL){
         return $this->response(array('status' =>422 ,'message'=>'Invalid token','data'=>null), REST_Controller::HTTP_OK); //
        }
        else
        {
            $this->db->select('*');
            $this->db->from('chat_device_details');
            $this->db->where('imei',"$token");
            $query1 = $this->db->get();
            $where = "registered_profile.id=$proileID";
            

            
            
            if($query1->result_array()){
                        $this->db->select('*','registered_profile.id as pid','registered_profile.*','registered_image.id as iid','registered_image.*','comments_tag.id as tid','comments_tag.*','comments_table.id as cid','comments_table.*');
                       // $this->db->select('*');
                        $this->db->from('registered_profile');
                         $this->db->where($where);
                        $query = $this->db->get();

                        // image files
                        $this->db->select("*,(CASE 
            WHEN is_unlock = 0 THEN 'false'
            WHEN is_unlock = 1 THEN 'true'
            END) as is_unlock");
                        $this->db->from('registered_image');
                        $this->db->where('profileID',"$proileID");
                        //$this->db->where('is_unlock = ',0,false); 
                        $query_registered_image = $this->db->get();
                        
                        // comments_tag
                        $this->db->select('*');
                        
                        $this->db->from('comments_tag');
                        $this->db->where('profileID',"$proileID");
                        $query_comments_tag = $this->db->get();
                        
                        // comments_table
                        $this->db->select("*,(CASE 
            WHEN recommend_flag = 0 THEN 'false'
            WHEN recommend_flag = 1 THEN 'true'
            END) as recommend_flag,(CASE 
            WHEN show_flag = 0 THEN 'false'
            WHEN show_flag = 1 THEN 'true'
            END) as show_flag");
                        $this->db->from('comments_table');
                        $this->db->where('profileID',"$proileID");
                        $query_comments_table = $this->db->get();
                        
                        
                        
                         $arrayName = array();
                         
                        foreach ($query->result() as $row)
                        {
                                  
                                $arrayName['id'] = $row->id;
                                $arrayName['name'] = $row->name;
                                $arrayName['age'] = $row->age;
                                $arrayName['location'] = $row->location;
                                $arrayName['likes'] = $row->likes;
                                $arrayName['hobbies'] = $row->hobbies;
                                $arrayName['primary_img'] = $row->primary_img;
                                $arrayName['bio'] = $row->bio;
                                $arrayName['km_away'] = $row->km_away;
                                $arrayName['is_private_album'] = (bool) $row->is_private_album;
                                $arrayName['comment_no'] = $row->comment_no;
                                $arrayName['likes_no'] = $row->likes_no;
                                $arrayName['image_count'] = $row->image_count;
                                $arrayName['is_hot'] = (bool) $row->is_hot;
                                $arrayName['registered_images'] = $query_registered_image->result();
                                $arrayName['comments_tags'] = $query_comments_tag->result();
                                $arrayName['comments'] =  $query_comments_table->result();
                                 
                             
                        }
                        
                        $arrayNameq = $query->result_array(array('registered_images'=>array(),'comments_tags'=>array(),'comments'=>array()));
                         
                        return $this->response(array('status' =>200 ,'message'=>'Success','data'=>$arrayName), REST_Controller::HTTP_OK); //      
            }
            else
            {
                return $this->response(array('status' =>422 ,'message'=>'Invalid token','data'=>null), 422); //
            }
        }
        
         
        
    }
 
    public function purchase_post(){   
        
        $this->load->library('form_validation');
        // $this->post('token')  = 'asd';
        $data = remove_unknown_fields($this->post(), $this->form_validation->get_field_names('purchase_post'));
          
        $transaction = hexdec(uniqid());
        array_push($data,$data['transaction_id']=$transaction);
        $removed = array_pop($data);
          
        $this->form_validation->set_data($data);
        
        if($this->form_validation->run('purchase_post') !=false){
            $this->load->model('Model_purchase');
            
            $exists = $this->Model_purchase->get_by(array('transaction_id' => $this->post('transaction_id')));

            if($exists){
                $this->response(array('status'=>400, 'message'=>'The specified transaction id  already exist in the system.', 'data'=> null), REST_Controller::HTTP_CONFLICT);
            }
               
            $user_id = $this->Model_purchase->insert($data);


            if(!$user_id){
                $this->response(array('status'=>400, 'message'=>'An unexpected error while trying to pay' ,'date'=> null), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
            else{
                $user = $this->Model_users->get_by(array('user_id' => $user_id ));
                $this->response(array('status'=> 200, 'message'=> 'Paid successfully','data'=>$user), REST_Controller::HTTP_CREATED);
            }
        }
        else{
            // BAD_REQUEST (400) being the HTTP response code
            $this->response(array('status'=>400,'message'=>$this->form_validation->get_errors_as_array(), 'data'=>null ), REST_Controller::HTTP_BAD_REQUEST);
        }
    }
 
    public function registeredProfile_post()
    {
         
        $token = $this->post('token');
        
        $whereToken = "token=$token";
        
        if($token === NULL){
         return $this->response(array('status' =>422 ,'message'=>'Invalid token','data'=>null), REST_Controller::HTTP_OK); //
        }
        else
        {
            $this->db->select('*');
            $this->db->from('profile_user');
            $this->db->where('token',"$token");
            $query1 = $this->db->get();
            
            
            if($query1->result_array()){
                
                        $this->db->select('*','registered_profile.id as pid','registered_profile.*','registered_image.id as iid','registered_image.*','comments_tag.id as tid','comments_tag.*','comments_table.id as cid','comments_table.*');
                       // $this->db->select('*');
                        $this->db->from('registered_profile'); 
                        $query = $this->db->get();
                        
                      
                        
                        
                        $arrayNameq = $query->result_array();
                         
                        return $this->response(array('status' =>200 ,'message'=>'Success','data'=>$arrayNameq), REST_Controller::HTTP_OK); //      
            }
        }

         
    
        return $this->response(array('status' =>200 ,'message'=>'Success','data'=>$query->result_array()), REST_Controller::HTTP_OK); 
    }
    
    public function profile_post()
    {
        
        $token = $this->post('token');
        $proileID = $this->post('id');
        
        $whereToken = "token=$token";
        if($proileID === NULL){
         return $this->response(array('status' =>422 ,'message'=>'Invalid ID','data'=>null), REST_Controller::HTTP_OK); //
        }
        if($token === NULL){
         return $this->response(array('status' =>422 ,'message'=>'Invalid token','data'=>null), REST_Controller::HTTP_OK); //
        }
        else
        {
            $this->db->select('*');
            $this->db->from('profile_user');
            $this->db->where('token',"$token");
            $query1 = $this->db->get();
            $where = "registered_profile.id=$proileID";
            

            
            
            if($query1->result_array()){
                        $this->db->select('*','registered_profile.id as pid','registered_profile.*','registered_image.id as iid','registered_image.*','comments_tag.id as tid','comments_tag.*','comments_table.id as cid','comments_table.*');
                       // $this->db->select('*');
                        $this->db->from('registered_profile');
                        $this->db->where($where);
                        $query = $this->db->get();

                        // image files
                        $this->db->select("*,(CASE 
            WHEN is_unlock = 0 THEN 'false'
            WHEN is_unlock = 1 THEN 'true'
            END) as is_unlock");
                        $this->db->from('registered_image');
                        $this->db->where('profileID',"$proileID");
                        //$this->db->where('is_unlock = ',0,false); 
                        $query_registered_image = $this->db->get();
                        
                        // comments_tag
                        $this->db->select('*');
                        
                        $this->db->from('comments_tag');
                        $this->db->where('profileID',"$proileID");
                        $query_comments_tag = $this->db->get();
                        
                        // comments_table
                        $this->db->select("*,(CASE 
            WHEN recommend_flag = 0 THEN 'false'
            WHEN recommend_flag = 1 THEN 'true'
            END) as recommend_flag,(CASE 
            WHEN show_flag = 0 THEN 'false'
            WHEN show_flag = 1 THEN 'true'
            END) as show_flag");
                        $this->db->from('comments_table');
                        $this->db->where('profileID',"$proileID");
                        $query_comments_table = $this->db->get();
                        
                        
                        
                         $arrayName = array();
                         
                        foreach ($query->result() as $row)
                        {
                                  
                                $arrayName['id'] = $row->id;
                                $arrayName['name'] = $row->name;
                                $arrayName['age'] = $row->age;
                                $arrayName['location'] = $row->location;
                                $arrayName['likes'] = $row->likes;
                                $arrayName['hobbies'] = $row->hobbies;
                                $arrayName['primary_img'] = $row->primary_img;
                                $arrayName['bio'] = $row->bio;
                                $arrayName['km_away'] = $row->km_away;
                                $arrayName['is_private_album'] = (bool) $row->is_private_album;
                                $arrayName['comment_no'] = $row->comment_no;
                                $arrayName['likes_no'] = $row->likes_no;
                                $arrayName['image_count'] = $row->image_count;
                                $arrayName['is_hot'] = (bool) $row->is_hot;
                                $arrayName['registered_images'] = $query_registered_image->result();
                                $arrayName['comments_tags'] = $query_comments_tag->result();
                                $arrayName['comments'] =  $query_comments_table->result();
                                 
                             
                        }
                        
                        $arrayNameq = $query->result_array(array('registered_images'=>array(),'comments_tags'=>array(),'comments'=>array()));
                         
                        return $this->response(array('status' =>200 ,'message'=>'Success','data'=>$arrayName), REST_Controller::HTTP_OK); //      
            }
            else
            {
                return $this->response(array('status' =>422 ,'message'=>'Invalid token','data'=>null), 422); //
            }
        }
        
         
        
    }
    
     
    
 
     
    /**
    * 
    */
    public function users_get(){   
        $this->load->model('Model_users');
        $user_id = $this->get('id');
        
        // If the id parameter doesn't exist return all users
        if($user_id === NULL){
            $users = $this->Model_users->get_many_by(array('status' =>  array('active', 'inactive')));
            if($users){

                return $this->response(array('status' =>200 ,'message'=>'Success','data'=>$users), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                 
                // Set the response and exit
                
            }
            else{
                // Set the response and exit
                $this->response(array('status'=> 404, 'message'=> 'The Specified user could not be found','data'=>null), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }
        
        // Find and return a single record for a particular user.
        $user_id = $this->get('id');
        if ($user_id <= 0){
            // Invalid id, set the response and exit.
            $this->response(array('Status' =>400 ,'message'=>'Failed','data'=>null), REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        $user = $this->Model_users->get_by(array('user_id' => $user_id, 'status' => array('active', 'inactive')));
        if(isset($user['user_id'])){
            $this->response(array('status'=>200,'message'=> 'success', 'data'=> $user));
        }
        else{
           $this->response(array('status'=> 404, 'message'=> 'The Specified user could not be found','data'=>null), REST_Controller::HTTP_NOT_FOUND);
        } 
    } 
    
    /**
     * 
     */
    
    public function users_post(){   
        
        $this->load->library('form_validation');
        // $this->post('token')  = 'asd';
        $data = remove_unknown_fields($this->post(), $this->form_validation->get_field_names('user_post'));
          
          $tokena = uniqid().uniqid(8);
          array_push($data,$data['token']=$tokena);
          $removed = array_pop($data);
 
          
        $this->form_validation->set_data($data);
        
        if($this->form_validation->run('user_post') !=false){
            $this->load->model('Model_users');
            
            $exists = $this->Model_users->get_by(array('imei' => $this->post('imei')));

            if($exists){
                $this->response(array('status'=>200, 'message'=>'The specified imei address already exist in the system.', 'data'=> $exists), 200);
            }
             
           // array_push($data,$data['token']="asduasdn121");
             
           
            $user_id = $this->Model_users->insert($data);


            if(!$user_id){
                $this->response(array('status'=>400, 'message'=>'An unexpected error while trying to create the user' ,'date'=> null), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
            else{
                $user = $this->Model_users->get_by(array('user_id' => $user_id ));
                $this->response(array('status'=> 200, 'message'=> 'user created','data'=>$user), REST_Controller::HTTP_CREATED);
            }
        }
        else{
            // BAD_REQUEST (400) being the HTTP response code
            $this->response(array('status'=>400,'message'=>$this->form_validation->get_errors_as_array(), 'data'=>null ), REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * 
     */
    public function users_put(){    
        //$user_id = $this->uri->segment(3);
        $user_id = $this->get('id');
        $this->load->model('Model_users');
        
        // Find and return a single record for a particular user.
        $user_id = (int) $user_id;
        // Validate the id.
        if ($user_id <= 0){
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        $user = $this->Model_users->get_by(array('user_id' => $user_id, 'status' =>  array('active', 'inactive')));
        
        if(isset($user['user_id'])){
            $this->load->library('form_validation');
            $data = remove_unknown_fields($this->put(), $this->form_validation->get_field_names('user_put'));
            $this->form_validation->set_data($data);
            if($this->form_validation->run('user_put') !=false){
                $this->load->model('Model_users');
                $safe_email = !isset($data['imei']) || $data['imei'] == $user['imei'] || !$this->Model_users->get_by(array('imei' => $data['imei']));
                if(!$safe_email){
                    $this->response(array('status'=>400, 'message'=> 'The specified imei address already in the use.'), REST_Controller::HTTP_CONFLICT);
                } 
                $updated = $this->Model_users->update($user_id, $data);
                if(!$updated){
                    $this->response(array('status'=>400, 'message'=> 'An unexpected error while trying to updated the user'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
                else{
                    $this->response(array('status'=> 200, 'message'=> 'user updated'), REST_Controller::HTTP_OK);
                }
            }
            else{
                // BAD_REQUEST (400) being the HTTP response code
                $this->response(array('status'=>404, 'message'=> $this->form_validation->get_errors_as_array()), REST_Controller::HTTP_BAD_REQUEST);
            }
        }
        else{
           $this->response(array('status'=> 404, 'message'=> 'The Specified user could not be found'), REST_Controller::HTTP_NOT_FOUND);
        }  
    }
    
    /**
     * 
     */
    public function users_delete(){    
        //$user_id = $this->uri->segment(3);
        $user_id = $this->get('id');
        $this->load->model('Model_users');

        // Find and return a single record for a particular user.
        $user_id = (int) $user_id;
        // Validate the id.
        if ($user_id <= 0){
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }
        $user = $this->Model_users->get_by(array('user_id' => $user_id, 'status' =>  array('active', 'inactive')));
        if(isset($user['user_id'])){
            $data['status'] = 'deleted';
            $deleted = $this->Model_users->update($user_id, $data);
            if(!$deleted){
                $this->response(array('status'=>400, 'message'=> 'An unexpected error while trying to delete the user'), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
            else{
                $this->response(array('status'=> 200, 'message'=> 'User deleted'), REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
            }
        }
        else{
           $this->response(array('status'=> 404, 'message'=> 'The Specified user could not be found'), REST_Controller::HTTP_NOT_FOUND);
        }  
    }
    
  
}
