<?php

  function adjustNInsert($year,$month,$connection_maria){

    $divSalesAdj = findDivwiseSlsAdjmnt($year,$month,$connection_maria);

    while($div = $divSalesAdj -> fetch_array(MYSQLI_ASSOC)){

      $trfSalesUnit = findDivTrfSlsUnit($div['division_id'], $year, $month, $connection_maria);    
      $selectedTariff = array();
      $adjGivenInTrf = array();
      $tariffwiseYtdAdjustment = array();
    
      while($trf = $trfSalesUnit -> fetch_array(MYSQLI_ASSOC)){         
        array_push($selectedTariff, $trf['tariff']);
        array_push($adjGivenInTrf, floatval($trf['unit_kwh']));
        array_push($tariffwiseYtdAdjustment, floatval($trf['cumulative_adjusted_unit']));    
      }
      
      array_walk($adjGivenInTrf,'calcTariffAdjustment', $div['sales_adjustment'] / array_sum($adjGivenInTrf));
      
      echo "<br />Total ".$div['sales_adjustment']." unit adjustment needed in Division ID ".$div['division_id']."<br />";        
      for( $i=0; $i<count($selectedTariff); $i++){
        $status = updateActTrfTbl($div['division_id'], $year, $month, $selectedTariff[$i], $adjGivenInTrf[$i], $adjGivenInTrf[$i] + $tariffwiseYtdAdjustment[$i], $connection_maria);      
        if($status){       
          echo $adjGivenInTrf[$i]." unit adjusted in Tariff ". $selectedTariff[$i];
        }
        else echo  $adjGivenInTrf[$i]." unit adjustment failed in Tariff ". $selectedTariff[$i]; 
        echo "<br />";
      }
      
      unset($trfSalesUnit);
      unset($selectedTariff);
      unset($adjGivenInTrf);
      unset($tariffwiseYtdAdjustment);  
    }

    $rowsInsrted = insertPublishedTrf($year, $month, $connection_maria);
    $ytdrowsUnitBillUpdated = updateYtdUnitBill($year, $month, $connection_maria);
    if($rowsInsrted > 0) echo "<br />".$rowsInsrted." Rows inserted in Published Tariffwise MOD Table and YTD fields in ".$ytdrowsUnitBillUpdated." rows Updated<br/>";
    else  echo "<br/ > No row inserted in Published Tariffwise MOD Table.";
  }

  function updateYtdUnitBill($year, $month, $connection){
    if($month == 7)
       $sql = "UPDATE desco.PUBLISHED_TRF_WISE_MOD SET `ytd_unit`=`unit_kwh`, `ytd_bill`=`net_bill`, ytd_collection = net_collection WHERE year = $year AND month = $month";
    else if($month == 1)
       $sql = "UPDATE desco.PUBLISHED_TRF_WISE_MOD a JOIN desco.PUBLISHED_TRF_WISE_MOD b ON a.division_id = b.division_id and a.tariff = b.tariff 
       SET a.`ytd_unit`= a.`unit_kwh` + IFNULL(b.`ytd_unit`,0), a.`ytd_bill`= a.`net_bill` + IFNULL(b.`ytd_bill`,0), a.ytd_collection = a.net_collection + IFNULL(b.ytd_collection,0)
       WHERE a.year = $year AND a.month = $month AND b.year = $year-1 AND b.month = 12";
    else
       $sql ="UPDATE desco.PUBLISHED_TRF_WISE_MOD a JOIN desco.PUBLISHED_TRF_WISE_MOD b ON a.division_id = b.division_id and a.tariff = b.tariff AND a.year = b.year
       SET a.`ytd_unit`= a.`unit_kwh` + IFNULL(b.`ytd_unit`,0), a.`ytd_bill`= a.`net_bill` + IFNULL(b.`ytd_bill`,0), a.ytd_collection = a.net_collection + IFNULL(b.ytd_collection,0)
       WHERE a.year =$year AND a.month =$month AND b.month =$month-1";

    $connection -> query($sql);
    return $connection->affected_rows; 
  }

  function insertPublishedTrf($year, $month, $connection){
    $sql = "INSERT INTO desco.`PUBLISHED_TRF_WISE_MOD`(`division_id`, `year`, `month`, `tariff`, `consumer_nos`, `bill_nos`, `unit_kwh`, `net_bill`, `net_collection`, 
            `bc_ratio`, `BILLED_VAT`, `COLLECTED_VAT`) SELECT `division_id`, `year`, `month`, `tariff`, `consumer_nos`, `bill_nos`, `unit_kwh`+`adjusted_unit`,
            `net_bill`, `net_collection`, `bc_ratio`,`BILLED_VAT`, `COLLECTED_VAT` FROM desco.`ACTUAL_TRF_WISE_MOD` WHERE year=$year and month=$month";    
    $connection -> query($sql);
    return $connection->affected_rows;          
  }

  function findDivwiseSlsAdjmnt($year, $month, $connection){
    $sql = "SELECT division_id, year, month, `sales_unit`, `sales_adjustment` FROM desco.`ACTUAL_MOD` WHERE division_id <> 9999 and sales_adjustment <> 0 AND year = $year AND month = $month";
    $resultSet = $connection -> query($sql);
    return $resultSet;
  }

  function findDivTrfSlsUnit($divId, $year, $month, $connection){
    $sql = "SELECT division_id, year, month, tariff, unit_kwh, ifnull(adjusted_unit,0) adjusted_unit, 
            ifnull(cumulative_adjusted_unit,0) cumulative_adjusted_unit FROM desco.ACTUAL_TRF_WISE_MOD
            WHERE year = $year AND month = $month AND division_id = $divId AND consumer_nos > 100 
            ORDER by unit_kwh DESC, consumer_nos DESC  LIMIT 7";
        
    $resultSet = $connection -> query($sql);
    return $resultSet;    
  }

  function calcTariffAdjustment(&$arr, $key, $ratio){
    $arr = round($arr * $ratio);
  }

  function updateActTrfTbl ($div, $year, $month, $tariff, $adjUnit, $ytdAdjUnit, $connection){
    $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD SET adjusted_unit= $adjUnit, cumulative_adjusted_unit= $ytdAdjUnit WHERE year = $year and month = $month and
            tariff = '$tariff' and division_id = $div";               
    return $connection -> query($sql);
  }
  
?>