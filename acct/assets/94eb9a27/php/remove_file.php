<?php 
$uploadDir=$_POST['uploadDir'];
if(isset($_POST['file'])){
	$file = $uploadDir . $_POST['file'];
	if(file_exists($file)){
		unlink($file);
	}
}
?>