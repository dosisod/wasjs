<?php

$bits=17; //forces the client to get a POW with n leading bits

$shm_key=ftok(__FILE__,"t"); //shared memory key

//

if (isset($_POST["challenge"])) { //if user requests challenge
	$bytes=random_bytes(32); //gets random bytes
	$challenge=base64_encode($bytes); //create challenge
	echo json_encode(array("bits"=>$bits,"challenge"=>$challenge)); //returns json obj to client
}

/* not needed yet (converts hex digest to binary str)
$hash=hash("sha512",$_POST["str"].$_POST["pow"]);
$out="";
for ($i=0;$i<strlen($hash);$i++){ //loop through each char of hex digest
	$out=$out.str_pad(base_convert($hash[$i],16,2),4,"0",STR_PAD_LEFT); //create 1s and 0s
}
*/

/* not needed yet (escapes string and grabs file)
$dir=$_SERVER["HOME"]."/Downloads/"; //file storage must be out of docroot or user can navigate to it

$fn=$_POST["FILE"]; //get filename as string

if (strpos($fn,"../")===false) { //make sure there is no ".." in file path
	//do stuff
}
*/

function shm_id(int $index) { //returns id or false
	return @shmop_open($index, "a", 0644, 0) or false;
}

function shm_get(int $index) {
	$tmpid=shm_id($index);
	if ($tmpid) {
		return @shmop_read($tmpid, 0, 0);
	}
	else {
		return false;
	}
}

function shm_put(int $index) {
	$tmpid=shm_id($index);
	if ($tmpid) {
		shm_write($tmpid, data, 0);
	}
}

?>
