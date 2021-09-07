<?php
    session_start();
    unset($_SESSION['id_usuario']);
    unset($_SESSION['id_usuario_pesquisado']);
    unset($_SESSION['pesquisa']);
    header('Location:../index.php');
?>