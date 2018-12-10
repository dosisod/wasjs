<?php

class memory {
	private $shm_size;
	private $shm_key;
	private $shm_id;
	private $shm_used=0;
	
	public function __construct() {
		$this->shm_size=16*1024; //makes 16kb memory cache
		$this->shm_key=ftok(__FILE__,"t"); //shared memory key
		$this->shm_id=shmop_open($this->shm_key, "w", 0644, $this->shm_size);
		$this->shm_used=0;

		$current=str_replace("\0", "", shmop_read($this->shm_id,0,0)); //read and removes null bytes
		$return=json_decode($current);
		if (($return==$current)||(json_last_error()!==JSON_ERROR_NONE)) { $this->reset(); } //reset if json is bad
	}
	function reset() {
		shmop_delete($this->shm_id);
		shmop_close($this->shm_id);
		shmop_open($this->shm_key, "c", 0644,$this->shm_size);
		$this->shm_id=shmop_open($this->shm_key, "w", 0644, $this->shm_size);
		$this->update_arr(array());
	}
	function update_str($str) { $this->shm_used=shmop_write($this->shm_id,$str,0); }
	
	function update_arr($arr) { $this->shm_used=shmop_write($this->shm_id,json_encode($arr),0); }
	
	function read() { return str_replace("\0", "", shmop_read($this->shm_id,0,$this->shm_used)); }
	
	function append(array $arr) {
		$tmp=json_decode($this->read());
		array_push($tmp,$arr);
		$this->update_arr($tmp);
	}
}

$bits=17; //forces the client to get a POW with n leading bits
$mem=new memory;

if (isset($_POST["challenge"]) && !isset($_POST["pow"]) && isset($_POST["file"])) { //if user requests challenge
	$bytes=random_bytes(32);
	$challenge=base64_encode($bytes); //create challenge
	$arr=array("bits"=>$bits,"challenge"=>$challenge);
	$mem->append($arr);
	echo json_encode($arr); //returns json obj to client
}
else if (isset($_POST["challenge"]) && isset($_POST["pow"]) && isset($_POST["file"])) { //if user completed challenge
	//
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

?>