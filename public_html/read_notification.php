<?php
	require_once "utils.php";
	check_logined();

	if (!isset_post_fields("id"))
		exit();

	$id = $_POST["id"];

	sql_query("UPDATE notifications SET READED=1 WHERE ID=$id");
?>