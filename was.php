<?php
session_start();

$bits=4; //forces the client to get a POW with n leading bits

if (isset($_POST["challenge"]) && !isset($_POST["pow"]) && isset($_POST["file"])) { //if user requests challenge
	$challenge=base64_encode(random_bytes(32)); //create challenge
	$arr=array("bits"=>$bits,"challenge"=>$challenge,"file"=>$_POST["file"]);

	$_SESSION["challenge"]=$challenge; //sets session data
	$_SESSION["file"]=$_POST["file"];
	$_SESSION["bits"]=$bits;
	error_log(json_encode($_SESSION));
	
	echo json_encode($arr); //returns json obj to client
}
else if (isset($_POST["challenge"]) && isset($_POST["pow"]) && isset($_POST["file"])) { //if user completed challenge
	if (isset($_SESSION["challenge"]) && isset($_SESSION["file"])) { //makes sure session data exists
		if ($_SESSION["challenge"]==$_POST["challenge"]) { //checks if challenge came from server
			$hex=hash("sha512",$_POST["challenge"].$_POST["pow"]);
			$bin="";
			for ($i=0;$i<strlen($hex);$i++){ //loop through each char of hex digest
				$bin=$bin.str_pad(base_convert($hex[$i],16,2),4,"0",STR_PAD_LEFT); //create 1s and 0s
			}
			if (substr($bin,0,$bits)==str_repeat("0",$bits)) { //check if leading 0s is >= bits
				//file complete, download it
				
				session_unset();
				session_destroy();
			}
			else {
				//POW is incorrect
			}
		}
		else {
			//session challenge and post challenge dont match
		}
	}
	else {
		//session data isnt set
	}
}

/* not needed yet (escapes string and grabs file)
$dir=$_SERVER["HOME"]."/Downloads/"; //file storage must be out of docroot or user can navigate to it

$fn=$_POST["FILE"]; //get filename as string

if (strpos($fn,"../")===false) { //make sure there is no ".." in file path
	//do stuff
}
*/

?>