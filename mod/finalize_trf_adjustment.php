<?php
  require('../../../../opt/config/connection.php');  
  $connection_maria = $mysqli;
  
  $divSalesAdj = findDivTrfSlsAdjmnt(2022,7,$connection_maria);
  while($div = $divSalesAdj -> fetch_array(MYSQLI_ASSOC)){
    echo "Division Id ".$div['division_id'];
    echo "<br />";
    $ID = $div['division_id']; 
    $tariff = $div['tariff'];  
    echo "Monthlyl adj ".$div['adjusted_unit']; echo "<br />"; 
    echo "YTD adj ".$div['cumulative_adjusted_unit']; echo "<br />"; 
    $ytdAdjUnit = $div['adjusted_unit'] + $div['cumulative_adjusted_unit'];
    echo "summation ".$ytdAdjUnit; echo "<br />"; 

    $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD1 SET `cumulative_adjusted_unit`=$ytdAdjUnit WHERE year = 2022 and month = 7 and
            tariff = '$tariff' and division_id = $ID";             
    echo $sql;
        $status = $connection_maria -> query($sql);
    unset($sql);

    // $status = updateActTrfTbl($div['division_id'], 2022, 7, $div['tariff'], $adjGivenInTrf[$i], $adjGivenInTrf[$i] + $tariffwiseYtdAdjustment[$i], $connection_maria);
    // $status = updateActTrfTbl($div['division_id'], 2022, 7, $selectedTariff[$i], $adjGivenInTrf[$i], $adjGivenInTrf[$i] + $tariffwiseYtdAdjustment[$i], $connection_maria);
    if($status) echo "Adjustement given in div ".$div['division_id']." in Tariff ". $div['tariff'];
    else echo "Failed in div ".$div['division_id']." in Tariff ". $div['tariff'];
    echo "<br />";      
  }
  
  echo "<br />";echo "<br />";echo "<br />";

  $connection_maria ->close();
 

  function findDivTrfSlsAdjmnt($year, $month, $connection){
    $sql = "SELECT division_id, year, month, tariff, ifnull(adjusted_unit,0) adjusted_unit, ifnull(cumulative_adjusted_unit,0) cumulative_adjusted_unit 
            FROM desco.`ACTUAL_TRF_WISE_MOD1` WHERE division_id <> 9999 and adjusted_unit <> 0 AND year = $year AND month = $month";
    $resultSet = $connection -> query($sql);
    return $resultSet;
  }


  function updateActTrfTbl ($div, $year, $month, $tariff, $adjUnit, $ytdAdjUnit, $connection){
    $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD1 SET adjusted_unit= $adjUnit, cumulative_adjusted_unit= $ytdAdjUnit WHERE year = $year and month = $month and
            tariff = '$tariff' and division_id = $div";               
    echo $sql;
    return $connection -> query($sql);
  }

?>