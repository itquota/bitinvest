<?php
session_start();
require("captcha.php");
$_SESSION['captcha'] = simple_php_captcha();
$imgc_captcha = $_SESSION['captcha']['image_src'];
$code = $_SESSION['captcha']['code'];
$cap_data = array('image'=>$imgc_captcha,'code'=>$code);
print_r($cap_data);exit;

