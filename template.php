<?php

//delete the file so it cannot be clicked again
unlink("{FILENAME}");

session_start();
session_unset();
session_destroy();

//ensure there is no file traversal
$clean=basename("{SESSION-FILE}");
$fullpath="{PATH}".$clean;

if (!file_exists($fullpath)) {
	echo "ERROR: File not found";
	die();
}

$mime=finfo_file(
	finfo_open(FILEINFO_MIME_TYPE),
	$fullpath
);

header("Content-Disposition: attachment; filename=\"$clean;\"");
header("Content-Type: $mime");
header("Content-Length: filesize($fullpath)");

$file=fopen($fullpath, "rb");
fpassthru($file);

?>
