<?php

class memory {
	private $shm_size;
	private $shm_key;
	private $shm_id;
	private $shm_used=0;
	
	public function __construct() {
		$this->shm_size=32*1024; //makes 32kb memory cache
		$this->shm_key=ftok(__FILE__,"t"); //shared memory key
		$this->shm_id=shmop_open($this->shm_key, "c", 0644, $this->shm_size);
		$this->shm_used=0;

		$current=str_replace("\0", "", shmop_read($this->shm_id,0,0)); //read and removes null bytes
		$ret=json_decode($current);
		if (($ret==$current)||(json_last_error()!==JSON_ERROR_NONE)) { $this->reset(); } //reset if json is bad
		else { $this->reset(); }
	}
	function reset() {
		shmop_delete($this->shm_id);
		shmop_close($this->shm_id);
		$this->shm_id=shmop_open($this->shm_key, "c", 0644, $this->shm_size);
		$this->update_arr(array());
	}
	function update_str($str) {
		$this->shm_used=shmop_write($this->shm_id, $this->safety($str), 0);
	}
	function update_arr($arr) {
		$this->shm_used=shmop_write($this->shm_id, $this->safety(json_encode($arr)), 0);
	}
	function read() {
		return str_replace("\0", "", shmop_read($this->shm_id,0,$this->shm_used));
	}

	function safety($str) { //makes sure if data is less then current size, append trailing zero bytes
		$len=strlen($str);
		if ($len<$this->shm_used) {
			shmop_write($this->shm_id,str_repeat("\0", ($this->shm_used-$len)), $len); //zero the extra data
		}
		return $str;
	}

	function contains($challenge) {
		$tmp=json_decode($this->read());
		foreach($tmp as $obj) {
			if ($obj->challenge==$challenge) { return TRUE; }
		}
		return FALSE;
	}
	
	function append(array $arr) {
		$tmp=json_decode($this->read());
		array_push($tmp,$arr);
		$this->update_arr($tmp);
	}

	function delete($challenge) {
		$tmp=json_decode($this->read());
		error_log(json_encode($tmp));
		foreach($tmp as $obj) {
			if ($obj->challenge==$challenge) { unset($tmp); }
		}
		error_log(json_encode($tmp));
		$tmp=array_values($tmp);
		$this->update_arr($tmp);
	}
}

$bits=4; //forces the client to get a POW with n leading bits
$mem=new memory;

if (isset($_POST["challenge"]) && !isset($_POST["pow"]) && isset($_POST["file"])) { //if user requests challenge
	$bytes=random_bytes(32);
	$challenge=base64_encode($bytes); //create challenge
	$arr=array("bits"=>$bits,"challenge"=>$challenge,"file"=>$_POST["file"]);
	$mem->append($arr);
	echo json_encode($arr); //returns json obj to client
}
else if (isset($_POST["challenge"]) && isset($_POST["pow"]) && isset($_POST["file"])) { //if user completed challenge
	die();
	//$challenge=$_POST["challenge"];
	error_log($_POST["challenge"]);
	//die();
	if ($mem->contains($chdllenge)) { //checks if challenge came from server
		$hex=hash("sha512",$challenge.$_POST["pow"]);
		$bin="";
		for ($i=0;$i<strlen($hex);$i++){ //loop through each char of hex digest
			$bin=$bin.str_pad(base_convert($hex[$i],16,2),4,"0",STR_PAD_LEFT); //create 1s and 0s
		}
		if (substr($bin,0,$bits)==str_repeat("0",$bits)) { //check if leading 0s is >= bits
			//file complete, download it
			$mem->delete($challenge);
		}
	}
	else {
		echo "challenge not found";
	}
}
else {
	if (isset($_GET["r"])) { $mem->reset(); }
	echo $mem->read()."<br>";
	echo TRUE;
}

/* not needed yet (escapes string and grabs file)
$dir=$_SERVER["HOME"]."/Downloads/"; //file storage must be out of docroot or user can navigate to it

$fn=$_POST["FILE"]; //get filename as string

if (strpos($fn,"../")===false) { //make sure there is no ".." in file path
	//do stuff
}
*/

?>