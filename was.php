<?php
session_start();

//Modify these as needed

$path="/absolute/path/to/files/"; //root path to files (dont put in doc root)
$bits=17; //forces the client to get a POW with n leading bits

//

if (isset($_POST["challenge"]) && !isset($_POST["pow"]) && isset($_POST["file"])) { //if user requests challenge
	$challenge=base64_encode(random_bytes(32)); //create challenge
	$arr=array("bits"=>$bits,"challenge"=>$challenge,"file"=>$_POST["file"]);

	$_SESSION["challenge"]=$challenge; //sets session data
	$_SESSION["file"]=$_POST["file"];
	$_SESSION["bits"]=$bits;
	
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
				//pow is done, make temp link file

				//create random url name
				$fn=hash("md5", random_bytes(64)).".php";

				//code to be ran on the temp file
				$file='<?php'.PHP_EOL.
				'unlink("'.$fn.'");'.PHP_EOL.
				'session_start();'.PHP_EOL.
				'$file="'.$_SESSION["file"].'";'.PHP_EOL.
				'session_unset();'.PHP_EOL.
				'session_destroy();'.PHP_EOL.
				'$clean=basename($file);'.PHP_EOL.
				'$fullpath="'.$path.'".$clean;'.PHP_EOL.
				'if (!file_exists($fullpath)) die();'.PHP_EOL.
				'$mime=finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullpath);'.PHP_EOL.
				'header("Content-Disposition: attachment; filename=$clean;");'.PHP_EOL.
				'header("Content-Type: $mime");'.PHP_EOL.
				'header("Content-Length: filesize($fullpath)");'.PHP_EOL.
				'$f=fopen($fullpath, "rb");'.PHP_EOL.
				'fpassthru($f);'.PHP_EOL.
				'?>';
				
				file_put_contents($fn, $file); //output to file
				echo $fn; //send the url to be downloaded by the client
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

?>