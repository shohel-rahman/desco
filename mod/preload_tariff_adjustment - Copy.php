<?php
  require('../../../../opt/config/connection.php');  
  $connection_maria = $mysqli;
  $year=2022;
  $month =7;

  $adjusmentTableDesco = array();

  $divSalesAdj = findDivwiseSlsAdjmnt($year,$month,$connection_maria);
  while($div = $divSalesAdj -> fetch_array(MYSQLI_ASSOC)){
    // echo "Division Id ".$div['division_id'];
    // echo "<br />";
    // echo "Total Sales Unit ".$div['sales_unit'];
    // echo "<br />";

    // echo "<br />";

    $trfSalesUnit = findDivTrfSlsUnit($div['division_id'], $year, $month, $connection_maria);
    // echo "triff selected " .mysqli_num_rows($trfSalesUnit)."<br />";
    $selectedTariff = array();
    $adjGivenInTrf = array();
    $tariffwiseYtdAdjustment = array();
   

    while($trf = $trfSalesUnit -> fetch_array(MYSQLI_ASSOC)){   
      // echo "seleted tariff ".$trf['tariff']; echo "<br />";
      array_push($selectedTariff, $trf['tariff']);
      
      // echo "unit in this tariff ".$trf['unit_kwh']; echo "<br />";
      array_push($adjGivenInTrf, floatval($trf['unit_kwh']));
      // echo "".$trf['adjusted_unit']; echo "<br />";
      // echo "ytd adjustment given in this tariff ".$trf['cumulative_adjusted_unit']; echo "<br />";
      array_push($tariffwiseYtdAdjustment, floatval($trf['cumulative_adjusted_unit']));
      // $adjUnitInTariff = calcTariffAdjustment($div['sales_unit'], $remainingAdjustment, $trf['unit_kwh']);
      // echo "unit calculated to be adjusted ".$adjUnitInTariff;
      // echo "<br />";
      // $remainingAdjustment -= $adjUnitInTariff;
      // echo "Remaining adustment is ".$remainingAdjustment;
      //echo "<br />";
      // UPDATE `ACTUAL_TRF_WISE_MOD1` SET adjusted_unit = 0, cumulative_adjusted_unit =0;     
    }
    
    // var_dump($selectedTariff);
    // echo "<br />";

    // echo " YTD adjustment upto prev month  <br/>";
    // var_dump($tariffwiseYtdAdjustment);
    // echo "<br />";

    // $adjGivenInTrf = $actulUnitInTrf;
    // var_dump($adjGivenInTrf);
    // echo "<br />";
    
    array_walk($adjGivenInTrf,'calcTariffAdjustment', $div['sales_adjustment'] / array_sum($adjGivenInTrf));
    
    // echo "After adjustment <br/>";
    // var_dump($adjGivenInTrf);
        


    //$status = updateActTrfTbl($div['division_id'], $year, $month, $selectedTariff[$i], $adjGivenInTrf[$i], $adjGivenInTrf[$i] + $tariffwiseYtdAdjustment[$i], $connection_maria);
    echo "<br />Total ".$div['sales_adjustment']." unit adjustment needed in Division ID ".$div['division_id']."<br />";        
    for( $i=0; $i<count($selectedTariff); $i++){
      $status = updateActTrfTbl($div['division_id'], $year, $month, $selectedTariff[$i], $adjGivenInTrf[$i], $adjGivenInTrf[$i] + $tariffwiseYtdAdjustment[$i], $connection_maria);      
      if($status){       
        echo $adjGivenInTrf[$i]." unit adjusted in Tariff ". $selectedTariff[$i];
        // ." and YTD adjustment is ".$adjGivenInTrf[$i] + $tariffwiseYtdAdjustment[$i]
      }
      else echo  $adjGivenInTrf[$i]." unit adjustment failed in Tariff ". $selectedTariff[$i]; 
      echo "<br />";


      // if($tariffwiseYtdAdjustment[$i] == 0)  
      //   array_push($adjusmentTableDesco, array($div['division_id'], $selectedTariff[$i], $adjGivenInTrf[$i], $adjGivenInTrf[$i]));
      // else 
      //   array_push($adjusmentTableDesco, array($div['division_id'], $selectedTariff[$i], $adjGivenInTrf[$i], floatval($adjGivenInTrf[$i]+$tariffwiseYtdAdjustment[$i])));
      
    }
    
    unset($trfSalesUnit);
    unset($selectedTariff);
    unset($adjGivenInTrf);
    unset($tariffwiseYtdAdjustment);
    // unset($i);    
  }
  
  // echo "<br />";echo "<br />";echo "<br />";
  // echo "Full table";echo "<br />";
