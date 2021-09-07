<?php
    session_start();
    if(!isset($_SESSION['id_usuario'])){

        header("location: ../index.php");
        exit;
    }
    $id_user = $_SESSION['id_usuario'];
    $id_user_pesquisado = $_SESSION['id_usuario_pesquisado'];

    require_once '../servidor/connection.php';
    $u = new connection;

    if($id_user == $id_user_pesquisado){
        header("location: ../perfil/index.php");
        exit;
    }

    $u->conectar();

?>

<!DOCTYPE HTML>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Perfil</title>
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="../images/twitter.ico" />
    </head>

    <body>
        
        <!--Campo de pesquisa de usuário -->
        <div id="container-menu">

            <div id = "retornar-inicio">
                <a href="../perfil/index.php">Home</a>
            </div>

            <div id = "itens-menu">

                <form method="post" class="hMenu">
                    <input class ="inputMenu" type="text" name="pesquisar" class = "campo" maxlength="25" required placeholder="Pesquisar">
                    <input class = "inputMenuImg" type="image" src="../images/search.png" alt="submit" value = "pesquisar">
                    <?php
                        if (isset($_POST['pesquisar'])){
                            $pesquisa = $_POST['pesquisar'];
                            $_SESSION['pesquisa'] = $pesquisa;
                            header("location: ../pesquisa/index.php");
                            exit;
                        }
                    ?>
                </form>
            </div>

            <div id = "sair-menu">
                <a href="../sair/index.php">Sair</a>
            </div>
        </div>
        <hr class = "hrMenu">   

        <!--Final do campo de pesquisa de usuário -->
        <?php
            if (isset($_POST['bloquear'])){
                $u->conectar();
                $u->bloquear($id_user, $id_user_pesquisado);

                $u->fazerPararDeSeguir($id_user, $id_user_pesquisado);
                $u->pararSeguir($id_user, $id_user_pesquisado);
                
                $u->deletarComentarioBloqueado($id_user, $id_user_pesquisado);
                $u->deletarTopicoBloqueado($id_user, $id_user_pesquisado);
                $u->removerSolicitacao($id_user, $id_user_pesquisado);
                $u->removerNotificacaoBloqueado($id_user, $id_user_pesquisado);
            }

            if (isset($_POST['desbloquear'])){
                $u->conectar();
                $u->desbloquear($id_user, $id_user_pesquisado);
                
            }
        ?>     

        <div id = "container-perfil" >
            <!--Botão de bloquear -->
            <?php 
            $u->conectar();
            if($u->verificarBloqueado($id_user, $id_user_pesquisado)){
                //Se o usuário está bloqueado, mostre o botão de desbloquear.
                ?>
                <form method="post">
                    <div id ="Bloquear">
                        <input class = "InputBloquear" type="submit" name = "desbloquear" value="Desbloquear">
                    </div>
                </form>
                <?php 
            }else{
                //Se o usuário não está bloqueado, mostre o botão de bloquear.
                ?>
                <form method="post">
                    <div id ="Bloquear">
                        <input class = "InputBloquear" type="submit" name = "bloquear" value="Bloquear">
                    </div>    
                </form>

                <?php 
            }
            ?>

            <div id = "itens-perfil">
            <?php
                $u->conectar();
                $retorno = $u->retornarPerfil($id_user_pesquisado);

                $exibirPerfil = $retorno->fetch();
                $nome = $exibirPerfil['Nome'];
                $apelido = $exibirPerfil['Apelido'];
                $biografia = $exibirPerfil['Biografia'];
                $numeroSeguidor = $exibirPerfil['Numero_seguidores'];
                $privacidade = $exibirPerfil['Privacidade'];
                $foto_do_perfil = $exibirPerfil['Foto'];

                if(!empty($foto_do_perfil)){
                    ?><img class = "perfilImg" src = "<?php print("$foto_do_perfil"); ?>" alt = "Foto_de_Perfil"> <?php

                }else{
                    ?><img class = "perfilImg" src = "../images/sem-foto.png" alt = "Foto_de_Perfil"> <?php
                }
                
                ?>
                <h3><?php print("$nome"); ?></h3>
                <h4><?php print("@$apelido"); ?></h4>
                <p><?php print("$biografia");?> </p>
                <p><?php print("$numeroSeguidor Seguidores"); ?></p>
                <?php

                //Exibindo se a pessoa segue o usuario.
                $retornoSegue = $u->verificarSeSegue($id_user_pesquisado, $id_user);
                if($retornoSegue){
                    ?><p><?php print("$apelido Segue Você!"); ?></p><?php
                }
            ?>
            </div>

            <?php
                //Código para o botão de seguir
                $u->conectar();
                $retornoSegue = $u->verificarSeSegue($id_user, $id_user_pesquisado);
                $existeSolicitacao = $u->existeSolicitacao($id_user, $id_user_pesquisado);
                $userBloqueado = $u->verificarBloqueado($id_user_pesquisado, $id_user);
                $pessoaBloqueado = $u->verificarBloqueado($id_user, $id_user_pesquisado);
                $euBloquado = $u->verificarBloqueado($id_user_pesquisado, $id_user);
                if(!($userBloqueado) && !($pessoaBloqueado)){
                    //Só é possível seguir se não estiver bloqueado.
                    if(!($retornoSegue)){
                        if($existeSolicitacao){
                            ?>
                            <form method="post">
                                <div id ="removerSolicitacao">
                                    <input class = "InputRemoverSolicitacao" type="submit" name="RemoverSolicitacao" value="Remover Solicitação">
                                </div>
                            </form>
                            <?php
                        }else{
                            /*Se o usuario ainda não segue a pessoa, e nenhuma solicitação foi enviada
                            o botão de seguir irá aparecer. */
                            ?>
                            <form method="post">
                                <div id="seguir">
                                    <input class = "InputSeguir" type="submit" name="Seguir" value="Seguir">
                                </div>
                            </form>
                            <?php
                        }
                    }else{
                        //Se o usuario já segue a pessoa, o botão de parar de seguir irá aparecer.
                        ?>
                        <form method="post">
                            <div id="deixarSeguir">
                                <input class = "InputDeixarSeguir" type="submit" name="deixar_Seguir" value="Parar de Seguir">
                            </div>    
                        </form>
                        <?php
                    }
                }
                //Botão de clickar em seguir
                if(isset($_POST['Seguir'])){
                    if(!($privacidade)){
                        //Perfil é público, então pode seguir direto.
                        $u->conectar();
                        $u->seguir($id_user, $id_user_pesquisado);
                        $u->notificarSeguidor($id_user_pesquisado, $id_user);
                        header("Location: index.php");
                        exit;

                    }else{
                        //Precisa ser enviado a notificação.
                        $u->solicitar($id_user, $id_user_pesquisado);
                        $u->notificarSolicitacao($id_user, $id_user_pesquisado);
                        header("Location: index.php");
                        exit;
                    }
                }
                //Botão para parar de seguir
                if(isset($_POST['deixar_Seguir'])){
                    $u->conectar();
                    $u->pararSeguir($id_user, $id_user_pesquisado);
                    header("Location: index.php");
                    exit;
                }
                //Botão para parar Remover a solicitacao de seguir
                if(isset($_POST['RemoverSolicitacao'])){
                    $u->conectar();
                    $u->removerSolicitacao($id_user, $id_user_pesquisado);
                    header("Location: index.php");
                    exit;
                }

            ?>

        </div>

        <?php //Código que envia o comentario para seu respectivo post ?>       
        <?php
            //Caso o botão comentar seja apertado o sistema ira armazenar o comentario em seu respectivo post.
            if (isset($_POST['comentario'])){
                $comentario = array_filter($_POST['comentario']);
                $id_post = $_POST['id_post'];

                foreach( $comentario as $key => $texto_comentario ) {
                    //Atribuindo valor ao texto comentado.
                }
                //Se algo foi comentado, armazene em sua devida postagem.
                if(!empty($comentario)){
                    $data = date('Y/m/d');
                    $u->conectar();
                    $u->comentar($id_user, $id_post, $data, $texto_comentario);

                    $id_comen = $u->retornarIdComentario($id_user, $id_post, $data, $texto_comentario);
                    $partes_texto = explode(" ", $texto_comentario);

                    //Percorrendo as partes
                    foreach($partes_texto as $valor){
                        //Retirando os acentos.
                        $semAcento = $u->tirarAcentos($valor);
                        
                        //Verificando se existe uma marcação.
                    
                        if($semAcento[0] == '@'){
    
                            $semArroba = substr($semAcento, 1);
                            $tamanho = strlen($semArroba);
                            if($tamanho > 0){
                                //Verificando se existe a pessoa do @.
                                $pessoaMarcada = $u->pesquisarPessoaApelido($semArroba);
                                if(($pessoaMarcada->rowCount()) > 0){
                                    //Se exite, notifique.
                                    $pessoaMarcadaF = $pessoaMarcada->fetch();
                                    $id_pessoa_marcada = $pessoaMarcadaF['0'];
                                    $u->notificarMarcacaoComentario($id_user, $id_pessoa_marcada, $id_post, $id_comen);
                                }
                            }
                        }
                        
                        if($semAcento[0] == '#'){
    
                            $semHastag = substr($semAcento, 1);
                            $tamanho = strlen($semHastag);
                            if($tamanho > 0){
                                $u->adicionarTopicoComentario($id_user, $id_post, $id_comen ,$semHastag);
                            }
                        }
                    }
    
                    header("Location: index.php");
                }else{
                    echo "É necessário escrever algo para comentar!";
                }
            }
            //Caso o botão de apagar comentário seja precionado, o comentário será excluido.
            if (isset($_POST['deletarComentario'])){
                $valorC = $_POST['id_deletar_comentario'];
                $u->deletarComentario($valorC);
                $u->removerTopicoComentario($valorC);
                $u->removerNotificacaoComentario($valorC);
            }
        ?>
        <?php //Fim do código que envia o comentario para seu respectivo post ?>

        <div>
            <?php
                
                //Exibindo as postagens do usuário pesquisado
                $u->conectar();
                $retornoP = $u->retornarPostagens($id_user_pesquisado);
                $numerodlinhas = $retornoP->rowCount();

                if($euBloquado == 0 && $pessoaBloqueado == 0 && ($privacidade == 0 || ($privacidade == 1 && $retornoSegue == true))){
                    /*Se a conta é pública, tudo é vizualizado. 
                    Se a conta é privada mas o usuário segue a pessoa, tudo é vizualizado.
                    Se o usuário não está bloqueado, tudo é vizualizado.*/
                    while($exibirPostagem = $retornoP->fetch()){
                        $id_postagem = $exibirPostagem['0'];
                        $nome = $u->apelidoUsuario($exibirPostagem['1']);
                        $data = $exibirPostagem['2'];
                        $texto = $exibirPostagem['3'];
                        $partes_data = explode("-", $data);
                        $foto_postagem = $exibirPostagem['4'];
                        $foto_quem_postou = $u->retornarFoto($exibirPostagem['1']);

                        ?>  
                        
                        <div id = "PostagemTextoContainer">
                            <h3>Postagem</h3>
                        </div>

                        <div id="container-postagem">
                            <?php
                                if(!empty($foto_quem_postou)){
                                    ?><img class = "usuarioImg" src = "<?php print("$foto_quem_postou"); ?>" alt = "Foto_de_Perfil"> <?php

                                }else{
                                    ?><img class = "usuarioImg" src = "../images/sem-foto.png" alt = "Foto_de_Perfil"> <?php
                                }
                            ?>
                            
                            <h3><?php print("$nome");?> <span> <?php print("$partes_data[2]/$partes_data[1]/$partes_data[0] <br>");?> </span></h3>
                            <p> <?php print("$texto"); ?> </p>
                        

                            <?php  
                            if(!empty($foto_postagem)){
                
                                ?><img class = "postagemImg" src = "<?php print("$foto_postagem"); ?>" alt = "Foto_da_Postagem"><?php   
                            }
                            ?>

                        </div>

                        <div id = "comentarioTextoContainer">
                            <h3>Comentários:</h3>
                        </div>

                        <?php
                        //exibindo o comentarios em sua respectiva postagem.
                        $retornoC = $u->retornarComentarios($id_postagem);

                        //Se não houver comentarios, não precisa tentar imprimir.
                        if($retornoC->rowCount() > 0){
                            while($exibirComentario = $retornoC->fetch()){

                                $id_comentario = $exibirComentario['0'];
                                $id_usuario_comentou = $exibirComentario['1'];
                                $nomeC = $u->apelidoUsuario($id_usuario_comentou);
                                $id_postagemC = $exibirComentario['2'];
                                $dataC = $exibirComentario['3'];
                                $textoC = $exibirComentario['4'];
                                $partes_dataC = explode("-", $dataC);
                                $foto_quem_comentou = $u->retornarFoto($exibirComentario['1']);

                                
                                ?><div id="container-comentario"><?php
                                    
                                    if(!($u->verificarBloqueado($id_user, $id_usuario_comentou))){
                                        if($id_usuario_comentou == $id_user){
                                            //O botão de deletar apenas aparece para o dono do comentário.
                                            ?>
                                            <form method="post"> 
                                                <div id="deletarBtn">
                                                    <?php //Botão de deletar comentário?>
                                                    <input type="hidden" name = "id_deletar_comentario" value= "<?php echo $id_comentario; ?>" >
                                                    <input class="InputDeletar" type="submit" name = "deletarComentario" value = "deletar">
                                                </div>    
                                            </form>
                                            <?php
                                        }


                                        if(!empty($foto_quem_comentou)){
                                            ?><img class = "usuarioImg" src = "<?php print("$foto_quem_comentou"); ?>" alt = "Foto_de_Perfil"> <?php
            
                                        }else{
                                            ?><img class = "usuarioImg" src = "../images/sem-foto.png" alt = "Foto_de_Perfil"> <?php
                                        }

                                        
                                        ?>
                                        <h3><?php print("$nomeC");?> <span> <?php print("$partes_dataC[2]/$partes_dataC[1]/$partes_dataC[0] <br>");?> </span></h3>
                                        <p> <?php print("$textoC"); ?> </p>
                                        <?php
                                    }
                                ?></div><?php    
                            }
                        }    
                        ?>
                        <div id = "containerComentar">
                            <form method="post"> 
                                <!--Armazedando o id de cada postagem em um array-->
                                <div id = "InputComentar1" >  
                                    <input class = "InputComentar1" type="text" name="comentario[]" class = "campo" maxlength="1000" spellcheck="true" placeholder = "Comentar">
                                </div>    
                                <input type="hidden" name = "id_post" value= "<?php echo $id_postagem; ?>">

                                <div id = "InputComentar2">
                                    <input class = "InputComentar2" type="submit" name = "Comentar" value = "Comentar">
                                </div>    

                            </form>
                        </div>    
                        <?php

                    }
                }else{

                    ?><div id = "containerNaoPossivel">
                            <h3 class = "NaoPossivel">Não é possível visualizar as informações dessa conta.</h3>
                    </div><?php
                
                }
            ?>

        </div>

        <div></div>
    </body>

</html>