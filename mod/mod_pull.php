<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  
  
  require('../../../../opt/config/orcl_con.php');
  

  $dblink = ['AGARGAON'=>'@AGARGAON','BADDA'=>'@BADDA','BARIDHARA'=>'@BARIDHARA','BASHUNDHARA'=>'@BASHUNDHARA','DAKSHINKHAN'=>'@DAKSHINKHAN','EASTERN HOUSING'=>'@EASTERNHOUSING',
              'GULSHAN'=>'@GULSHAN','IBRAHIMPUR'=>'@IBRAHIMPUR','JOARSHAHARA'=>'@JOARSHAHARA','KAFRUL'=>'@JDMIS','KALLYANPUR'=>'@KALLYANPUR','KHILKHET'=>'@KHILKHET',
              'MONIPUR'=>'@KPUR','PALLABI'=>'@UTTARA','RUPNAGAR'=>'@RUPNAGAR','SHAHALI'=>'@SHAHALI','SHAHKABIR'=>'@SHAHKABIR','TONGI CENTRAL'=>'@TONGICENTRAL',
              'TONGI EAST'=>'@TONGIE','TONGI WEST'=>'@TONGIW','TURAG'=>'@TURAG','UTTARA EAST'=>'@DBUTTARA','UTTARA WEST'=>'@UTTARAWEST','UTTARKHAN'=>'@UTTARKHAN'
            ];  
                        
  $year = $_GET['year'];        
  $month = $_GET['month'];        
  for($i=0; $i<count($_GET); $i++){
    if($_GET[$i] != null)
      $pullList[$i] = $_GET[$i];
  }        
  //print_r($pullList);       
  foreach($pullList as $division){
    if(checkConnectivity($conn, $dblink[$division])>0){
      echo "Connected to ".$division." S & D Division<br/><br/>";
      
      $confirmationStatus = checkConfirmationStatus($conn, $dblink[$division], $year, $month);
      if( $confirmationStatus == "All Confirmed" ){
        echo "Required data is confirmed.<br />";
        
        echo "Inserting Data into MOD_SUMMARY_TOTAL Table. Please have patience...<br />";
        $rowsInserted = pullModSummaryTotal($conn, $dblink[$division], $year, $month);
        if($rowsInserted>0){
          echo "Sucessfully inserted "  .$rowsInserted. " rows into MOD_SUMMARY_TOTAL table.<br /><br/>";
        }else{
          echo "Data Insertion into MOD_SUMMARY_TOTAL Table has failed.<br /><br/>";
        }

        echo "Inserting Data into MOD_SUMMARY Table. Please have patience...<br />";
        $rowsInserted = pullModSummary($conn, $dblink[$division], $year, $month);
        if($rowsInserted > 0){
          echo "Sucessfully inserted " .$rowsInserted. " rows into MOD_SUMMARY table.<br /><br/>";
        }else{
          echo "Data Insertion into MOD_SUMMARY Table has failed.<br /><br/>";
        }

        echo "Inserting Data into MOD_PREPAID_DATA Table. Please have patience...<br />";
        $rowsInserted = pullModPrepaidData($conn, $dblink[$division], $year, $month);
        if($rowsInserted > 0){
          echo "Sucessfully inserted " .$rowsInserted. " rows into MOD_PREPAID_DATA table.<br /><br/>";
        }else{
          echo "Data Insertion into MOD_PREPAID_DATA Table has failed.<br /><br/>";
        }
      }
      else{
        echo "Neccessary data is not confirmed yet.<br />".$confirmationStatus . "<br />";            
        echo "Please assure confirmation and try again later.<br /><br />";
      }
    }
    else{
      echo "Could not Connect to ".$division." S & D Division. Ensure Connectivity and try again.<br />";
      echo "<br /><br />";          
    }
  } 
  
  function checkConfirmationStatus($oracle_connection, $link, $year, $month){
    $sql = "SELECT COUNT(*) AS CONFIRMED_ROWS FROM DESCO.MOD_PREPAID_DATA" .$link. " WHERE STATUS ='Y' AND MONTH = $month AND YEAR = $year";
    $parsed = oci_parse($oracle_connection, $sql);
    oci_execute($parsed);
    $resultSet = oci_fetch_array($parsed, OCI_BOTH + OCI_RETURN_NULLS);
    oci_free_statement($parsed);  

    if($resultSet['CONFIRMED_ROWS'] == 0){
      return "Prepaid Data Not Confirmed";
    }
    
    $sql = "SELECT COUNT(*) AS CONFIRMED_ROWS FROM DESCO.MOD_SUMMARY" .$link. " WHERE STATUS ='Y' AND MOD_MONTH = $month AND MOD_YEAR = $year";
    $parsed = oci_parse($oracle_connection, $sql);
    oci_execute($parsed);
    $resultSet = oci_fetch_array($parsed, OCI_BOTH + OCI_RETURN_NULLS);
    oci_free_statement($parsed);

    if($resultSet['CONFIRMED_ROWS'] == 0){
      return "MOD Summary Table Not Confirmed";
    }
    
    $sql = "SELECT COUNT(*) AS CONFIRMED_ROWS FROM DESCO.MOD_SUMMARY_TOTAL" .$link. " WHERE STATUS ='Y' AND MONTH = $month AND YEAR = $year";
    $parsed = oci_parse($oracle_connection, $sql);
    oci_execute($parsed);
    $resultSet = oci_fetch_array($parsed, OCI_BOTH + OCI_RETURN_NULLS);
    oci_free_statement($parsed);

    if($resultSet['CONFIRMED_ROWS'] == 0){
      return "MOD Summary Total Table Not Confirmed";
    }

    return "All Confirmed";
  }

  function checkConnectivity($oracle_connection, $link){
    $sql ="SELECT S_AND_D_NAME FROM DESCO.S_AND_D_INFO".$link;
    $parsed = oci_parse($oracle_connection, $sql);
    oci_execute($parsed);         
    $inserted_rows = oci_num_rows($parsed);
    oci_free_statement($parsed);
    return $inserted_rows; 
  }

  function pullModPrepaidData($oracle_connection, $link, $year, $month){
    $sql = "INSERT INTO DESCO.MOD_PREPAID_DATA@HMIS(MONTH, YEAR, TARIFF, CONSUMER_NOS, BILL_NOS, UNIT_KWH, NET_BILL, NET_COLL, ENTRY_DATE, ENTRY_BY, S_CONNECTED, 
            S_REMOVED, S_CONNECTED_YTD, S_REMOVED_YTD, STATUS,division, VAT_COLL, BILLED_VAT)
            SELECT 
              MONTH, YEAR, TARIFF, CONSUMER_NOS, BILL_NOS, UNIT_KWH, NET_BILL, NET_COLL, ENTRY_DATE, ENTRY_BY, S_CONNECTED, S_REMOVED, S_CONNECTED_YTD, S_REMOVED_YTD,
              STATUS,SND.S_AND_D_NAME,VAT_COLL, BILLED_VAT FROM DESCO.MOD_PREPAID_DATA".$link.",(SELECT SD.S_AND_D_NAME FROM desco.s_and_d_info".$link." sd) snd WHERE STATUS ='Y' AND MONTH=$month AND YEAR=$year";
    $parsed = oci_parse($oracle_connection, $sql);
    // oci_execute($parsed);         
    $inserted_rows = oci_num_rows($parsed);
    oci_free_statement($parsed);
    return $inserted_rows;          
  }

  function pullModSummary($oracle_connection, $link, $year, $month){
    $sql = "INSERT INTO DESCO.MOD_SUMMARY@HMIS(TARIFF, CONSUMER_NOS, NOS_BILL_NORMAL, UNIT_NORMAL, AMOUNT_NORMAL, NOS_BILL_SUPP, UNIT_SUPP, AMOUNT_SUPP, 
            S_DATE_SUPP,E_DATE_SUPP, NOS_BILL_DMCM, UNIT_DMCM, AMOUNT_DMCM, S_DATE_DMCM, E_DATE_DMCM, NOS_MIN_BILL, NOS_EST_BILL, TOTAL_UNIT, TOTAL_AMOUNT, 
            COLLECTION, MOD_MONTH, MOD_YEAR, ENTRY_DATE, STATUS, MOD_BY, ADJUSTMENT, SUNIT_AFTER_ADJUSTMENT, DIVISION, VAT_NORMAL, VAT_SUPP, VAT_DMCM, 
            BILLED_VAT, COLLECTED_VAT)
            SELECT 
              TARIFF, CONSUMER_NOS, NOS_BILL_NORMAL, UNIT_NORMAL, AMOUNT_NORMAL, NOS_BILL_SUPP, UNIT_SUPP, AMOUNT_SUPP, S_DATE_SUPP, E_DATE_SUPP, NOS_BILL_DMCM, 
              UNIT_DMCM, AMOUNT_DMCM, S_DATE_DMCM, E_DATE_DMCM, NOS_MIN_BILL, NOS_EST_BILL, TOTAL_UNIT, TOTAL_AMOUNT, COLLECTION, MOD_MONTH, MOD_YEAR, ENTRY_DATE,
              STATUS, MOD_BY, ADJUSTMENT, SUNIT_AFTER_ADJUSTMENT, SND.S_AND_D_NAME, VAT_NORMAL, VAT_SUPP, VAT_DMCM, BILLED_VAT, COLLECTED_VAT 
            FROM DESCO.MOD_SUMMARY".$link.",(SELECT SD.S_AND_D_NAME FROM desco.s_and_d_info".$link." sd) snd WHERE MOD_MONTH =$month AND MOD_YEAR =$year AND STATUS = 'Y'";
    
    $parsed = oci_parse($oracle_connection, $sql);
    // oci_execute($parsed);         
    $inserted_rows = oci_num_rows($parsed);
    oci_free_statement($parsed);
    return $inserted_rows;
  }

  function pullModSummaryTotal($oracle_connection, $link, $year, $month){
    $sql = "INSERT INTO DESCO.MOD_SUMMARY_TOTAL@HMIS(TOTAL_CONSUMER, TOTAL_BILL, SALES_UNIT, BILL_AMOUNT, COLLECTION_AMOUNT, IMPORT_UNIT, SYSTEM_LOSS, BC_RATIO, 
            CI_RATIO, MONTH, YEAR, ENTRY_DATE, STATUS, MOD_BY, IMPORT_LAST_MONTH, SALES_LAST_MONTH, B_AMOUNT_LAST_MONTH, C_AMOUNT_LAST_MONTH, IMPORT_YEAR_TO_DATE, 
            SALES_YEAR_TO_DATE, B_AMOUNT_YEAR_TO_DATE, C_AMOUNT_YEAR_TO_DATE, SL_YTD, BC_RATIO_YTD, CI_RATIO_YTD, SL_PREV, BC_PREV, CI_PREV, SERVICE_CONNECTED, 
            SERVICE_REMOVED, COST_OF_MATERIALS, SECURITY_DEPOSIT, MISCELLANEOUS, SERVICE_CONNECTED_TYD, SERVICE_REMOVED_TYD, COST_OF_MATERIALS_TYD, SECURITY_DEPOSIT_TYD, 
            MISCELLANEOUS_TYD, TARGET, TARGET_BC, TARGET_CI, MIS_ADJUSTMENT_UNIT, AFTER_ADJ_SALES_UNIT, ADJ_SYS_LOSS, ADJ_CI_RATIO, ADJ_SALES_YEAR_TO_DATE, ADJ_SL_YTD, 
            ADJ_CI_RATIO_YTD, DIVISION, BILLED_VAT, COLLECTED_VAT)     
            SELECT 
              TOTAL_CONSUMER, TOTAL_BILL, SALES_UNIT, BILL_AMOUNT, COLLECTION_AMOUNT, IMPORT_UNIT, SYSTEM_LOSS, BC_RATIO, CI_RATIO, MONTH, YEAR, ENTRY_DATE, 
              STATUS, MOD_BY, IMPORT_LAST_MONTH, SALES_LAST_MONTH, B_AMOUNT_LAST_MONTH, C_AMOUNT_LAST_MONTH, IMPORT_YEAR_TO_DATE, SALES_YEAR_TO_DATE, 
              B_AMOUNT_YEAR_TO_DATE, C_AMOUNT_YEAR_TO_DATE, SL_YTD, BC_RATIO_YTD, CI_RATIO_YTD, SL_PREV, BC_PREV, CI_PREV, SERVICE_CONNECTED, SERVICE_REMOVED, 
              COST_OF_MATERIALS, SECURITY_DEPOSIT, MISCELLANEOUS, SERVICE_CONNECTED_TYD, SERVICE_REMOVED_TYD, COST_OF_MATERIALS_TYD, SECURITY_DEPOSIT_TYD, 
              MISCELLANEOUS_TYD, TARGET, TARGET_BC, TARGET_CI, MIS_ADJUSTMENT_UNIT, AFTER_ADJ_SALES_UNIT, ADJ_SYS_LOSS, ADJ_CI_RATIO, ADJ_SALES_YEAR_TO_DATE, 
              ADJ_SL_YTD, ADJ_CI_RATIO_YTD, SND.S_AND_D_NAME, BILLED_VAT, COLLECTED_VAT
            FROM DESCO.MOD_SUMMARY_TOTAL".$link.",(SELECT SD.S_AND_D_NAME FROM desco.s_and_d_info".$link." sd) snd where STATUS='Y' and MONTH=$month and YEAR=$year";          
    
    $parsed = oci_parse($oracle_connection, $sql);
  //  oci_execute($parsed);                  
    $inserted_rows = oci_num_rows($parsed);
    oci_free_statement($parsed);
    return $inserted_rows;
  }        
  oci_close($conn);	       
?>
