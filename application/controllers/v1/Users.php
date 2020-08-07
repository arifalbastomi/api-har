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
class Users extends REST_Controller {

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
	
	public function login_post(){

		//$auth=$this->input->get_request_header('Authorization');
		$myAuth = $this->var_global['Auth'];
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
			$auth = $result->authorization;
			if($auth==$myAuth){
				$email = $result->email;
				$password = $result->password;
				$getdata=$this->db->query("select * from users where email='".$email."' ");

				if($getdata->num_rows()){
					$row=$getdata->row();
					$u_password=$row->password;
					if($password==$u_password){
					
						$this->response([
							'status_code' => 200,
							'message' =>"success",
							'id_user' => $row->id_user,
							'nama' =>$row->nama,
							'email' =>$row->email,
							'jenis_kelamin' =>$row->jenis_kelamin,
							'usia' =>$row->usia,
							'tgl_lahir' =>$row->tgl_lahir
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
						'message' =>"email not register"
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
	
	public function login_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport method"
		], REST_Controller::HTTP_NOT_FOUND);
		
	}

	public function check_auth($auth,$uid){
		
		$this->db->where('uid', $uid);
        $setting = $this->db->get('setting')->row();
		
		return $setting;
	}
	
	public function transaction_post(){
		
		$auth=$this->input->get_request_header('Authorization');
		$uid=$this->input->get_request_header('Uid');
		$server_key=$this->input->get_request_header('server_key');
		
		if($auth!="" && $uid!="" && $server_key!=""){
			
			$setting=$this->check_auth($auth,$uid);
			if(!empty($setting)){
				$branch=$setting->branch;
				$apikey=$setting->apikey;
				
				if($apikey==$auth){
					
					$cabang = $this->post('outlet');
					$amount = $this->post('amount');
					$amount=floatval($amount);
					
					if($cabang!="" && $amount!=""){
						
						
						if($amount > 0){
							
							
				
							
							$Brg = "BB01";
							$NamaBarang ="Omega product";
							$Jml = 1;
							$Jml = floatval($Jml);
							
							$items = array();
							$items_number=0;
							$items[$items_number] = array ('id' => $Brg,'price' => $amount,'quantity' => $Jml,'name' => $NamaBarang );
								
							
							
							//$order_id=uniqid();
							$datetime = new DateTime();
							$tgl=$datetime->format('YmdHis');
							$kodenota= "Gopay".$cabang."/".$tgl;
							$order_id = $kodenota;
							$order_id = str_replace("/","-",$order_id);
							$transaction_details = array(
								'order_id' 			=> $order_id,
								'gross_amount' 	=> $amount
							);

							// Populate items
							/*$items = [
								array(
									'id' 				=> 'item1',
									'price' 		=> 500,
									'quantity' 	=> 1,
									'name' 			=> 'Adidas f50'
								),
								array(
									'id'				=> 'item2',
									'price' 		=> 500,
									'quantity' 	=> 1,
									'name' 			=> 'Nike N90'
								)
							];*/

							// Populate customer's Info
							$customer_details = array(
								'first_name' 			=> "Arif",
								'last_name' 			=> "Al Bastomi",
								'email' 				=> "tomysantoso23@gmail.com",
								'phone' 				=> "081336733162"
								);
							 $gopay = array (
								'enable_callback' =>false,
								'last_name' => "someapps://callback"
							  );
							$transaction_data = array(
								'payment_type'      => 'gopay', 
								'transaction_details'   => $transaction_details,
								'item_details' => $items,
								//'customer_details'=>$customer_details,
								'gopay'=>$gopay
								
							);
							
							//print_r($transaction_data);
							
							$server_key = $server_key;
							$params = array('server_key' => $server_key, 'production' => true);
							$this->load->library('veritrans',$params);
							$this->veritrans->config($params);
							//$params = array('server_key' => 'SB-Mid-server-kqxBpNGhbcHWWXHGXN-TpGNL', 'production' => false);
							//$this->load->library('veritrans');
							//$this->veritrans->config($params);
							
							$response = null;
							try
							{
								$response= $this->veritrans->vtdirect_charge($transaction_data);
							} 
							catch (Exception $e) 
							{
								echo $e->getMessage();	
							}

							//var_dump($response);
							if($response)
							{
								if($response->transaction_status == "capture")
								{
									$data =  array(
											//'kodenota' => $kodenota,
											'transaction_code' => $order_id,
											'transaction_status' => $response->transaction_status
											);
									$this->response([
										'status_code' => 200,
										'message' => "",
										'data' =>$data
									], REST_Controller::HTTP_OK);
								}
								else if($response->transaction_status == "deny")
								{
									$data =  array(
											//'kodenota' => $kodenota,
											'transaction_code' => $order_id,
											'transaction_status' => $response->transaction_status
											);
									$this->response([
										'status_code' => 200,
										'message' => "",
										'data' =>$data
									], REST_Controller::HTTP_OK);
								}
								else if($response->transaction_status == "challenge")
								{
									$data =  array(
											//'kodenota' => $kodenota,
											'transaction_code' => $order_id,
											'transaction_status' => $response->transaction_status
											);
									$this->response([
										'status_code' => 200,
										'message' => "",
										'data' =>$data
									], REST_Controller::HTTP_OK);
								}
								else if($response->transaction_status == "pending")
								{
									//error
									if($response->status_code==201){
										$insertmysql=$this->db->query("insert into trx_gopay(trx_id,kodenota,transaction_status,createdate,tglentry,fraud_status,amount,transaction_name) values ('".$order_id."','".$order_id."','".$response->transaction_status."',now(),now(),'".$response->fraud_status."','".$amount."','gopay')");
										//$insertmysql=$this->db->query("insert into trx_gopay(trx_id,kodenota,transaction_status,createdate,tglentry,fraud_status) values ('".$order_id."','".$kodenota."','".$response->transaction_status."',now(),now(),'".$response->fraud_status."')");
										$actions=$response->actions;
										$i=0;
										$urlqr="";
										foreach($actions as $item) {
											$name=$item->name; 
											$url=$item->url;
											//echo $i." ".$url."<br>";
											if($i==0){
												$urlqr=$url;
												$updateurl=$this->db->query("update trx_gopay set url_qr='".$url."' where trx_id='".$order_id."'");
											}else if($i==2){
												$updateurl=$this->db->query("update trx_gopay set url_status='".$url."' where trx_id='".$order_id."'");
											}else if($i==3){
												$updateurl=$this->db->query("update trx_gopay set url_cancel='".$url."' where trx_id='".$order_id."'");
											}
											$i++;
											
										}
										$urlcode="http://api-dev.omegasoft.co.id/v1/gopayview/qrcode/id/$order_id";
										$data =  array(
												//'kodenota' => $kodenota,
												'transaction_code' => $order_id,
												'url_qrcode' => $urlcode,
												'url_qrcode_raw'=>$urlqr,
												'transaction_status' => $response->transaction_status
												);
										$this->response([
											'status_code' => 200,
											'message' => "",
											'data' =>$data
										], REST_Controller::HTTP_OK);
										
										
									}else{
										$data = array();
										$this->response([
											'status_code' => 404,
											'message' => $response->status_message,
											'data' =>$data
										], REST_Controller::HTTP_OK);
									}
									
								}else{
									$data = array();
									$this->response([
										'status_code' => 404,
										'message' => $response->status_message,
										'data' =>$data
									], REST_Controller::HTTP_OK);
									//error
									
								}	
							}else{
								$data = array();
								$this->response([
									'status_code' => 404,
									'message' => $e->getMessage(),
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}
						}else{
							$gross_amount = $row->Total;
							$data = array();
							$this->response([
									'status_code' => 400,
									'message' => "kodenota not found",
									'data' =>$data
								], REST_Controller::HTTP_OK);
						}
						
						
						/*
						*/
					}else{
						$data = array();
						$this->response([
							'status_code' => 404,
							'message' => "Missing parameter kodenota or amount",
							'data' =>$data
						], REST_Controller::HTTP_OK);
					}
					
				}else{
					$data = array();
					$this->response([
						'status_code' => 404,
						'message' => 'Not Valid Authorization',
						'data' =>$data
					], REST_Controller::HTTP_NOT_FOUND);
				}
				
				 
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => 'Uid headers not registered',
					'data' =>$data
				], REST_Controller::HTTP_NOT_FOUND);
			}
			
			
		}else{
			 $data = array();
			 $this->response([
				'status_code' => 404,
				'message' => 'fill all Headers params',
				'data' =>$data
			], REST_Controller::HTTP_NOT_FOUND);
		}
		
	}
	
	public function transaction_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	public function gettransaction_post(){
		$auth=$this->input->get_request_header('Authorization');
		$uid=$this->input->get_request_header('Uid');
		
		if($auth!="" && $uid!=""){
			
			$setting=$this->check_auth($auth,$uid);
			if(!empty($setting)){
				$branch=$setting->branch;
				$apikey=$setting->apikey;
				
				if($apikey==$auth){
					
					$kodenota = $this->post('transaction_code');
					
					
					if($kodenota!=""){
						
						//$this->db->where('trx_id', $kodenota);
						$setting_trx = $this->db->query("select * from trx_gopay where trx_id='".$kodenota."'")->row();
						if($setting_trx->transaction_status!=''){
							
							$trx_status=$setting_trx->transaction_status;
							$trx_id=$setting_trx->trx_id;
							
							if($trx_status=='settlement'){
								$notlp="";
								$email="";
								$additional_information=$kodenota.";".$notlp.";".$email;
								$data =  array(
									
									'transaction_code' => $kodenota,
									'transaction_status' => $trx_status,
									'additional_information' =>$additional_information
									);
								$this->response([
									'status_code' => 200,
									'message' => "successs",
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}else{
								$notlp="";
								$email="";
								$additional_information=$trx_id.";".$notlp.";".$email;
								$data =  array(
									//'kodenota' => $kodenota,
									'transaction_code' => $trx_id,
									'transaction_status' => $trx_status,
									'additional_information' => $additional_information
									);
								$this->response([
									'status_code' => 404,
									'message' => "transaction not settlement",
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}
							
							
							
						}else{
							$data = array();
							$this->response([
								'status_code' => 404,
								'message' => "trx not found",
								'data' =>$data
							], REST_Controller::HTTP_OK);
						}
						
						
					}else{
						$data = array();
						$this->response([
							'status_code' => 404,
							'message' => "Missing parameter kodenota",
							'data' =>$data
						], REST_Controller::HTTP_OK);
					}
					
				}else{
					$data = array();
					$this->response([
						'status_code' => 404,
						'message' => 'Not Valid Authorization',
						'data' =>$data
					], REST_Controller::HTTP_NOT_FOUND);
				}
				
				 
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => 'Uid headers not registered',
					'data' =>$data
				], REST_Controller::HTTP_NOT_FOUND);
			}
			
			
		}else{
			 $data = array();
			 $this->response([
				'status_code' => 404,
				'message' => 'fill all Headers params',
				'data' =>$data
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	public function gettransaction_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	public function transactioncancel_post(){
		$auth=$this->input->get_request_header('Authorization');
		$uid=$this->input->get_request_header('Uid');
		
		if($auth!="" && $uid!=""){
			
			$setting=$this->check_auth($auth,$uid);
			if(!empty($setting)){
				$branch=$setting->branch;
				$apikey=$setting->apikey;
				
				if($apikey==$auth){
					
					$kodenota = $this->post('kodenota');
					
					if($kodenota!=""){
						
						$this->db->where('kodenota', $kodenota);
						$setting_trx = $this->db->get('trx_gopay')->row();
						if($setting_trx->transaction_status!=''){
							
							$trx_status=$setting_trx->transaction_status;
							$trx_id=$setting_trx->trx_id;
							$url_cancel=$setting_trx->url_cancel;
							
							$data="SB-Mid-server-kqxBpNGhbcHWWXHGXN-TpGNL:";
							$YourBasicData=base64_encode($data);
							
								
							$ch = curl_init();    
							curl_setopt($ch, CURLOPT_URL, $url_cancel); // set url
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
							//curl_setopt($ch, CURLOPT_POSTFIELDS,$jsondata);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
							//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); // set browser/user agent    
							//curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
							curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',"Authorization:Basic $YourBasicData"));
							
							$data = curl_exec($ch);	
							$response = json_decode($data, true);
							//$err_code= $response['err_code'];
							if($response['status_code']==200){
								$data =  array(
										'transaction_code' => $kodenota,
										'gopay_transaction_code' => $trx_id,
										'transaction_status' => $response['status_message']
										);
								$this->response([
									'status_code' => 200,
									'message' => "success",
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}else{
								$data = array();
								$this->response([
									'status_code' => 404,
									'message' => $response['status_message'],
									'data' =>$data
								], REST_Controller::HTTP_OK);
								
							}
							
							//array(10) { ["status_code"]=> string(3) "200" ["status_message"]=> string(32) "Success, transaction is canceled" ["transaction_id"]=> string(36) "3c5632d9-c441-43c2-84df-e65e9d35ce49" ["order_id"]=> string(13) "5cda40aabea49" ["gross_amount"]=> string(7) "1000.00" ["currency"]=> string(3) "IDR" ["payment_type"]=> string(5) "gopay" ["transaction_time"]=> string(19) "2019-05-14 11:14:37" ["transaction_status"]=> string(6) "cancel" ["fraud_status"]=> string(6) "accept" }
							
							
							//var_dump($response);
						
							
						}else{
							$data = array();
							$this->response([
								'status_code' => 404,
								'message' => "trx not found",
								'data' =>$data
							], REST_Controller::HTTP_OK);
						}
						
						
					}else{
						$data = array();
						$this->response([
							'status_code' => 404,
							'message' => "Missing parameter kodenota",
							'data' =>$data
						], REST_Controller::HTTP_OK);
					}
					
				}else{
					$data = array();
					$this->response([
						'status_code' => 404,
						'message' => 'Not Valid Authorization',
						'data' =>$data
					], REST_Controller::HTTP_NOT_FOUND);
				}
				
				 
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => 'Uid headers not registered',
					'data' =>$data
				], REST_Controller::HTTP_NOT_FOUND);
			}
			
		}else{
			 $data = array();
			 $this->response([
				'status_code' => 404,
				'message' => 'fill all Headers params',
				'data' =>$data
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	public function transactioncancel_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	public function refund_post(){
		$auth=$this->input->get_request_header('Authorization');
		$uid=$this->input->get_request_header('Uid');
		
		if($auth!="" && $uid!=""){
			
			$setting=$this->check_auth($auth,$uid);
			if(!empty($setting)){
				$branch=$setting->branch;
				$apikey=$setting->apikey;
				
				if($apikey==$auth){
					
					$kodenota = $this->post('transaction_code');
					$reason = $this->post('reason');
					$amount = $this->post('amount');
					//$gross_amount =floatval($amount);
					if($kodenota!="" && $reason!="" && $amount){
						
		
						//$this->db->where('kodenota', $kodenota);
						$setting_trx = $this->db->query("select * from trx_gopay where trx_id='".$kodenota."'")->row();
						if($setting_trx->transaction_status!=''){
							if($setting_trx->transaction_status=='settlement'){
								$trx_status=$setting_trx->transaction_status;
								$trx_id=$kodenota;
								//$amount =floatval($amount);
								///$url_cancel=$setting_trx->url_cancel;
								$refund_key=uniqid();
								$jsondata = array(
									'refund_key'  => $refund_key, 
									'amount'   =>$amount,
									
									'reason' => $reason
								);
								/*$this->response([
										'status_code' => 200,
										'message' => "success",
										'data' =>$jsondata
									], REST_Controller::HTTP_OK);*/
								$data="SB-Mid-server-kqxBpNGhbcHWWXHGXN-TpGNL";
								$YourBasicData=base64_encode($data);
								//BASE_URL/v2/{order_id OR transaction_id}/refund/online/direct refund
								//$urlrefund="https://api.sandbox.veritrans.co.id/v2/$trx_id/refund/online/direct";
								$urlrefund="https://api.sandbox.veritrans.co.id/v2/$trx_id/refund";
								
								$ch = curl_init();    
								curl_setopt($ch, CURLOPT_URL, $urlrefund); // set url
								curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
								curl_setopt($ch, CURLOPT_POSTFIELDS,$jsondata);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
								//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); // set browser/user agent    
								//curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',"Authorization:Basic $YourBasicData"));
								
								$data = curl_exec($ch);	
								$response = json_decode($data, true);
								//$err_code= $response['err_code'];
								if($response['status_code']==200){
									$data =  array(
											'transaction_code' => $kodenota,
											'gopay_transaction_code' => $trx_id,
											'transaction_status' => $response['status_message'],
											'transaction_time' => $response['transaction_time'],
											'refund_chargeback_id' => $response['refund_chargeback_id'],
											'refund_amount' => $response['refund_amount'],
											'settlement_time' => $response['settlement_time'],
											'refund_key' => $response['refund_key']
											);
									$this->response([
										'status_code' => 200,
										'message' => "success",
										'data' =>$data
									], REST_Controller::HTTP_OK);
								}else{
									$data = array();
									$this->response([
										'status_code' => 404,
										'message' => $response['status_message'],
										'kodenota'   =>$trx_id,
										'data' =>$response
									], REST_Controller::HTTP_OK);
									
								}
							}else{
								$data = array();
								$this->response([
									'status_code' => 404,
									'message' => "trx ".$setting_trx->transaction_status,
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}
						}else{
							$data = array();
							$this->response([
								'status_code' => 404,
								'message' => "trx not found",
								'data' =>$data
							], REST_Controller::HTTP_OK);
						}
						
						
					}else{
						$data = array();
						$this->response([
							'status_code' => 404,
							'message' => "Missing parameter kodenota or reason",
							'data' =>$data
						], REST_Controller::HTTP_OK);
					}
					
				}else{
					$data = array();
					$this->response([
						'status_code' => 404,
						'message' => 'Not Valid Authorization',
						'data' =>$data
					], REST_Controller::HTTP_NOT_FOUND);
				}
				
				 
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => 'Uid headers not registered',
					'data' =>$data
				], REST_Controller::HTTP_NOT_FOUND);
			}
			
		}else{
			 $data = array();
			 $this->response([
				'status_code' => 404,
				'message' => 'fill all Headers params',
				'data' =>$data
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	
	public function expire_post(){
		$auth=$this->input->get_request_header('Authorization');
		$uid=$this->input->get_request_header('Uid');
		
		if($auth!="" && $uid!=""){
			
			$setting=$this->check_auth($auth,$uid);
			if(!empty($setting)){
				$branch=$setting->branch;
				$apikey=$setting->apikey;
				
				if($apikey==$auth){
					
					$kodenota = $this->post('kodenota');
					
					if($kodenota!=""){
						
						$this->db->where('kodenota', $kodenota);
						$setting_trx = $this->db->get('trx_gopay')->row();
						if($setting_trx->transaction_status!=''){
							
							$trx_status=$setting_trx->transaction_status;
							$trx_id=$setting_trx->trx_id;
							
							
							$data="SB-Mid-server-kqxBpNGhbcHWWXHGXN-TpGNL:";
							$YourBasicData=base64_encode($data);
							//BASE_URL/v2/{order_id OR transaction_id}/refund/online/direct refund
							//$urlrefund="https://api.sandbox.veritrans.co.id/v2/$trx_id/refund/online/direct";
							$urlexpire="https://api.sandbox.veritrans.co.id/v2/$trx_id/expire";
							
							$ch = curl_init();    
							curl_setopt($ch, CURLOPT_URL, $urlexpire); // set url
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
							//curl_setopt($ch, CURLOPT_POSTFIELDS,$jsondata);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
							//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); // set browser/user agent    
							//curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
							curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',"Authorization:Basic $YourBasicData"));
							
							$data = curl_exec($ch);	
							$response = json_decode($data, true);
							//$err_code= $response['err_code'];
							if($response['status_code']==200){
								$data =  array(
										'transaction_code' => $kodenota,
										'gopay_transaction_code' => $trx_id,
										'transaction_status' => $response['status_message']
										);
								$this->response([
									'status_code' => 200,
									'message' => "success",
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}else{
								$data = array();
								$this->response([
									'status_code' => 404,
									'message' => $response['status_message'],
									'data' =>$response
								], REST_Controller::HTTP_OK);
								
							}
							
							//array(10) { ["status_code"]=> string(3) "200" ["status_message"]=> string(32) "Success, transaction is canceled" ["transaction_id"]=> string(36) "3c5632d9-c441-43c2-84df-e65e9d35ce49" ["order_id"]=> string(13) "5cda40aabea49" ["gross_amount"]=> string(7) "1000.00" ["currency"]=> string(3) "IDR" ["payment_type"]=> string(5) "gopay" ["transaction_time"]=> string(19) "2019-05-14 11:14:37" ["transaction_status"]=> string(6) "cancel" ["fraud_status"]=> string(6) "accept" }
							
							
							//echo $urlrefund;
							//var_dump($response);
						
							
						}else{
							$data = array();
							$this->response([
								'status_code' => 404,
								'message' => "trx not found",
								'data' =>$data
							], REST_Controller::HTTP_OK);
						}
						
						
					}else{
						$data = array();
						$this->response([
							'status_code' => 404,
							'message' => "Missing parameter kodenota",
							'data' =>$data
						], REST_Controller::HTTP_OK);
					}
					
				}else{
					$data = array();
					$this->response([
						'status_code' => 404,
						'message' => 'Not Valid Authorization',
						'data' =>$data
					], REST_Controller::HTTP_NOT_FOUND);
				}
				
				 
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => 'Uid headers not registered',
					'data' =>$data
				], REST_Controller::HTTP_NOT_FOUND);
			}
			
		}else{
			 $data = array();
			 $this->response([
				'status_code' => 404,
				'message' => 'fill all Headers params',
				'data' =>$data
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	
	public function expire_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	
	public function qrcode_post(){
		$auth=$this->input->get_request_header('Authorization');
		$uid=$this->input->get_request_header('Uid');
		
		if($auth!="" && $uid!=""){
			
			$setting=$this->check_auth($auth,$uid);
			if(!empty($setting)){
				$branch=$setting->branch;
				$apikey=$setting->apikey;
				
				if($apikey==$auth){
					
					$kodenota = $this->post('kodenota');
					
					if($kodenota!=""){
						
						$this->db->where('kodenota', $kodenota);
						$setting_trx = $this->db->get('trx_gopay')->row();
						if($setting_trx->transaction_status!=''){
							
							$trx_status=$setting_trx->transaction_status;
							$trx_id=$setting_trx->trx_id;
							$url_qr=$setting_trx->url_qr;
							
							$data['url_qr']=$url_qr;
							$this->load->view('qrcode',$data);
							
						}else{
							$data = array();
							$this->response([
								'status_code' => 404,
								'message' => "trx not found",
								'data' =>$data
							], REST_Controller::HTTP_OK);
						}
						
						
					}else{
						$data = array();
						$this->response([
							'status_code' => 404,
							'message' => "Missing parameter kodenota",
							'data' =>$data
						], REST_Controller::HTTP_OK);
					}
					
				}else{
					$data = array();
					$this->response([
						'status_code' => 404,
						'message' => 'Not Valid Authorization',
						'data' =>$data
					], REST_Controller::HTTP_NOT_FOUND);
				}
				
				 
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => 'Uid headers not registered',
					'data' =>$data
				], REST_Controller::HTTP_NOT_FOUND);
			}
			
		}else{
			 $data = array();
			 $this->response([
				'status_code' => 404,
				'message' => 'fill all Headers params',
				'data' =>$data
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	
	public function qrcode_get(){

        $id = $this->get('id');	
		$kodenota=$id;
		//echo $kodenota;
		if($kodenota!=""){
			
			//$this->db->where('trx_id', $kodenota);
			//$setting_trx = $this->db->get('trx_gopay')->row();
			$setting_trx = $this->db->query("select * from trx_gopay where trx_id='".$kodenota."'")->row();
			if($setting_trx->transaction_status!=''){
				
				$trx_status=$setting_trx->transaction_status;
				$trx_id=$setting_trx->trx_id;
				$url_qr=$setting_trx->url_qr;
				echo $url_qr;
				//$data['url_qr']=$url_qr;
				//$this->load->view('qrcode',$data);
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => "trx not found",
					'data' =>$data
				], REST_Controller::HTTP_OK);
			}
			
			
		}else{
			$data = array();
			$this->response([
				'status_code' => 404,
				'message' => "Missing parameter kodenota",
				'data' =>$data
			], REST_Controller::HTTP_OK);
		}
	}
	
	
	public function getqrcode_post(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	
	public function getqrcode_get(){
		$kodenota = $this->get('kodenota');	
		if($kodenota!=""){
			
			$this->db->where('kodenota', $kodenota);
			$setting_trx = $this->db->get('trx_gopay')->row();
			if($setting_trx->transaction_status!=''){
				
				$trx_status=$setting_trx->transaction_status;
				$trx_id=$setting_trx->trx_id;
				$url_qr=$setting_trx->url_qr;
				
				$data['url_qr']=$url_qr;
				$this->load->view('qrcode',$data);
				
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => "trx not found",
					'data' =>$data
				], REST_Controller::HTTP_OK);
			}
			
			
		}else{
			$data = array();
			$this->response([
				'status_code' => 404,
				'message' => "Missing parameter kodenota",
				'data' =>$data
			], REST_Controller::HTTP_OK);
		}
	}
	
	public function getgopaytrans_post(){
		
		$auth=$this->input->get_request_header('Authorization');
		$uid=$this->input->get_request_header('Uid');
		
		if($auth!="" && $uid!=""){
			
			$setting=$this->check_auth($auth,$uid);
			if(!empty($setting)){
				$branch=$setting->branch;
				$apikey=$setting->apikey;
				
				if($apikey==$auth){
					
					$kodenota = $this->post('transaction_code');
					
					
					if($kodenota!=""){
						
						//$this->db->where('trx_id', $kodenota);
						$setting_trx = $this->db->query("select * from trx_gopay where trx_id='".$kodenota."'")->row();
						if($setting_trx->transaction_status!=''){
							
							$trx_status=$setting_trx->transaction_status;
							$trx_id=$setting_trx->trx_id;
							
							if($trx_status=='settlement'){
								$notlp="";
								$email="";
								$additional_information=$trx_id.";".$notlp.";".$email;
								$data =  array(
									
									'transaction_code' => $trx_id,
									'transaction_status' => $trx_status,
									'additional_information' =>$additional_information
									);
								$this->response([
									'status_code' => 200,
									'message' => "success",
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}else{
								$notlp="";
								$email="";
								$additional_information=$trx_id.";".$notlp.";".$email;
								$data =  array(
									'kodenota' => $kodenota,
									'transaction_code' => $trx_id,
									'transaction_status' => $trx_status,
									'additional_information' => $additional_information
									);
								$this->response([
									'status_code' => 404,
									'message' => "transaction not settlement",
									'data' =>$data
								], REST_Controller::HTTP_OK);
							}
							
							
							
						}else{
							$data = array();
							$this->response([
								'status_code' => 404,
								'message' => "trx not found",
								'data' =>$data
							], REST_Controller::HTTP_OK);
						}
						
						
					}else{
						$data = array();
						$this->response([
							'status_code' => 404,
							'message' => "Missing parameter kodenota",
							'data' =>$data
						], REST_Controller::HTTP_OK);
					}
					
				}else{
					$data = array();
					$this->response([
						'status_code' => 404,
						'message' => 'Not Valid Authorization',
						'data' =>$data
					], REST_Controller::HTTP_NOT_FOUND);
				}
				
				 
			}else{
				$data = array();
				$this->response([
					'status_code' => 404,
					'message' => 'Uid headers not registered',
					'data' =>$data
				], REST_Controller::HTTP_NOT_FOUND);
			}
			
			
		}else{
			 $data = array();
			 $this->response([
				'status_code' => 404,
				'message' => 'fill all Headers params',
				'data' =>$data
			], REST_Controller::HTTP_NOT_FOUND);
		}
		
	}
	
	
	public function getgopaytrans_get(){
		
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
		
	}
	
	public function handling_post(){
		
		/*
			{
			  "status_code": "200",
			  "status_message": "midtrans payment notification",
			  "transaction_id": "841c7da8-530b-435a-9f67-c9d632d15537",
			  "order_id": "1000176721005355",
			  "gross_amount": "698879.00",
			  "payment_type": "credit_card",
			  "transaction_time": "2016-07-04 00:53:55",
			  "transaction_status": "refund",
			  "permata_va_number": "8778004890127981",
			  "signature_key": "f1066b06ec8a4b7d6ffb941fd9772f6df304618e15e02cf17c2914cec12793e19c71653042f7f617b027eae6ecb6759529c67eca9af55264b736408d8b4df2b9"
			}
		
		*/
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
			//$notif = $this->veritrans->status($result->order_id);
			$transaction = $result->transaction_status;
			$type = $result->payment_type;
			$order_id = $result->order_id;
			$fraud = $result->fraud_status;
				if ($transaction == 'capture') {
				  if ($type == 'credit_card'){
					if($fraud == 'challenge'){
					  echo "Transaction order_id: " . $order_id ." is challenged by FDS";
					  }else {
					  
					  }
					}
				}else if ($transaction == 'settlement'){
					$getid=$this->db->query("select trx_id,transaction_name from trx_gopay where trx_id='".$order_id."' and (transaction_status='batal' or transaction_status='close')  order by createdate desc  limit 1 ");
					if($getid->num_rows()){
					
						$data = array();
						 $this->response([
							'status_code' => 407,
							'message' =>"gagal transaksi sudah dibatalkan",
							'data' =>$data
						], REST_Controller::HTTP_OK);
		
					
					}else{
					
						$sql =$this->db->query("update trx_gopay set transaction_status='settlement',tglentry=now() where trx_id='".$order_id."'");
					
						
						
						$querymysql = $this->db->query("select trx_id,transaction_name from trx_gopay where trx_id='".$order_id."'  order by createdate desc  limit 1 ");
						$rowmysql=$querymysql->row();
						$trx_id="";
						$transaction_name="";
						if($rowmysql->trx_id!=""){
							$trx_id=$rowmysql->trx_id;
						}
						if($rowmysql->transaction_name!=""){
							$transaction_name=$rowmysql->transaction_name;
						}
						$this->write_log("transaction_name","200",$transaction_name);
						
						
						if($transaction_name=="iot-gopay"){
							$this->write_log("pengecekan_iot","200",$transaction_name);
							$msg = str_replace("-","/",$order_id);
							$cb=explode('/',$msg);
							$cb1=$cb[0];
							$cb2=$cb[1];
							$cabang=$cb1."/".$cb2;
							$branch=$cb1;
							
							$query = $this->opc->query("select Total,Cust,Operator from MasterSo where kodenota='".$msg."'");
							$row=$query->row();
							if($row->Total!=""){
								$gross_amount = $row->Total;
								$cust = $row->Cust;
								$staff = $row->Operator;
								$gross_amount =floatval($gross_amount);
							}
							
							//iot save pos
							$this->write_log("querypos","200","sebelum querypos dijalankan");
							$querypos="DECLARE @tgl datetime,@kodenota varchar(30),@gabung varchar(100),@kodedepan VARCHAR(30),@cabang VARCHAR(15), @staff VARCHAR(15), @kode_tanggal VARCHAR(5), @inisial VARCHAR(5), @lastnomornota INT,  @lastkodenota VARCHAR(50) 
									, @PPNPersenManual NUMERIC(18,4), @PerkiraanLinkAja VARCHAR(50), @TotalBayarSO NUMERIC(18,4)
									set @tgl = [OmegaCloud].[dbo].GetTransDate('".$cabang."') 
									SET @cabang = '".$cabang."' 
									SET @staff = '".$staff."' 
									SET @inisial = 'VN' 
									 SET @staff = replace(right(@staff, 5), '/', '') 
									SET @kode_tanggal = right('00' + cast(datepart(dd, @tgl) as varchar(10)), 2) 
									SET @kode_tanggal = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@kode_tanggal, '0', 'X'), '1', 'A'), '2', 'B'), '3', 'C'), '4', 'D'), '5', 'E'), '6', 'F'), '7', 'G'), '8', 'H'), '9', 'I') 
									SET @kodedepan = @cabang + '/' + @inisial + '/' + @staff + '/' + right('00' + cast(datepart(yy, @tgl) as varchar(10)), 2) + right('00' + cast(datepart(mm, @tgl) as varchar(10)), 2) + '/' + @kode_tanggal 
									select @lastkodenota = max(kodenota) from [OmegaCloud].[dbo].MasterJual where kodenota like @kodedepan +'%' 
									set @lastnomornota = isnull(cast(right(@lastkodenota, 4) as int), 0) 
									set @lastnomornota = @lastnomornota + 1 
									set @kodenota = @kodedepan + right('0000000000' + cast(@lastnomornota as varchar), 4) 
									set @gabung= '".$msg."' +';T000001'
									SET @PPNPersenManual=ISNULL((SELECT TOP 1 PPNPersenManual FROM [OmegaCloud].[dbo].MasterSO WHERE KodeNota='".$msg."'),0)
									SET @TotalBayarSO=ISNULL((SELECT TOP 1 TotalBayar FROM [OmegaCloud].[dbo].MasterSO WHERE KodeNota='".$msg."'),0)
									SET @PerkiraanLinkAja=(SELECT TOP 1 NoPerkiraan FROM [OmegaCloud].[dbo].AkunEDC WHERE PayName='LinkAja' AND Cabang=@cabang)
									if not exists(select top 1 kodenota from [OmegaCloud].[dbo].masterjual where kodenota like '".$cabang."'+'%' and refno ='".$msg."' and tgl = @tgl) 
									begin 
										declare @kodenotapp varchar(30), @kodenotapk varchar(30), @kodenotapu varchar(30), @kodenotapu2 varchar(30), @kodenotapd varchar(30), @kodenotapt varchar(30), @kodenotapv varchar(30), @kodenotapkl varchar(30), @kodeCust varchar(50), @norefdraftso varchar(30), @keterangan1 varchar(100), @tglKreditSettlement DATETIME  
										SET @tglKreditSettlement = @tgl 
										SET @kodenotapp = left(@kodenota, 9) + 'VNO' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapk = left(@kodenota, 9) + 'VNR' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapu = left(@kodenota, 9) + 'VNZ' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapu2 = left(@kodenota, 9) + 'VNN' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapd = left(@kodenota, 9) + 'VNE' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapt = left(@kodenota, 9) + 'VNU' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapv = left(@kodenota, 9) + 'VNW' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapkl = left(@kodenota, 9) + 'VNM' + right(@kodenota, len(@kodenota) - 11)
										if (select top 1 OtomatisGenPOS from Kategori where Cabang='".$cabang."')=1
										begin
										  exec [OmegaCloud].[dbo].GenPOS @tgl , @gabung,'".$staff."', @kodenota, 1
										  exec [OmegaCloud].[dbo].GenPOS @tgl , @gabung,'".$staff."', @kodenota, 2
										end
										EXEC [OmegaCloud].[dbo].SavePOS3 '".$msg."', @kodenota, @kodenotapp, @kodenotapk, @kodenotapu,
										@kodenotapu2, @kodenotapd, @kodenotapt, @kodenotapv, @kodenotapkl, @norefdraftso,
										@PPNPersenManual,'', 0, 0, 0,  
										0, 0, 0 , 
										@TotalBayarSO, 0 , 0 ,  
										0, 0 , 
										 '".$cust."' ,'".$staff."', @tgl, 'IDR' , '' ,  
										'', 0,'', @keterangan1 , '<CLOSE>  BY : $staff ' , 0 , 
										0 , 3 ,'', '','',  
										 '', @tglKreditSettlement,  
										 @PerkiraanLinkAja , 'LinkAja' , ';' , @tgl , 
										 0 , 0 , 1 ,  
										 0 , '', '' , '".$cabang."','', 
										 0,'','','',0,0,1 
									 end";
							$this->write_log("querypos","200",$querypos);
							$ch = curl_init();
						
							curl_setopt($ch, CURLOPT_URL,"http://dev-api-integration.thumbstalk.co:1337/api/webservice");
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS,
										"db=oc&action=insert&token=9eeb7e2b9e5c48308256224e6d97a02f&query=$querypos");
							curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
							// receive server response ...
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							$server_output = curl_exec ($ch);
							curl_close ($ch);
							$result = json_decode($server_output);
							$pesan = $result->pesan;
							$this->write_log("kembalian_querypos_1","200",$pesan);
							if (strpos($pesan, 'Error') !== false) {
								$this->write_log("querypos_1","200","sebelum querypos 1 dijalankan");
								$querypos="DECLARE @tgl datetime,@kodenota varchar(30),@gabung varchar(100),@kodedepan VARCHAR(30),@cabang VARCHAR(15), @staff VARCHAR(15), @kode_tanggal VARCHAR(5), @inisial VARCHAR(5), @lastnomornota INT,  @lastkodenota VARCHAR(50) 
									, @PPNPersenManual NUMERIC(18,4), @PerkiraanLinkAja VARCHAR(50), @TotalBayarSO NUMERIC(18,4)
									set @tgl = [OmegaCloud].[dbo].GetTransDate('".$cabang."') 
									SET @cabang = '".$cabang."' 
									SET @staff = '".$staff."' 
									SET @inisial = 'VN' 
									 SET @staff = replace(right(@staff, 5), '/', '') 
									SET @kode_tanggal = right('00' + cast(datepart(dd, @tgl) as varchar(10)), 2) 
									SET @kode_tanggal = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@kode_tanggal, '0', 'X'), '1', 'A'), '2', 'B'), '3', 'C'), '4', 'D'), '5', 'E'), '6', 'F'), '7', 'G'), '8', 'H'), '9', 'I') 
									SET @kodedepan = @cabang + '/' + @inisial + '/' + @staff + '/' + right('00' + cast(datepart(yy, @tgl) as varchar(10)), 2) + right('00' + cast(datepart(mm, @tgl) as varchar(10)), 2) + '/' + @kode_tanggal 
									select @lastkodenota = max(kodenota) from [OmegaCloud].[dbo].MasterJual where kodenota like @kodedepan +'%' 
									set @lastnomornota = isnull(cast(right(@lastkodenota, 4) as int), 0) 
									set @lastnomornota = @lastnomornota + 1 
									set @kodenota = @kodedepan + right('0000000000' + cast(@lastnomornota as varchar), 4) 
									set @gabung= '".$msg."' +';T000001'
									SET @PPNPersenManual=ISNULL((SELECT TOP 1 PPNPersenManual FROM [OmegaCloud].[dbo].MasterSO WHERE KodeNota='".$msg."'),0)
									SET @TotalBayarSO=ISNULL((SELECT TOP 1 TotalBayar FROM [OmegaCloud].[dbo].MasterSO WHERE KodeNota='".$msg."'),0)
									SET @PerkiraanLinkAja=(SELECT TOP 1 NoPerkiraan FROM [OmegaCloud].[dbo].AkunEDC WHERE PayName='LinkAja' AND Cabang=@cabang)
									if not exists(select top 1 kodenota from [OmegaCloud].[dbo].masterjual where kodenota like '".$cabang."'+'%' and refno ='".$msg."' and tgl = @tgl) 
									begin 
										declare @kodenotapp varchar(30), @kodenotapk varchar(30), @kodenotapu varchar(30), @kodenotapu2 varchar(30), @kodenotapd varchar(30), @kodenotapt varchar(30), @kodenotapv varchar(30), @kodenotapkl varchar(30), @kodeCust varchar(50), @norefdraftso varchar(30), @keterangan1 varchar(100), @tglKreditSettlement DATETIME  
										SET @tglKreditSettlement = @tgl 
										SET @kodenotapp = left(@kodenota, 9) + 'VNO' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapk = left(@kodenota, 9) + 'VNR' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapu = left(@kodenota, 9) + 'VNZ' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapu2 = left(@kodenota, 9) + 'VNN' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapd = left(@kodenota, 9) + 'VNE' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapt = left(@kodenota, 9) + 'VNU' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapv = left(@kodenota, 9) + 'VNW' + right(@kodenota, len(@kodenota) - 11) SET @kodenotapkl = left(@kodenota, 9) + 'VNM' + right(@kodenota, len(@kodenota) - 11)
										if (select top 1 OtomatisGenPOS from Kategori where Cabang='".$cabang."')=1
										begin
										  exec [OmegaCloud].[dbo].GenPOS @tgl , @gabung,'".$staff."', @kodenota, 1
										  exec [OmegaCloud].[dbo].GenPOS @tgl , @gabung,'".$staff."', @kodenota, 2
										end
										EXEC [OmegaCloud].[dbo].SavePOS3 '".$msg."', @kodenota, @kodenotapp, @kodenotapk, @kodenotapu,
										@kodenotapu2, @kodenotapd, @kodenotapt, @kodenotapv, @kodenotapkl, @norefdraftso,
										@PPNPersenManual,'', 0, 0, 0,  
										0, 0, 0 , 
										@TotalBayarSO, 0 , 0 ,  
										0, 0 , 
										 '".$cust."' ,'".$staff."', @tgl, 'IDR' , '' ,  
										'', 0,'', @keterangan1 , '<CLOSE>  BY : $staff ' , 0 , 
										0 , 3 ,'', '','',  
										 '', @tglKreditSettlement,  
										 @PerkiraanLinkAja , 'LinkAja' , ';' , @tgl , 
										 0 , 0 , 1 ,  
										 0 , '', '' , '".$cabang."','', 
										 0,'','','',0,0,1 
									 end";
									 $this->write_log("querypos_1","200",$querypos);
								//echo $querypos;
								$ch = curl_init();
								
								curl_setopt($ch, CURLOPT_URL,"http://dev-api-integration.thumbstalk.co:1337/api/webservice");
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_POSTFIELDS,
											"db=oc&action=insert&token=9eeb7e2b9e5c48308256224e6d97a02f&query=$querypos");
								curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));


								// receive server response ...
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

								$server_output = curl_exec ($ch);

								curl_close ($ch);

								$result = json_decode($server_output);
								 $pesan = $result->pesan;
							
								if (strpos($pesan, 'Error') !== false) {
								
								}else{
									 $data = array();
									 $this->response([
										'status_code' => 201,
										'message' =>"succes update trx settlement",
										'data' =>$data
									], REST_Controller::HTTP_OK);
								}
								
							}else{
								$data = array();
								$this->response([
									'status_code' => 201,
									'message' =>"succes update trx settlement",
									'data' =>$data
								], REST_Controller::HTTP_OK);
								
							}
							
								
						}else{
							$data = array();
							$this->response([
								'status_code' => 201,
								'message' =>"succes update trx settlement",
								'data' =>$data
							], REST_Controller::HTTP_OK);
							
						}
					
					}
					
				}else if($transaction == 'pending'){
					$sql =$this->db->query("update trx_gopay set transaction_status='pending',tglentry=now() where trx_id='".$order_id."'");
					$this->response([
						'status_code' => 201,
						'message' =>"succes update trx pending",
						'data' =>$data
					], REST_Controller::HTTP_OK);
				}else if ($transaction == 'deny') {
					$sql =$this->db->query("update trx_gopay set transaction_status='deny',tglentry=now() where trx_id='".$order_id."'");
					$this->response([
						'status_code' => 201,
						'message' =>"succes update trx deny",
						'data' =>$data
					], REST_Controller::HTTP_OK);
				}else if ($transaction == 'failure') {
					$sql =$this->db->query("update trx_gopay set transaction_status='failure',tglentry=now() where trx_id='".$order_id."'");
					$this->response([
						'status_code' => 201,
						'message' =>"succes update trx deny",
						'data' =>$data
					], REST_Controller::HTTP_OK);
				}else if ($transaction == 'cancel') {
					$sql =$this->db->query("update trx_gopay set transaction_status='cancel',tglentry=now() where trx_id='".$order_id."'");
					$this->response([
						'status_code' => 201,
						'message' =>"succes update trx deny",
						'data' =>$data
					], REST_Controller::HTTP_OK);
				}else if ($transaction == 'refund') {
					$sql =$this->db->query("update trx_gopay set transaction_status='refund',tglentry=now() where trx_id='".$order_id."'");
					$this->response([
						'status_code' => 201,
						'message' =>"succes update trx refund",
						'data' =>$data
					], REST_Controller::HTTP_OK);
				}else if ($transaction == 'expire') {
					$sql =$this->db->query("update trx_gopay set transaction_status='expire',tglentry=now() where trx_id='".$order_id."'");
					$this->response([
						'status_code' => 201,
						'message' =>"succes update trx expire",
						'data' =>$data
					], REST_Controller::HTTP_OK);
				}
		}
		//error_log(print_r($result,TRUE));
		//var_dump($result);

	}
	
	public function handling_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	
	public function finish_post(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	public function finish_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	public function unfinish_post(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	public function unfinish_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	
	public function error_post(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	
	public function error_get(){
		$data = array();
		$this->response([
			'status_code' => 404,
			'message' =>"not suppport",
			'data' =>$data
		], REST_Controller::HTTP_NOT_FOUND);
	}
	

}
