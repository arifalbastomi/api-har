<?php

use Restserver\Libraries\REST_Controller;
use Restserver\Libraries\Veritrans;
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Veritrans.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Project extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		header('Content-Type: application/json');
		$this->load->database();
		
		error_reporting(E_ALL ^ E_NOTICE); 
		error_reporting(0);
		ini_set('display_errors', 0);	
		$this->var_global = array(
			'Auth' => '5e636b16-df7f-4a53-afbe-497e6fe07edc',
        );
		
	}
	
	public function getproject_post(){

		//$auth=$this->input->get_request_header('Authorization');
		$myAuth = $this->var_global['Auth'];
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
			$auth = $result->authorization;
			if($auth==$myAuth){
				$id = $result->id;
				
				$getdata=$this->db->query("select * from masterproject where id_project='".$id."' ");

				if($getdata->num_rows()){
					$row=$getdata->row();
					$u_password=$row->password;
					if($password==$u_password){
					
						$this->response([
							'status_code' => 200,
							'message' =>"success",
							'id_project' => $row->id_project,
							'insert_interval' =>$row->insert_interval,
							'sync_interval' =>$row->sync_interval
						], REST_Controller::HTTP_OK);

					}else{
						
						$this->response([
							'status_code' => 400,
							'message' =>"wrong password"
						], REST_Controller::HTTP_OK);

					}
					
				}else{

					$this->response([
						'status_code' => 400,
						'message' =>"project not found"
					], REST_Controller::HTTP_OK);

				}
			}else{

				$this->response([
					'status_code' => 400,
					'message' =>"invalid Auth"
				], REST_Controller::HTTP_OK);

			}
			
		}else{
			$this->response([
				'status_code' => 400,
				'message' =>"empty params"
			], REST_Controller::HTTP_OK);
		}

	}
	
	public function getproject_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport method"
		], REST_Controller::HTTP_NOT_FOUND);
		
	}

	
	
	

}
