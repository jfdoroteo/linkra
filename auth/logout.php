<?php
session_start(); session_unset(); session_destroy();
header('Location:/linkra/auth/login.php'); exit();
