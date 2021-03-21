<?php
ini_set('display_errors', 1); 

// Database login information
$host = '172.16.8.21';
$port = '5432';
$dbname = 'NHIRD';
$user = 'deltadrc';
$password = 'deltadrc';

// Connet to our postgersql database
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// If connection ois error, then return it
if (!$conn) {
	echo "Not connected : " . pg_error();
	exit;
}

// Get the table name
$table = "r01_cd";
// Get the table year
$yearArray = ["2012"];
// Get the query type and user input
$FUNC_TYPE_query = ["小兒科"];
$ICD_OP_CODE_query = "";
$ACODE_ICD9_query = "";
$CURE_ITEM_query = "";
$ICD9CM_CODE_query = "";
$BED_DAY_query = "";

$sql_hospital = array();
$sql_residence = array();

// 門診治療檔 CD
if ($table == "r01_cd") {
    foreach ($yearArray as $year) {
        // Set sql statements for searching data by hospital place
        array_push($sql_hospital, 
                    "SELECT hosb2012.area_no_h, r01_cd".$year.".id_sex, r01_cd".$year.".id_birthday, r01_cd".$year.".func_date 
                        FROM r01_cd".$year." INNER JOIN hosb2012 
                        ON r01_cd".$year.".hosp_id = hosb2012.hosp_id WHERE");
        // Set sql statements for searching data by people residence
        array_push($sql_residence, 
                    "SELECT r01_id2012.reg_zip_code_decode, r01_cd".$year.".id_sex, r01_cd".$year.".id_birthday, r01_cd".$year.".func_date 
                        FROM r01_cd".$year." INNER JOIN r01_id2012 
                        ON r01_cd".$year.".id = r01_id2012.id WHERE");
	}
	// Create an array to store query content
	$query = array();
	if ($FUNC_TYPE_query != "") {
		$FUNC_TYPE = " (";
		for ($i = 0; $i < sizeof($FUNC_TYPE_query); $i++) {
			$FUNC_TYPE = $FUNC_TYPE."func_type = '".$FUNC_TYPE_query[$i]."'";
			if ($i != sizeof($FUNC_TYPE_query)-1) $FUNC_TYPE = $FUNC_TYPE." OR ";
		}
		$FUNC_TYPE = $FUNC_TYPE.")";
		array_push($query, $FUNC_TYPE);
	}
	if ($ICD_OP_CODE_query != "") {
		$ICD_OP_CODE = " (";
		for ($i = 0; $i < sizeof($ICD_OP_CODE_query); $i++) {
			$ICD_OP_CODE = $ICD_OP_CODE."icd_op_code = '".$ICD_OP_CODE_query[$i]."'";
			if ($i != sizeof($ICD_OP_CODE_query)-1) $ICD_OP_CODE = $ICD_OP_CODE." OR ";
		}
		$ICD_OP_CODE = $ICD_OP_CODE.")";
		array_push($query, $ICD_OP_CODE);
	}
	if ($ACODE_ICD9_query != "") {
		$ACODE_ICD9 = " (";
		for ($i = 0; $i < sizeof($ACODE_ICD9_query); $i++) {
			$ACODE_ICD9 = $ACODE_ICD9."(acode_icd9_1 LIKE '".$ACODE_ICD9_query[$i]."%' OR acode_icd9_2 LIKE '".$ACODE_ICD9_query[$i]."%' OR acode_icd9_3 LIKE '".$ACODE_ICD9_query[$i]."%')";
			if ($i != sizeof($ACODE_ICD9_query)-1) $ACODE_ICD9 = $ACODE_ICD9." OR ";
		}
		$ACODE_ICD9 = $ACODE_ICD9.")";
		array_push($query, $ACODE_ICD9);
	}
	if ($CURE_ITEM_query != "") { 
		$CURE_ITEM = " (";
		for ($i = 0; $i < sizeof($CURE_ITEM_query); $i++) {
			$CURE_ITEM = $CURE_ITEM."(cure_item_no1 = '".$CURE_ITEM_query[$i]."' OR cure_item_no2 = '".$CURE_ITEM_query[$i]."' OR cure_item_no3 = '".$CURE_ITEM_query[$i]."' OR cure_item_no4 = '".$CURE_ITEM_query[$i]."')";
			if ($i != sizeof($CURE_ITEM_query)-1) $CURE_ITEM = $CURE_ITEM." OR ";
		}
		$CURE_ITEM = $CURE_ITEM.")";
		array_push($query, $CURE_ITEM);
	}
}

// Complete sql statements
for ($i = 0; $i < sizeof($yearArray); $i++) {
	for ($j = 0; $j < sizeof($query); $j++) {
		$sql_hospital[$i] = $sql_hospital[$i].$query[$j];
		$sql_residence[$i] = $sql_residence[$i].$query[$j];
		if ($j != sizeof($query)-1) {
			$sql_hospital[$i] = $sql_hospital[$i]." AND";
			$sql_residence[$i] = $sql_residence[$i]." AND";
		}
		else {
			$sql_hospital[$i] = $sql_hospital[$i].";";
			$sql_residence[$i] = $sql_residence[$i].";";
		}
	}
}

echo $sql_hospital[0];
echo "<br>";

// Send the query
for ($i = 0; $i < sizeof($yearArray); $i++) {
    if (!$response_hospital[$i] = pg_query($conn, $sql_hospital[$i])) {
        echo "A query error occured.\n";
        exit;
    }
    if (!$response_residence[$i] = pg_query($conn, $sql_residence[$i])) {
        echo "A query error occured.\n";
        exit;
    }
}

// Echo the response
for ($i = 0; $i < sizeof($yearArray); $i++) {
    while ($row = pg_fetch_row($response_hospital[$i])) {
        foreach ($row as $item) {
            echo $item . " ";
        }
    }
}


// $queryFuncType = "牙科";
// $queryAcodeICD9 = "";
// $queryCureItem = "";
// $queryIcdOpCode = "";

// // Create an array to store query content
// $query = array();
// if($queryFuncType != "") array_push($query, " func_type = '$queryFuncType'");
// if($queryAcodeICD9 != "") array_push($query, " acode_icd9_1 LIKE '$queryAcodeICD9%' OR acode_icd9_2 LIKE '$queryAcodeICD9%' OR acode_icd9_3 LIKE '$queryAcodeICD9%'");
// if($queryCureItem != "") array_push($query, " cure_item_no1 = '$queryCureItem' OR cure_item_no2 = '$queryCureItem' OR cure_item_no3 = '$queryCureItem' OR cure_item_no4 = '$queryCureItem'");
// if($queryIcdOpCode != "") array_push($query, " icd_op_code = '$queryInput'");

// // Sql statements
// $sql_hospital = "SELECT hosb2012.area_no_h, r01_cd2012.id_sex, r01_cd2012.id_birthday, r01_cd2012.func_date
// 				FROM r01_cd2012 INNER JOIN hosb2012 
// 				ON r01_cd2012.hosp_id = hosb2012.hosp_id WHERE ";

// for($i = 0; $i < sizeof($query); $i++) {
// 	$sql_hospital = $sql_hospital . $query[$i];
// 	if($i != sizeof($query)-1) 
// 		$sql_hospital = $sql_hospital . " AND";
// 	else 
// 		$sql_hospital = $sql_hospital . ";";
// }

// // Send the query
// if (!$response_hospital = pg_query($conn, $sql_hospital)) {
// 	echo "A query error occured.\n";
// 	exit;
// }

// $count = 0;
// while ($row = pg_fetch_row($response_hospital)) {
//     echo $count. ".[ ";
//     foreach ($row as $item) {
//         echo $item . " ";
//     }
// 	echo "]<br>";
//     $count++;
// }

?>