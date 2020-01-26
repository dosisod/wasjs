<?php
session_start();

//Modify these as needed

//root path to files (dont put in doc root)
$path="/absolute/path/to/files/";

//required bits of entropy per file type
$types=[
	"mp3" => 21,
	"mp4" => 21,
	"zip" => 19,
	"exe" => 19,
	"default" => 17
];

//end of modify

//if user requests challenge
if (isset($_POST["challenge"], $_POST["file"]) && !isset($_POST["pow"])) {
	$challenge=base64_encode(random_bytes(32));

	$_SESSION["challenge"]=$challenge;
	$_SESSION["file"]=$_POST["file"];

	$extension=pathinfo($_POST["file"], PATHINFO_EXTENSION);

	$_SESSION["bits"]=(
		$GLOBALS["types"][$extension]
		??
		$GLOBALS["types"]["default"]
	);

	echo json_encode([
		"bits" => $_SESSION["bits"],
		"challenge" => $challenge
	]);
}

//if user completed challenge
else if (isset($_POST["challenge"], $_POST["pow"], $_POST["file"])) {
	if (isset($_SESSION["challenge"], $_SESSION["file"])) {
		if ($_SESSION["challenge"]==$_POST["challenge"]) {
			$hexdigest=hash("sha512", $_POST["challenge"].$_POST["pow"]);
			$binary="";

			$max=intdiv(
				strlen($hexdigest) + 3,
				4
			);

			for ($i=0;$i<$max;$i++) {
				$binary.=str_pad(
					base_convert($hexdigest[$i], 16, 2),
					4,
					"0",
					STR_PAD_LEFT
				);
			}
			$bits=$_SESSION["bits"];

			//ensure there is enough leading bits
			if (substr($binary, 0, $bits)==str_repeat("0", $bits)) {
				//pow is done, make temp file

				$filename=hash("md5", random_bytes(64)).".php";

				make_file(
					$filename,
					$_SESSION["file"],
					$path
				);

				echo $filename;
			}
			else {
				echo "ERROR: POW is incorrect";
			}
		}
		else {
			echo "ERROR: POST and session data do not match";
		}
	}
	else {
		echo "ERROR: Invalid session data";
	}
}
else {
	echo "ERROR: Invalid POST request(s)";
}

function make_file($filename, $session_file, $path) {
	$template=file_get_contents("./template.php");

	$template=str_replace(
		["{FILENAME}", "{SESSION-FILE}", "{PATH}"],
		[
			"FILENAME" => $filename,
			"SESSION-FILE" => $session_file,
			"PATH" => $path
		],
		$template
	);

	file_put_contents(
		$filename,
		$template
	);
}

?>