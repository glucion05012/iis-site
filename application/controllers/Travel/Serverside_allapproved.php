<?php



defined('BASEPATH') OR exit('No direct script access allowed');

/**

 *

 */

class Serverside_allapproved extends CI_Controller

{

  public function __construct() {

    parent::__construct();
    $this->load->library('session');
    $this->load->library('form_validation');

  }

  public function index(){

      $region    = $this->session->userdata('region');
      $userid    = $this->session->userdata('userid');
      $usertoken = $this->session->userdata('token');

      // Database connection info
      $dbDetails = array(
          'host' => $this->session->userdata('host'),
          'user' => $this->session->userdata('user'),
          'pass' => $this->session->userdata('pass'),
          'db'   => $this->session->userdata('db')
      );

      // DB table to use

      $table ="to_trans";

      // Table's primary key

      $primaryKey = 'er_no';

      // Array of database columns which should be read and sent back to DataTables.
      // The `db` parameter represents the column name in the database.
      // The `dt` parameter represents the DataTables column identifier.x

       $columns = array(
                      array( 'db' => '`tt`.`name`', 'dt' => 'name', 'field' => 'name'),
                      array( 'db' => '`tt`.`er_no`', 'dt' => 'er_no', 'field' => 'er_no'),
                      array( 'db' => '`tt`.`toid`', 'dt' => 'toid', 'field' => 'toid'),
                      array( 'db' => '`tt`.`travel_cat`', 'dt' => 'travel_cat', 'field' => 'travel_cat','formatter'=>function($x,$row){
                        if($row['travel_cat'] == "Yes"){ $cat = "Regional"; }else{ $cat = "National"; }
                        return $cat;
                      }),
                      array( 'db' => '`tt`.`travel_type`', 'dt' => 'travel_type', 'field' => 'travel_type'),
                      array( 'db' => '`tt`.`departure_date`', 'dt' => 'departure_date', 'field' => 'departure_date','formatter'=>function($x,$row){
                        $departure_date = date("M d, Y", strtotime($row['departure_date']));
                        return $departure_date;
                      }),
                      array( 'db' => '`tt`.`arrival_date`', 'dt' => 'arrival_date', 'field' => 'arrival_date','formatter'=>function($x,$row){
                        $arrival_date = date("M d, Y", strtotime($row['arrival_date']));
                        return $arrival_date;
                      }),
                      array( 'db' => '`tt`.`travel_purpose`', 'dt' => 'travel_purpose', 'field' => 'travel_purpose','formatter'=>function($x,$row){
                        // $travel_purpose = "<span title='".$row['travel_purpose']."'>".substr($row['travel_purpose'],0,150)."....."."</span>";
                        return $row['travel_purpose'];
                      }),
                      array( 'db' => '`tt`.`destination`', 'dt' => 'destination', 'field' => 'destination','formatter'=>function($x,$row){
                        $destination = str_replace('Array', '', $row['destination']);
                        return $destination;
                      }),
                      array( 'db' => '`tt`.`status`', 'dt' => 'status', 'field' => 'status'),

                      array( 'db' => '`tt`.`token`', 'dt' => 0, 'field' => 'token'),

                      array( 'db' => '`et`.`token`', 'dt' => 1, 'field' => 'token','formatter'=>function($x,$row){
                        return $this->encrypt->encode($row[10]);
                      }),
                      array( 'db' => '`tt`.`travel_type`', 'dt' => 'travel_type', 'field' => 'travel_type'),
                      array( 'db' => '`ttr`.`cnt`', 'dt' => 'cnt', 'field' => 'cnt'),

                      array( 'db' => '`et`.`receive`',  'dt' => 'receive', 'field' => 'receive' ),
                  );

      // Include SQL query processing class

        $this->load->view('includes/common/ssp.customized.class.php');

        $joinQuery  = "FROM `embis`.`to_trans` AS `tt`
                       LEFT JOIN `embis`.`er_transactions` AS `et` ON `et`.`trans_no`=`tt`.`er_no`
                       LEFT JOIN `embis`.`to_ticket_request` AS `ttr` ON `ttr`.`er_no`=`tt`.`er_no`";
        if($this->session->userdata('func') == 'Regional Executive Director'){
          $joinQuery .= "LEFT JOIN `embis`.`to_trans_log` AS `ttl` ON `ttl`.`er_no`=`tt`.`er_no` AND `ttl`.`assignedto`='".$userid."'";
          $extraWhere = "`et`.`region` = '".$region."' AND `et`.`status` != 0  AND (`tt`.`status` = 'Approved' OR `tt`.`status` = 'Signed Document') AND `ttl`.`assignedto`='".$userid."'";
        }else if($region == 'CO'){
          // $joinQuery .= "LEFT JOIN `embis`.`to_trans_log` AS `ttl` ON `ttl`.`er_no`=`tt`.`er_no` AND `ttl`.`assignedto`='".$userid."'";
          $extraWhere = "`et`.`status` != 0  AND (`tt`.`status` = 'Approved' OR `tt`.`status` = 'Signed Document')";
        }else{
          $joinQuery .= "LEFT JOIN `embis`.`to_trans_log` AS `ttl` ON `ttl`.`er_no`=`tt`.`er_no` AND `ttl`.`assignedto`='".$userid."'";
          $extraWhere = "`et`.`status` != 0  AND (`tt`.`status` = 'Approved' OR `tt`.`status` = 'Signed Document') AND `et`.`region`='".$region."'";
        }
        $groupBy    = null;
        $having     = null;

      echo json_encode(
          SSP::simple($_GET, $dbDetails, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy, $having)
      );
      $this->db->close();
  }

}
?>
