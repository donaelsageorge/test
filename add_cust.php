<?php
$insertArray=array();
$errMsg="";$succMsg="";$err=0;
$insertArray['name']=isset($_POST['name'])?cleanInputField($_POST['name']):'';