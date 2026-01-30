<?php
// pages/logout.php

session_start();
session_destroy();
header("Location: mensajeria_cesar/pages/login.php");
exit();
?>