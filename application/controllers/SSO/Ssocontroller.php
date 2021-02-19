<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Ssocontroller extends CI_Controller
{

    function __construct()
    {
      parent::__construct();
      $this->load->model('Ssomodel');
      $this->load->model('Embismodel');
      $this->load->database();
    }

    function test(){
        // $data['test'] =  $this->Ssomodel->fetch_subsys();
        // echo json_encode($data['test']);

    }

    function emailtoken($email)
    {
            $this->sendtoken($email);

            $this->load->library('session');
            $this->load->view('includes/login/header');
            $this->load->view('sso/emailtoken');
            $this->load->view('includes/login/footer');
        
    }

    function sendtoken($email){
        // echo $email;

        // get email address
        $where = array('sec.userid' => $email );
		$queryselect = $this->Embismodel->selectdata('acc_credentials AS sec','sec.email',$where);

        $response = json_encode($queryselect);

        $res = json_decode($response, true);
            $resemail = ($res[0]['email']);

        //  echo $resemail;


        // get otp token
        $str_result = '0123456789'; 
		$randtoken = substr(str_shuffle($str_result), 0, 6); 

        $data = array(
            'otp' => $randtoken
        );

        $this->db->where('iis_id', $email);
        $this->db->update('sso_tb', $data);


        // ------------------- SEND EMAIL ----------------------
        $this->load->library('email');

        $this->load->config('email');
        $this->load->library('email');
        $this->email->set_crlf( "\r\n" );
        $from = $this->config->item('smtp_user');
        $to 	 = $resemail;

        $subject = 'IIS - One Time Password (OTP) Confirmation';
        $message  = "***THIS IS AUTOMATICALLY GENERATED EMAIL, PLEASE DO NOT REPLY***<br><br>";
        $message .= "SINGLE SIGN ON (SSO) Confirmation.<br><br>";
        $message .= "<h1>Your One Time Password is: ".$randtoken."</h1><br><br>";
        $message .= "If you have any concerns and issues, please don't hesitate to contuct us <u><i>support@emb.gov.ph</i></u>.<br><br>";
        $message .= "Thank you!";
       

        $this->email->set_newline("\r\n");
        $this->email->set_mailtype('html');
        // $this->email->from($from);
        $this->email->from('r7support@emb.gov.ph', 'EMB - IIS');
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($message);



        if ($this->email->send()) {
            $msg['msg']='sent to '.$resemail;
        }else {
            $msg['msg'] = $this->email->print_debugger();
        }
       
        // echo json_encode($msg);
       
    }

    function emailtokenverify(){
        $txt1 = $this->input->post('input1');
        $txt2 = $this->input->post('input2');
        $txt3 = $this->input->post('input3');
        $txt4 = $this->input->post('input4');
        $txt5 = $this->input->post('input5');
        $txt6 = $this->input->post('input6');

        $input_token = $txt1.$txt2.$txt3.$txt4.$txt5.$txt6;
//token from email 

        
        $query  = $this->db->get_where('sso_tb', array('iis_id' => 1));
        $row = $query->row();

        if($input_token ==  $row->otp){
         
            $data['getsub'] =  $this->Ssomodel->fetch_subsys();
            $this->load->library('session');
            $this->load->view('includes/login/header');
            $this->load->view('sso/selectsys', $data);
            $this->load->view('includes/login/footer');
           
        }else{
            $this->session->set_flashdata('flashmsg', 'Invalid Code!');
            $url = $_SERVER['HTTP_REFERER'];
            redirect($url);
   
        }
    }

    function enrollment()
    {

        $this->load->library('session');
        $this->load->view('includes/common/header');
        $this->load->view('includes/common/sidebar');
        $this->load->view('includes/common/nav');
        $this->load->view('includes/common/footer');
        $this->load->view('sso/enrollment', $data);
    }

    function enroll()
    {

        $result = $this->input->post('selSubsystem');
        $result_explode = explode('|', $result);
        
        $subsys = $result_explode[0];
        $subsyslink = $result_explode[1];
        $subsysimg = $result_explode[2];

        $nickname = $this->input->post('txtnickname');
        $uname = $this->input->post('txtusernames');
        $password = $this->input->post('txtpasswords');

        if($subsys == 'PCB'){
            // get api
            $data =  file_get_contents("http://pcb.emb.gov.ph/api/table/userTB/$uname/$password");
            $var = json_decode($data);

            $iisid = $this->session->userdata('userid');

            if($var->status == 1 && $var->message == 'Valid'){
                $data = array(
                    'iis_id' =>  $iisid,
                    'subsys_id' => $subsys,
                    'subsys_link' => $subsyslink,
                    'subsys_img' => $subsysimg,
                    'nickname' => $nickname,
                    'username' => $uname,
                    'password' => $password
                );
               $res = $this->Ssomodel->enroll($data);

               $this->session->set_flashdata('flashmsg', 'Account successfully added!');
            }else{

                $this->session->set_flashdata('flashmsg', $var->message);
            }
        }else if($subsys == 'HWMS'){
            // get api

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://hwms.emb.gov.ph/api/user/$uname/$password/");
            //curl_setopt($ch, CURLOPT_URL, 'https://hwms.emb.gov.ph/api/user/support@emb.gov.ph/@l03e1t3/');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 45);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Content-Type: application/json',
                sprintf('Authorization: Bearer %s', 'sh4PgSyLRvBUax1wznv6tpICeC101Dj24btQuWRGj5ck6RDpaP3WypLpiSlL')
             ));
           
           
                $response = curl_exec($ch);  
               
            curl_close($ch);
  
            $res = json_decode($response, true);
            $status = ($res[0]['status']);
            $message = ($res[0]['message']);

            // echo $response;
            // echo $status;
            // echo $message;

            $iisid = $this->session->userdata('userid');

            if($status == 1 && $message == 'Valid'){
                $data = array(
                    'iis_id' =>  $iisid,
                    'subsys_id' => $subsys,
                    'subsys_link' => $subsyslink,
                    'subsys_img' => $subsysimg,
                    'nickname' => $nickname,
                    'username' => $uname,
                    'password' => $password
                );
                $res = $this->Ssomodel->enroll($data);

                $this->session->set_flashdata('flashmsg', 'Account successfully added!');
            }else{

                $this->session->set_flashdata('flashmsg', $message);
            }
        }else{
            $this->session->set_flashdata('flashmsg', 'System Unavailable!');
        }

        $url = $_SERVER['HTTP_REFERER'];
        redirect($url);
   
    }

    function getsubsys(){
        $data['data'] =  $this->Ssomodel->fetch_subsys();
        echo json_encode($data['data']);
    }

    function remsubsys($id){
        $this->Ssomodel->rem_subsys($id);
        
        $url = $_SERVER['HTTP_REFERER'];
		redirect($url);
    }
}

?>