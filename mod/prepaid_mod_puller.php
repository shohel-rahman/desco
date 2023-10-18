<?php

    function insertPrepaidMOD($year, $month, $conn, $connection_maria){

      $insertionQueryPrepaid ="INSERT INTO desco.`MOD_PREPAID_DATA`(`DIVISION_ID`, `YEAR`, `MONTH`, `TARIFF`, `CONSUMER_NOS`, `BILL_NOS`, `UNIT_KWH`, `NET_BILL`, `NET_COLLECTION`, `COLLECTED_VAT`) VALUES "; 
                              
      $divPrepaid = prepaid_mod_div_pull($year, $month, $conn);
  
      foreach($divPrepaid as $dataRow){
        $divisionId = findDivIdByName($dataRow['DIVISION'],$connection_maria);
        $tariff = $dataRow['TARIFF'];
        $insertionQueryPrepaid .="(".
                                  $divisionId.",".
                                  $dataRow['YEAR'].",".
                                  $dataRow['MONTH'].",". 
                                  "'$tariff'".",".                             
                                  $dataRow['CONSUMER_NOS'].",".
                                  $dataRow['BILL_NOS'].",".
                                  $dataRow['UNIT_KWH'].",".                                                                                                    
                                  $dataRow['NET_BILL'].",".                             
                                  $dataRow['NET_COLL'].",".
                                  $dataRow['VAT_COLL']."),";                              
      }   

      $descoPrepaid = prepaid_mod_pull_desco($year, $month, $conn);
      $divisionId = findDivIdByName('DESCO',$connection_maria);
      foreach($descoPrepaid as $dataRow){
        $tariff = $dataRow['TARIFF'];
        $insertionQueryPrepaid .="(".
                                  $divisionId.",".
                                  $dataRow['YEAR'].",".
                                  $dataRow['MONTH'].",". 
                                  "'$tariff'".",".                            
                                  $dataRow['CONSUMER_NOS'].",".
                                  $dataRow['BILL_NOS'].",".
                                  $dataRow['UNIT_KWH'].",".                                                                                                    
                                  $dataRow['NET_BILL'].",".                             
                                  $dataRow['NET_COLL'].",".
                                  $dataRow['VAT_COLL'].
                                "),";
      }

      $insertionQueryPrepaid = substr($insertionQueryPrepaid,0,strlen($insertionQueryPrepaid)-1);                                 
      $connection_maria -> query($insertionQueryPrepaid);
      $rowsInserted = $connection_maria->affected_rows;
   
      if($rowsInserted){
        return $rowsInserted." rows Inserted into MOD_PREPAID_DATA Table<br/>";
      }else{
        return "Data Insertion into MOD_PREPAID_DATA table failed<br/>";
      }

    }


  function prepaid_mod_div_pull($year,$month,$connection){       
      $sql = "SELECT PD.DIVISION, pd.year, pd.month, PD.TARIFF, nvl(PD.CONSUMER_NOS,0) CONSUMER_NOS, nvl(PD.BILL_NOS,0) BILL_NOS, nvl(PD.UNIT_KWH,0) UNIT_KWH,
              nvl(PD.NET_BILL,0) NET_BILL, nvl(PD.NET_COLL,0) NET_COLL, nvl(PD.VAT_COLL,0) VAT_COLL 
              from DESCO.MOD_PREPAID_DATA PD 
              where PD.YEAR = $year and PD.MONTH = $month
              order by PD.DIVISION ASC";
            
      $parsed = oci_parse($connection, $sql);
      oci_execute($parsed);
      oci_fetch_all($parsed, $resultSet, 0, -1, OCI_FETCHSTATEMENT_BY_ROW	+ OCI_ASSOC);
      oci_free_statement($parsed);
      return $resultSet;
  }

  function prepaid_mod_pull_desco($year,$month,$connection){       
    $sql = "SELECT 'DESCO' DIVISION, pd.year, pd.month, PD.TARIFF, sum(PD.CONSUMER_NOS) CONSUMER_NOS, sum(PD.BILL_NOS) BILL_NOS, sum(PD.UNIT_KWH) UNIT_KWH, sum(PD.NET_BILL) NET_BILL,
            sum(PD.NET_COLL) NET_COLL, sum(PD.VAT_COLL) VAT_COLL from DESCO.MOD_PREPAID_DATA PD 
            where PD.YEAR = $year and PD.MONTH = $month
            group by PD.YEAR, PD.MONTH, PD.TARIFF
            order by PD.TARIFF";
          
    $parsed = oci_parse($connection, $sql);
    oci_execute($parsed);
    oci_fetch_all($parsed, $resultSet, 0, -1, OCI_FETCHSTATEMENT_BY_ROW	+ OCI_ASSOC);
    oci_free_statement($parsed);
    return $resultSet;
  }
?>