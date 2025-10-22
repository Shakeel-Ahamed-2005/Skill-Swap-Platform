<?php
session_start();
session_unset();
session_destroy();
header('Location: /skillswap/index.php');
exit;
