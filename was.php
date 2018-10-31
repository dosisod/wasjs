<?php

if (isset($_POST["c"])) { //if user requests challenge
	echo str_replace("=","",base64_encode(random_bytes(16))); //return a challenge string
}

/* not needed yet
$hash=hash("sha512",$_POST["str"].$_POST["pow"]);
$out="";
for ($i=0;$i<strlen($hash);$i++){ //loop through each char of hex digest
	$out=$out.str_pad(base_convert($hash[$i],16,2),4,"0",STR_PAD_LEFT); //create 1s and 0s
}
*/

/* not needed yet
$dir=$_SERVER["HOME"]."/Downloads/"; //file storage must be out of docroot or user can navigate to it

$fn=$_POST["FILE"]; //get filename as string

if (strpos($fn,"../")===false) { //make sure there is no ".." in file path
	//do stuff
}
*/
?>
