<?php	
  $conn = mysql_connect('localhost','desco','mm12*12MM') or trigger_error("SQL", E_USER_ERROR);
  $db = mysql_select_db('test',$conn) or trigger_error("SQL", E_USER_ERROR);
  
  $searchName = $_GET['name'];
  $searchDesignation = $_GET['designationSelector'];
  $searchParam = false;  

  //Number of Data Rows Calculation  
  $pagingSql = "SELECT COUNT(*) FROM contact_emp";
  if(strlen($searchDesignation) > 0 && $searchDesignation!= 'All'){
    $pagingSql .= " WHERE designation LIKE '".$searchDesignation."%'";
    $searchParam = true;  
  }
  if(strlen($searchName) > 0 ){
    if(!$searchParam){
      $pagingSql .= " WHERE name LIKE '%".$searchName."%'";
      $searchParam = true;
    }
    else{
      $pagingSql .= " AND name LIKE '%".$searchName."%'";
    }     
  }
	
  $result = mysql_query($pagingSql, $conn) or trigger_error("SQL", E_USER_ERROR);
	$r = mysql_fetch_row($result);
	$numrows = $r[0];
	$rowsperpage = 30;
	$totalpages = ceil($numrows / $rowsperpage);

	if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])){  
		$currentpage = (int) $_GET['currentpage'];   
	} 
  else{     
		$currentpage = 1;
	}  
  if ($currentpage > $totalpages){   	  
		$currentpage = $totalpages;
  } 	
  if ($currentpage < 1) {  		 
		$currentpage = 1;
	} 	 
  $offset = ($currentpage - 1) * $rowsperpage;

  //Data Section
	$dataSql = "SELECT name, designation, contact_no, email_id, office_name FROM contact_emp";

  $searchParam = false;
  if(strlen($searchDesignation) > 0 && $searchDesignation != 'All'){
    $dataSql .= " WHERE designation LIKE '".$searchDesignation."%'";
    $searchParam = true;  
  }
  if(strlen($searchName) > 0 ){
    if(!$searchParam){
      $dataSql .= " WHERE name LIKE '%".$searchName."%'";
      $searchParam = true;
    }
    else{
      $dataSql .= " AND name LIKE '%".$searchName."%'";
    }     
  }
  $dataSql .=  " ORDER BY department,position ASC LIMIT ".$offset.',' .$rowsperpage;
 
	$result = mysql_query($dataSql, $conn) or trigger_error("SQL", E_USER_ERROR);
  $dataTable = "";
  while ($list = mysql_fetch_assoc($result)){ 			  	   
    $dataTable .= "<tr class='noselect'>"; 
    $dataTable .= "<td><span style='font-size:12px'>". $list['name'] . "</span></td>"; 
    $dataTable .= "<td><span style='font-size:12px'>" . $list['designation'] . "</span></td>"; 
    $dataTable .= "<td><span style='font-size:12px'>" . $list['email_id'] . "</span></td>";  
    $dataTable .= "<td><span style='font-size:12px'>" . $list['office_name'] . "</span></td>"; 
    $dataTable .= "<td><span style='font-size:12px'>0" . $list['contact_no'] . "</span></td>";
    $dataTable .= "</tr>";
  }

  //Pagination Section

  $range = 3;
  $paginationTable = "";
  if($currentpage > 1){  
    $paginationTable .= " <a href='{$_SERVER['PHP_SELF']}?designationSelector=$searchDesignation&name=$searchName&searchBtn=Search&currentpage=1'><b>First</b></a> "; 			       		   
    $prevpage = $currentpage - 1;
    $paginationTable .= " <a href='{$_SERVER['PHP_SELF']}?designationSelector=$searchDesignation&name=$searchName&searchBtn=Search&currentpage=$prevpage'><b>Previous</b></a>";    
  }
  for($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++){   
    if (($x > 0) && ($x <= $totalpages)){    
      if ($x == $currentpage){
        $paginationTable .= " [<b>$x</b>] ";  
      }
      else{
        $paginationTable .= " <a href='{$_SERVER['PHP_SELF']}?designationSelector=$searchDesignation&name=$searchName&searchBtn=Search&currentpage=$x'>$x</a> ";        
      } 				  
    }
  }
  if ($currentpage != $totalpages){
    $nextpage = $currentpage + 1;
    $paginationTable .= " <a href='{$_SERVER['PHP_SELF']}?designationSelector=$searchDesignation&name=$searchName&searchBtn=Search&currentpage=$nextpage'><b>Next</b></a> ";
    $paginationTable .= " <a href='{$_SERVER['PHP_SELF']}?designationSelector=$searchDesignation&name=$searchName&searchBtn=Search&currentpage=$totalpages'><b>Last</b></a> ";
  }
  mysql_close($conn);
?>
<style>
  	.noselect {
  -webkit-touch-callout: none; /* iOS Safari */
    -webkit-user-select: none; /* Safari */
     -khtml-user-select: none; /* Konqueror HTML */
       -moz-user-select: none; /* Old versions of Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently supported by Chrome, Edge, Opera and Firefox */
}
</style>