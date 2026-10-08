<?php
session_start();
if(isset($_SESSION['user_rol'])){
return true;
	}
else{
return false;
}
?>
