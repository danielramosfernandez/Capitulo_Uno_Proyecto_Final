<?php
session_start();

//^En este archivo se destruy la sesión
$_SESSION = [];

//^Concretamente en esta función
session_destroy();

//^Y después se te reenvia al inicio
header("Location: inicio.php");
exit;
