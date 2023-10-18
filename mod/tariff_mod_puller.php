<?php

    function insertActualTariffMOD($year, $month, $conn, $connection_maria){

      $insertionQueryTariff ="INSERT INTO desco.ACTUAL_TRF_WISE_MOD (division_id, year, month, tariff,consumer_nos, bill_nos, unit_kwh, net_bill, net_collection, bc_ratio,
                             adjusted_unit, cumulative_adjusted_unit,BILLED_VAT, COLLECTED_VAT) VALUES "; 
                              
      $divTariffMod = tariff_mod_div_pull($year, $month, $conn);
  
      foreach($divTariffMod as $dataRow){
        $divisionId = findDivIdByName($dataRow['DIVISION'],$connection_maria);
        $tariffwiseYtdAdjustment = findCummilativeAdjustedUnit($divisionId, $year, $month-1, $dataRow['TARIFF'], $connection_maria) -> fetch_array(MYSQLI_ASSOC);
        $insertionQueryTariff .="(".
                                  $divisionId.",".
                                  $dataRow['YEAR'].",".
                                  $dataRow['MONTH'].",". 
                                  $dataRow['TARIFF'].",".                         
                                  $dataRow['CONSUMER_NOS'].",".
                                  $dataRow['NOS_BILL_NORMAL'].",".
                                  $dataRow['TOTAL_UNIT'].",".                                                                                                    
                                  $dataRow['TOTAL_AMOUNT'].",".                             
                                  $dataRow['COLLECTION'].",".
                                  $dataRow['BC_RATIO'].",".
                                  $dataRow['ADJUSTED_UNIT'].",".
                                  $tariffwiseYtdAdjustment['cumulative_adjusted_unit'].",".
                                  $dataRow['BILLED_VAT'].",".
                                  $dataRow['COLLECTED_VAT'].
                                "),";                              
      }   

      $descoActualTrf = tariff_mod_pull_desco($year, $month, $conn);
      $divisionId = findDivIdByName('DESCO',$connection_maria);
      
      foreach($descoActualTrf as $dataRow){        
        $tariffwiseYtdAdjustment = findCummilativeAdjustedUnit($divisionId, $year, $month-1, $dataRow['TARIFF'], $connection_maria) -> fetch_array(MYSQLI_ASSOC);
        $insertionQueryTariff .="(".
                                    $divisionId.",".
                                    $dataRow['YEAR'].",".
                                    $dataRow['MONTH'].",". 
                                    $dataRow['TARIFF'].",".                         
                                    $dataRow['CONSUMER_NOS'].",".
                                    $dataRow['NOS_BILL_NORMAL'].",".
                                    $dataRow['TOTAL_UNIT'].",".                                                                                                    
                                    $dataRow['TOTAL_AMOUNT'].",".                             
                                    $dataRow['COLLECTION'].",".
                                    $dataRow['BC_RATIO'].",".
                                    $dataRow['ADJUSTED_UNIT'].",".
                                    $tariffwiseYtdAdjustment['cumulative_adjusted_unit'].",".
                                    $dataRow['BILLED_VAT'].",".
                                    $dataRow['COLLECTED_VAT'].
                                  "),";
      }

      $insertionQueryTariff = substr($insertionQueryTariff,0,strlen($insertionQueryTariff)-1); 
      
      $connection_maria -> query($insertionQueryTariff);
      $rowsInserted = $connection_maria->affected_rows;

      $ytdRowsUpdated = updateYtdFields($year, $month, $connection_maria);
   
      if($rowsInserted){
        return $rowsInserted." rows Inserted into ACTUAL_TRF_WISE_MOD Table and YTD fields in ".$ytdRowsUpdated." rows Updated<br/>";
      }else{
        return "Data Insertion into ACTUAL_TRF_WISE_MOD table failed<br/>";
      }

    }


    function updateYtdFields($year, $month, $connection){
      if($month == 7)
         $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD SET `ytd_unit`=`unit_kwh`, `ytd_bill`=`net_bill`, `ytd_collection`=`net_collection` WHERE year = $year AND month = $month";
      else if($month == 1)
         $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD a JOIN desco.ACTUAL_TRF_WISE_MOD b ON a.division_id = b.division_id and a.tariff = b.tariff 
         SET a.`ytd_unit`= a.`unit_kwh` + IFNULL(b.`ytd_unit`,0), a.`ytd_bill`= a.`net_bill` + IFNULL(b.`ytd_bill`,0), 
         a.`ytd_collection` = a.`net_collection` + IFNULL(b.`ytd_collection`,0) WHERE a.year = $year AND a.month = $month AND b.year = $year-1 AND b.month = 12";
      else
         $sql ="UPDATE desco.ACTUAL_TRF_WISE_MOD a JOIN desco.ACTUAL_TRF_WISE_MOD b ON a.division_id = b.division_id and a.tariff = b.tariff AND a.year = b.year
         SET a.`ytd_unit`= a.`unit_kwh` + IFNULL(b.`ytd_unit`,0), a.`ytd_bill`= a.`net_bill` + IFNULL(b.`ytd_bill`,0), 
         a.`ytd_collection` = a.`net_collection` + IFNULL(b.`ytd_collection`,0) WHERE a.year =$year AND a.month =$month AND b.month =$month-1";

      $connection -> query($sql);
      return $connection->affected_rows; 
    }


    function findCummilativeAdjustedUnit($divisionId, $year, $month, $tariff, $connection){      
      if($month <= 0){
        $month = 12;
        $year -= 1;
        $sql = "SELECT `cumulative_adjusted_unit` FROM desco.ACTUAL_TRF_WISE_MOD WHERE tariff = $tariff and `year`= $year AND `month` = $month AND division_id = '$divisionId'";
      }
      else if($month == 6){
        $sql = "SELECT 0 AS `cumulative_adjusted_unit` FROM desco.ACTUAL_TRF_WISE_MOD WHERE tariff = $tariff and `year`= $year AND `month` = $month AND division_id = '$divisionId'";
      }
      else{
        $sql = "SELECT `cumulative_adjusted_unit` FROM desco.ACTUAL_TRF_WISE_MOD WHERE tariff = $tariff and `year`= $year AND `month` = $month AND division_id = '$divisionId'";
      }
      
      $resultSet = $connection -> query($sql);
      return $resultSet;
    }  
    
  function tariff_mod_div_pull($year,$month,$connection){       
      $sql = "SELECT ms.DIVISION, ms.MOD_YEAR YEAR, ms.MOD_MONTH MONTH, ''''||ms.TARIFF||'''' TARIFF, nvl(ms.CONSUMER_NOS,0) CONSUMER_NOS,nvl(ms.NOS_BILL_NORMAL,0) NOS_BILL_NORMAL,
              NVL(ms.TOTAL_UNIT,0) TOTAL_UNIT, nvl(ms.TOTAL_AMOUNT,0) TOTAL_AMOUNT, nvl(ms.COLLECTION,0) COLLECTION, TRUNC((nvl(ms.COLLECTION,0) / NVL(NULLIF(ms.TOTAL_AMOUNT,0),1))*100, 8) BC_RATIO,
              0 ADJUSTED_UNIT, nvl(ms.BILLED_VAT,0) BILLED_VAT, nvl(ms.COLLECTED_VAT,0) COLLECTED_VAT
              FROM DESCO.MOD_SUMMARY ms
              where ms.mod_year = $year and ms.mod_month = $month
              ORDER BY MS.DIVISION, ms.TARIFF ";
            
      $parsed = oci_parse($connection, $sql);
      oci_execute($parsed);
      oci_fetch_all($parsed, $resultSet, 0, -1, OCI_FETCHSTATEMENT_BY_ROW	+ OCI_ASSOC);
      oci_free_statement($parsed);
      return $resultSet;
  }

  function tariff_mod_pull_desco($year,$month,$connection){       
    $sql = "SELECT 'DESCO' DIVISION, ms.MOD_YEAR YEAR, ms.MOD_MONTH MONTH, ''''||ms.TARIFF||'''' TARIFF, sum(ms.CONSUMER_NOS) CONSUMER_NOS, sum(ms.NOS_BILL_NORMAL) NOS_BILL_NORMAL,
            sum(ms.TOTAL_UNIT) TOTAL_UNIT, sum(ms.TOTAL_AMOUNT) TOTAL_AMOUNT, sum(ms.COLLECTION) COLLECTION, 
            TRUNC((nvl(sum(ms.COLLECTION),0) / NVL(NULLIF(sum(ms.TOTAL_AMOUNT),0),1))*100, 8) BC_RATIO, 0 ADJUSTED_UNIT, nvl(sum(ms.BILLED_VAT),0) BILLED_VAT, nvl(sum(ms.COLLECTED_VAT),0) COLLECTED_VAT            
            FROM DESCO.MOD_SUMMARY ms 
            where ms.mod_year = $year and ms.mod_month = $month
            group by ms.mod_year, ms.mod_month, ms.tariff
            order by ms.tariff";
          
    $parsed = oci_parse($connection, $sql);
    oci_execute($parsed);
    oci_fetch_all($parsed, $resultSet, 0, -1, OCI_FETCHSTATEMENT_BY_ROW	+ OCI_ASSOC);
    oci_free_statement($parsed);
    return $resultSet;
  }
?>