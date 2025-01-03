<?php
session_start();
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

$db = new Database();
$auth = new Auth($db->getConnection());

// Cerrar sesión
$auth->logout();

// Redireccionar al login
header('Location: login.php');
exit; 