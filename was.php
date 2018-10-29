<?php
//still need to generate challenge for user and store it
$hash=hash("sha512",$_POST["str"].$_POST["pow"]);
$out="";
for ($i=0;$i<strlen($hash);$i++){ //loop through each char of hex digest
	$out=$out.str_pad(base_convert($hash[$i],16,2),4,"0",STR_PAD_LEFT); //create 1s and 0s
}

/*
$dir=$_SERVER["HOME"]."/Downloads/"; //file storage must be out of docroot or user can navigate to it

$fn=$_POST["FILE"]; //get filename as string

if (strpos($fn,"../")===false) { //make sure there is no ".." in file path
	//do stuff
}
*/
?>
