<?php
require_once( dirname(__DIR__) . "/Database.php" );

$output = [
	"state" => "failure",
	"info" => "Not enough arguments for function.",
	"data" => ""
];
if( isset($_POST['epost']) && isset($_POST['parool']) && 
	isset($_POST['eesnimi']) && isset($_POST['perenimi']) && isset($_POST['telefon']) )
{
	$Connection = new \Back\Connection("142.93.135.89", "editor", "mnJfFNl8mF0ptbIE", "veebirakendus");
	$DatabaseHandler = new \Back\DatabaseHandler($Connection);

	// TO-DO: CHECK IF SUCH ACCOUNT EXISTS!!!

	$result = $DatabaseHandler->create_account($_POST['epost'], $_POST['parool'], 
			$_POST['eesnimi'], $_POST['perenimi'], $_POST['telefon']);
	if( $result ){
		$output["state"] = "success";
		$output["info"] = "";
		$output["data"] = json_encode($result);
	} else {
		$output["info"] = "Account creation failed ";
		$output["data"] = json_encode($result);
	}
}


echo json_encode($output);