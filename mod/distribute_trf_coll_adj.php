<!DOCTYPE html>
<html>
    <head>   
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">    
        <title>MOD | DESCO</title>
    </head>
    <?php
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ALL);

        $year = $_POST['year'];
        $month = $_POST['month'];
        
        require('../../../../opt/config/connection.php');
        $connection_maria = $mysqli;

        $target_dir = "/var/www/html/mod/collection_adjustment_uploaded/";                     
        $fileType = strtolower(pathinfo(basename($_FILES["fileToUpload"]["name"]),PATHINFO_EXTENSION));
        $target_file = $target_dir ."tariff_coll_adj_".$month."_".$year.".".$fileType;

        $uploadOk = 1;
        // Allow certain file formats
        if($fileType != "xls" && $fileType != "xlsx") {
            echo "Sorry, only .xls and .xlsx files are allowed.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 1100000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "<br/>Sorry, your file was not uploaded.";
        }             
        // if everything is ok, try to upload file
        else {                                   
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "<br/>The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
            } else {
                echo "<br/>Sorry, there was an error uploading your file.";
            }
        }

        if($uploadOk ==1){
            require('../vendor/autoload.php');
            $excelFile;
            $testAgainstFormats = [\PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS, \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX];        
            
            $inputFileName = $target_file;

            $inputFileType="";
            try{
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($target_file, $testAgainstFormats);
            }catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
                die('File format not supported : '.$e->getMessage());
            }        
            try{
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);            

                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($inputFileName);

                $worksheet = $spreadsheet->getActiveSheet();
                
                $excelFile = $worksheet->toArray();                                             
            }catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
                die('Can not create reader : '.$e->getMessage());
            }
        }
        else{
            die("<br/>Collection Adjustment is not successfull.");
        }

    ?>        
    <body>
    </body>
    <?php
        $updateSql="";
        for ($row =1; $row <sizeof($excelFile); $row++) {                        
            $updateSql .="UPDATE desco.`ACTUAL_TRF_WISE_MOD` SET `collection_adjustment`=".$excelFile[$row][3]." WHERE `division_id`=".$excelFile[$row][0]. 
            " AND `tariff`='".$excelFile[$row][2]."' AND `year`=$year AND `month`=$month;";
        }
        $connection_maria ->multi_query($updateSql);
        $updateRowCount=1;
        while ($connection_maria->next_result()) // flush multi_queries
        {
            $updateRowCount++;
            if (!$connection_maria->more_results()) break;
        }

        echo "<br/>Collection Adjustment given in ".$updateRowCount." rows of ACTUAL_TRF_WISE_MOD table.<br/>";

        echo "<br/>YTD Collection Adjustment updated in ".updateActualYtdCollAdjustment($year, $month, $connection_maria)." rows of ACTUAL_TRF_WISE_MOD table.<br/>";

        echo "<br/> Monthly and YTD Collection Updated in ".updatePblshdTrfTabl($year, $month, $connection_maria)." rows of PUBLISHED_TRF_WISE_MOD table.<br/>";
        
        updatePublishedBC($year, $month, $connection_maria);

        echo "<br/><br/><button onclick=location.href='http://local.desco.org.bd/mod/mod_report_format.php' type='button'>Print MOD Report</button><br/><br/>";


        function updatePublishedBC($year, $month, $connection){
            $sql ="UPDATE desco.PUBLISHED_TRF_WISE_MOD SET bc_ratio = (IFNULL(net_collection,0)/IFNULL(net_bill,1))*100 WHERE year=$year AND month = $month";
            $connection -> query($sql);
            return;
        }
        function updatePblshdTrfTabl($year, $month, $connection){
            $sql = "UPDATE desco.PUBLISHED_TRF_WISE_MOD a JOIN desco.ACTUAL_TRF_WISE_MOD b ON a.division_id = b.division_id AND a.tariff =b.tariff AND a.year = b.year AND a.month =b.month
            SET a.net_collection = a.net_collection + IFNULL(b.collection_adjustment,0), a.ytd_collection = a.ytd_collection + IFNULL(b.collection_adjustment,0)
            WHERE a.year =$year AND a.month =$month";

            $connection -> query($sql);
            return $connection->affected_rows;
        }
        function updateActualYtdCollAdjustment($year, $month, $connection){
            if($month == 7)
               $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD SET ytd_coll_adjustment = IFNULL(collection_adjustment,0) WHERE year = $year AND month = $month";
            else if($month == 1)
               $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD a JOIN desco.ACTUAL_TRF_WISE_MOD b ON a.division_id = b.division_id AND a.tariff =b.tariff
                SET a.ytd_coll_adjustment = a.collection_adjustment + IFNULL(b.ytd_coll_adjustment,0)
                WHERE a.year =$year AND a.month =$month AND b.year = $year-1 AND b.month =12";
            else
               $sql = "UPDATE desco.ACTUAL_TRF_WISE_MOD a JOIN desco.ACTUAL_TRF_WISE_MOD b ON a.division_id = b.division_id AND a.tariff =b.tariff 
               SET a.ytd_coll_adjustment = a.collection_adjustment + IFNULL(b.ytd_coll_adjustment,0)
               WHERE a.year =$year AND a.month =$month AND b.year = $year AND b.month =$month-1";
            
            $connection -> query($sql);
            return $connection->affected_rows;
         }
    ?>   
</html>    