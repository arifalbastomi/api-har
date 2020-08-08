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
class Sensor extends REST_Controller {

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
	
	public function setdata_post(){

		$myAuth = $this->var_global['Auth'];
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
			$auth = $result->authorization;
			if($auth==$myAuth){

				$id = $result->id;
				$id_user = $result->id_user;
				$id_project = $result->id_project;
				$gyro_x = $result->gyro_x;
				$gyro_y = $result->gyro_y;
				$gyro_z = $result->gyro_z;

				$acc_x = $result->acc_x;
				$acc_y = $result->acc_y;
				$acc_z = $result->acc_z;

				$hrv = $result->hrv;
				$createdate = $result->createdate;
				$attempt = $result->attempt;
				$insert=$this->db->query("insert into detailproject (id_user,id_project,gyro_x,gyro_y,gyro_z,acc_x,acc_y,acc_z,hrv,createdate,attempt) values
				        ('".$id_user."','".$id_project."','".$gyro_x."','".$gyro_y."','".$gyro_z."','".$acc_x."','".$acc_y."','".$acc_z."','".$hrv."','".$createdate."','".$attempt."')");

				if($insert){
						$this->response([
							'status_code' => 200,
							'message' =>"success",
							'id' =>$id
							
						], REST_Controller::HTTP_OK);
				}else{

					$this->response([
						'status_code' => 400,
						'message' =>"failed insert"
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
	
	public function setdata_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport method"
		], REST_Controller::HTTP_NOT_FOUND);
		
	}

	public function setresult_post(){

		$myAuth = $this->var_global['Auth'];
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
			$auth = $result->authorization;
			if($auth==$myAuth){

				$id_user = $result->id_user;
				$id_project = $result->id_project;
				$activity = $result->activity;
				$attempt = $result->attempt;
				
				$insert=$this->db->query("insert into result (id_user,id_project,activity,attempt,createdate) values
				        ('".$id_user."','".$id_project."','".$activity."','".$attempt."',now() )");

				if($insert){
						$this->response([
							'status_code' => 200,
							'message' =>"success"
							
						], REST_Controller::HTTP_OK);
				}else{

					$this->response([
						'status_code' => 400,
						'message' =>"failed insert"
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
	
	public function setresult_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport method"
		], REST_Controller::HTTP_NOT_FOUND);
		
	}

}
