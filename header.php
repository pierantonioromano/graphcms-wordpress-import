<?php

//Config
require 'vendor/autoload.php';
include("config.php");

?>
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>GraphCMS Wordpress Import Tool</title>
	<meta name="description" content="A simple tool for migrating content from Wordpress to GraphCMS. This tool can be easily customized for others headless CMS">
	<meta name="author" content="Pier Antonio Romano">

	<link rel="icon" href="favicon.ico">
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
	<script src="assets/js/core.js"></script>
	<link rel="stylesheet" href="assets/css/dist/styles.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Code&display=swap" rel="stylesheet">

</head>

<body class="bg-black pt-24 text-white">

	<div class="header fixed bg-black top-0 left-0 w-full border-b border-gray-700 p-6 z-10">
		<h1 class="text-3xl text-white font-bold"><a href="index.php">GraphCMS Wordpress Import Tool</a></h1>
	</div>