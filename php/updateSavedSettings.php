<?php
	session_start();
	if ($_GET['test'] == "" || !isset($_GET['test'])){ //IF NO TEST TYPE HAS BEEN SELECTED
		header('Location: ../userSettings.php?err=5');
		exit;
	}


	$_SESSION['updatingSavedSettings']=true;
	header("Location: ../soundSettings.php?test=".$_GET['test']);
?>
