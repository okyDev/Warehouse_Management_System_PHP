<?php
if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
    header("Location: main/menu.php"); // Redirige si ya está logueado
} else{
    header("Location: login.php");     // Redirige al login si no hay sesión
}
exit();
?>
