<?php
$db = new mysqli('localhost', 'root', '', 'studypath_db');
header('Content-Type: application/json');

// Menangkap data dari $_POST maupun input stream
parse_str(file_get_contents('php://input'), $parsed_input);
$in = array_merge($_POST, $parsed_input);
