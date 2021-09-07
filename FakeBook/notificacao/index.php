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
    if(isset($_POST['visitar_usuario'])){
        $id_p_s = $_POST['id_visitar_usuario'];
        $_SESSION['id_usuario_pesquisado'] = $id_p_s;
        header("Location: ../perfil_visitado/index.php");
        exit;
    }

    
    if(isset($_POST['aceitar_usuario'])){
        $u->conectar();
        $id_pessoa = $_POST['id_visitar_usuario'];
        $u->aceitarSolicitacao($id_user, $id_pessoa);
        $u->notificarSeguidor($id_user, $id_pessoa);
        $u->removerNotificacao($id_user, $id_pessoa);
        $u->notificarAceitouSeguidor($id_user, $id_pessoa);
    }

    if(isset($_POST['recusar_usuario'])){
        $u->conectar();
        $id_pessoa = $_POST['id_visitar_usuario'];
        $u->recusarSolicitacao($id_user, $id_pessoa);
        $u->removerNotificacao($id_user, $id_pessoa);
    }


?>

<!DOCTYPE HTML>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Notificações</title>
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
            <h3>Notificações</h3>
        </div>

            <?php
                $u->conectar();
                $retornoN = $u->retornarNotificacao($id_user);
                $quantidadeResultados = $retornoN->rowCount();
                
                while($exibirRetorno = $retornoN->fetch()){

                    $id_pessoa_notificou = $exibirRetorno[2];
                    $id_postagem_notificado = $exibirRetorno[3];
                    $id_comentario_notificado = $exibirRetorno[6];
                    $mensagem_notificacao = $exibirRetorno[4];
                    $solicitacaoValor = $exibirRetorno[5];
                    $apelido_pessoa_notificou = $u->apelidoUsuario($id_pessoa_notificou);
                    $id_dono_post = $u->retornarIdPessoaPostagem($id_postagem_notificado);
                    
                    ?><div id="containerNotificacao"> <?php

                        if($solicitacaoValor == 0 && empty($id_postagem_notificado)){
                            //Se não é uma solicitação e não é uma postagem, então alguém seguiu o usuário.
                            ?>
                                <!--Botão para ir ao usuario selecionado -->
                            <form method="post">  
                                <input type="hidden" name = "id_visitar_usuario" value= "<?php echo $id_pessoa_notificou; ?>" >
                                <div id="NotificacaoInput">
                                    <input class = "InputVisitar" type="submit" name = "visitar_usuario" value = "Visitar">
                                </div>    
                            </form>
                            <?php
                        }

                        if($solicitacaoValor == 1 && empty($id_postagem_notificado)){
                            ?>
                            <form method="post"> 
                                <input type="hidden" name = "id_visitar_usuario" value= "<?php echo $id_pessoa_notificou; ?>" >
                                <div id="NotificacaoInput">
                                    <input class = "InputVisitar" type="submit" name = "visitar_usuario" value = "Visitar">
                                </div>
                                <div id="NotificacaoInputAceitar">
                                    <input class = "InputAceitar" type="submit" name = "aceitar_usuario" value = "Aceitar">
                                </div>

                                <div id="NotificacaoInputRecusar">
                                    <input class = "InputRecusar" type="submit" name = "recusar_usuario" value = "Recusar">
                                </div>
                            </form>
                            <?php
                        }

                        if(!empty($id_postagem_notificado) && empty($id_comentario_notificado)){
                            //Se não é uma solicitação e não é uma postagem, então alguém seguiu o usuário.
                            ?>
                                <!--Botão para ir ao usuario selecionado -->
                            <form method="post">  
                                <input type="hidden" name = "id_visitar_usuario" value= "<?php echo $id_dono_post; ?>" >
                                <div id="NotificacaoInput">
                                    <input class = "InputVisitar" type="submit" name = "visitar_usuario" value = "Ver">
                                </div>    
                            </form>
                            <?php
                        }

                        if(!empty($id_comentario_notificado)){
                            //Se não é uma solicitação e não é uma postagem, então alguém seguiu o usuário.
                            ?>
                                <!--Botão para ir ao usuario selecionado -->
                            <form method="post">  
                                <input type="hidden" name = "id_visitar_usuario" value= "<?php echo $id_dono_post; ?>" >
                                <div id="NotificacaoInput">
                                    <input class = "InputVisitar" type="submit" name = "visitar_usuario" value = "Ver">
                                </div>  
                            </form>
                            <?php
                        }

                        if(!empty($id_postagem_notificado)){

                            $id_dono_post = $u->retornarIdPessoaPostagem($id_postagem_notificado);
                            $dono_post = $u->retornarPerfil($id_dono_post);
                            $dono_post_f =  $dono_post->fetch();
                            $privacidade_dono_post = $dono_post_f['3'];

                            if($u->verificarSeSegue($id_user, $id_dono_post)){

                                ?><label><?php print("@$apelido_pessoa_notificou $mensagem_notificacao");?> 
                                <br>O perfil é seguido por você!</label><?php
                        
        
                            }else if($privacidade_dono_post == 0){

                                ?><label><?php print("@$apelido_pessoa_notificou $mensagem_notificacao");?> 
                                <br>O perfil é público</label><?php
                            }

                        }else{
                            ?><label><?php print("@$apelido_pessoa_notificou $mensagem_notificacao");?></label><?php
                        }

                    ?></div><?php
                }
            ?>
    </body>
</html>