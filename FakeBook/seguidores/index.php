<?php
    session_start();
    if(!isset($_SESSION['id_usuario'])){

        header("location: ../index.php");
        exit;
    }
    $id_user = $_SESSION['id_usuario'];
   
    require_once '../servidor/connection.php';
    $u = new connection;

?>

<?php
    if(isset($_POST['p_usuario'])){
        $id_p_s = $_POST['id_seguidor'];
        $_SESSION['id_usuario_pesquisado'] = $id_p_s;
        header("Location: ../perfil_visitado/index.php");
        exit;
    }
?>

<!DOCTYPE HTML>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Seguidores</title>
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="../images/twitter.ico" />
    </head>

    <body>

        <div id="container-menu">
            <div id = "retornar-inicio">
                <a href="../perfil/index.php">Home</a>
            </div>

            <div id = "sair-menu">
                <a href="../sair/index.php">Sair</a>
            </div>
        </div>

        <hr class = "hrMenu"> 

        <div id = "containerTitulo">
            <h3>Seguidores</h3>
        </div>

        
        <?php
            $u->conectar();
            $retornoS = $u->retornarSeguidores($id_user);
            $quantidadeResultados = $retornoS->rowCount();

            while($exibirRetorno = $retornoS->fetch()){
                $id_seguidor = $exibirRetorno[1];
                $apelido_seguidor = $u->apelidoUsuario($id_seguidor);

                ?>
                <div id = "containerSeguidores">
                    <!--Botão para ir ao usuario selecionado -->
                    <form method="post"> 
                        <?php //Armazedando o id de cada postagem em um array?> 
                        <input type="hidden" name = "id_seguidor" value= "<?php echo $id_seguidor; ?>" >
                        <input class = "InputVisitar" type="submit" name = "p_usuario" value = "Visitar">
                        <label><?php print("@$apelido_seguidor"); ?></label> 
                    </form>
                </div>
                <?php
            }

        ?>
    </body>

</html>
