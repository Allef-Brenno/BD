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
    if(isset($_POST['Voltar'])){
        header("Location: ../perfil/index.php");
        exit;
    }
?>

<?php
    if(isset($_POST['visitar_usuario'])){
        $id_p_s = $_POST['id_pessoa'];
        $_SESSION['id_usuario_pesquisado'] = $id_p_s;
        header("Location: ../perfil_visitado/index.php");
        exit;
    }

    if(isset($_POST['aceitar_usuario'])){
        $u->conectar();
        $id_pessoa = $_POST['id_pessoa'];
        $u->aceitarSolicitacao($id_user, $id_pessoa);
        $u->notificarSeguidor($id_user, $id_pessoa);
        $u->removerNotificacao($id_user, $id_pessoa);
    }

    if(isset($_POST['recusar_usuario'])){
        $u->conectar();
        $id_pessoa = $_POST['id_pessoa'];
        $u->recusarSolicitacao($id_user, $id_pessoa);
        $u->removerNotificacao($id_user, $id_pessoa);
    }


    
?>

<!DOCTYPE HTML>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Solicitacoes</title>
    </head>

    <body>
        <div>
            <form method = "post">
                <input type = "submit" name = "Voltar" value = "Home" class = "btn">
            </form>
        </div>

        <h3>Lista De Solicitações</h3>
        <hr>
            <?php
                $u->conectar();
                $retornoS = $u->retornarSolicitacoes($id_user);
                $quantidadeResultados = $retornoS->rowCount();

                print("Você tem $quantidadeResultados Solicitações!");

                while($exibirRetorno = $retornoS->fetch()){
                    $id_pedido = $exibirRetorno['1'];
                    $apelido_pedido = $u->apelidoUsuario($id_pedido);

                    print("<hr>$id_pedido<br>");
                    print("@$apelido_pedido<br>");

                    ?>
                        <!--Botão para ir ao usuario selecionado -->
                    <form method="post"> 
                        <?php //Armazedando o id de cada postagem em um array?>  
                        <input type="hidden" name = "id_pessoa" value= "<?php echo $id_pedido; ?>" >
                        <input type="submit" name = "visitar_usuario" value = "Visitar" class="btn">
                        <br>
                        <input type="submit" name = "aceitar_usuario" value = "Aceitar" class="btn">
                        <input type="submit" name = "recusar_usuario" value = "Recusar" class="btn">
                    </form>
                    <?php
                }

            ?>
    </body>

</html>