// $updateQuery = "";
//   for($i = 0; $i<count($adjusmentTableDesco); $i++){ 

//         $div = ($adjusmentTableDesco[$i][0]);
//         $tariff = ($adjusmentTableDesco[$i][1]);
//         $adjUnit = (($adjusmentTableDesco[$i][2]));
//         $ytdAdjUnit = (($adjusmentTableDesco[$i][3]));

        
        // echo "Monthly adj ".$adjUnit." type is ".gettype($adjUnit)."<br />";
        // echo "YTD adj ".$ytdAdjUnit." type is ".gettype($ytdAdjUnit)."<br />";
        // $mamun=$ytdAdjUnit; `cumulative_adjusted_unit` = $ytdAdjUnit,
        
        // $updateQuery .= " UPDATE desco.ACTUAL_TRF_WISE_MOD1 SET `adjusted_unit` = $adjUnit, `cumulative_adjusted_unit` = $ytdAdjUnit WHERE year = $year and month = $month and
        //     tariff = '$tariff' and division_id = $div;";             
        // echo $sql;
        // $status = $connection_maria -> query($sql);


        // $status = updateActTrfTbl($adjusmentTableDesco[$i][$j][0], $year, $month, $adjusmentTableDesco[$i][$j][1], $adjusmentTableDesco[$i][$j][2], $adjusmentTableDesco[$i][$j][3], $connection_maria);
        // if($status) echo "  Adjustement given in Tariff ". $adjusmentTableDesco[$i][1];
        // else echo " Failed in Tariff ". $adjusmentTableDesco[$i][1];  
        // echo "<br />";

        // unset($tariff);
        // unset($adjUnit);
        // unset($ytdAdjUnit);
        // unset($div);
             
  // }
  //  echo "lenght is ".strlen($updateQuery);
  //  $updateQuery = substr($updateQuery,0,strlen($updateQuery)-1); 
  //  echo $updateQuery;
  // echo "lenght is ".strlen($updateQuery);
  //  $status = $connection_maria -> multi_query($updateQuery);
  //  $status =  multi_query($updateQuery);
  //  if($status) echo $status;
  //  else echo " Failed in Tariff ";
  $connection_maria->close();


  function findDivwiseSlsAdjmnt($year, $month, $connection){
    $sql = "SELECT division_id, year, month, `sales_unit`, `sales_adjustment` FROM desco.`ACTUAL_MOD1` WHERE division_id <> 9999 and sales_adjustment <> 0 AND year = $year AND month = $month";
    $resultSet = $connection -> query($sql);
    return $resultSet;
  }

  function findDivTrfSlsUnit($divId, $year, $month, $connection){
    $sql = "SELECT division_id, year, month, tariff, unit_kwh, ifnull(adjusted_unit,0) adjusted_unit, 
            ifnull(cumulative_adjusted_unit,0) cumulative_adjusted_unit FROM desco.ACTUAL_TRF_WISE_MOD1 
            WHERE year = $year AND month = $month AND division_id = $divId AND consumer_nos > 100 
            ORDER by unit_kwh DESC, consumer_nos DESC  LIMIT 7";
        
    $resultSet = $connection -> query($sql);
    return $resultSet;    
  }

  function calcTariffAdjustment(&$arr, $key, $ratio){
    $arr = round($arr * $ratio);
  }

  function updateActTrfTbl ($div, $year, $month, $tariff, $adjUnit, $ytdAdjUnit, $connection){
    $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD1 SET adjusted_unit= $adjUnit, cumulative_adjusted_unit= $ytdAdjUnit WHERE year = $year and month = $month and
            tariff = '$tariff' and division_id = $div";               
    //echo $sql;
    return $connection -> query($sql);
  }
?>