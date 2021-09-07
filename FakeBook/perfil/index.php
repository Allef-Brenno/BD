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

<!DOCTYPE HTML>

<html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Home</title>
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="../images/twitter.ico" />
    </head>

    <body>
        <div id="container-menu">
            <div id = "retornar-inicio">
                <a href="index.php">Home</a>
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
                        }
                    ?>
                </form>
            </div>

            <div id = "sair-menu">
                <a href="../sair/index.php">Sair</a>
            </div>
        </div>

        <hr class = "hrMenu">
        
        <div id="container-opcoes">
            <div id = "itens-opcoes">
                <h3><a href="../linha_tempo/index.php">Linha Do Tempo</a></h3>
                <h3><a href="../seguidores/index.php">Seguidores</a></h3>
                <h3><a href="../seguidos/index.php">Seguidos</a></h3>
                <h3><a href="../notificacao/index.php">Notificações</a></h3>
                <h3><a href="../conf_perfil/index.php">Configurações</a></h3>
            </div>
        </div>

        <div id = "container-perfil">
            <div id = "itens-perfil">
                
                <?php
                    $u->conectar();
                    $retorno = $u->retornarPerfil($id_user);
                    $exibirPerfil = $retorno->fetch();
                    $nome = $exibirPerfil['Nome'];
                    $apelido = $exibirPerfil['Apelido'];
                    $biografia = $exibirPerfil['Biografia'];
                    $numeroSeguidor = $exibirPerfil['Numero_seguidores'];
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
            </div>
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

            //Caso o botão de apagar postagem seja precionado, a postagem será excluida.
            if (isset($_POST['deletarPostagem'])){
                $valorP = $_POST['id_deletar_post'];
                $u->deletarPostagem($valorP);
                $u->removerTopicoPostagem($valorP);
                $u->removerNotificacaoPostagem($valorP);
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

        <div id = "containerPostar">
            <form method="post">
                <div id = "InputPostar1">
                    <input class = "InputPostar1" type="text" name="postagem" maxlength="1000" spellcheck="true" placeholder="Postar">
                </div>

                <div id = "InputPostar3">
                    <input class = "InputPostar3" type="text" name="postagem_imagem" maxlength="2083" placeholder="URL Imagem">
                </div>

                <div id = "InputPostar2">
                    <input class = "InputPostar2" type="submit" value="Postar" >
                </div>
            </form>
        </div> 

        <?php
        //Caso o botão postar seja apertado o sistema ira armazenar a postagem
            if (isset($_POST['postagem'])){

                $postagem = $_POST['postagem'];
                $data = date('Y/m/d');
                $imagem = $_POST['postagem_imagem'];
                $u->conectar();
                if($u->msgError == ""){
                    if($u->postar($id_user, $postagem, $data, $imagem)){
                        
                    }else{
                        //echo "Aconteceu algum error ao postar.";
                    }
                }else{
                    echo "Error: ".$u->msgError;
                }

                $id_post = $u->retornarIdPostagem($id_user, $postagem, $data);
                $partes_texto = explode(" ", $postagem);

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
                                $u->notificarMarcacao($id_user, $id_pessoa_marcada, $id_post);
                            }
                        }
                    }
                    if($semAcento[0] == '#'){

                        $semHastag = substr($semAcento, 1);
                        $tamanho = strlen($semHastag);
                        if($tamanho > 0){
                            $u->adicionarTopicoPostagem($id_user, $id_post, $semHastag);
                        }
                    }
                }

                header("Location: index.php");

            }

        ?>

        <?php
                //Exibindo as postagens do usuário
        $u->conectar();
        $retornoP = $u->retornarPostagens($id_user);
        $numerodlinhas = $retornoP->rowCount();

        while($exibirPostagem = $retornoP->fetch()){
            $id_postagem = $exibirPostagem['0'];
            $id_quem_postou = $exibirPostagem['1'];
            $nome = $u->apelidoUsuario($id_quem_postou);
            $data = $exibirPostagem['2'];
            $texto = $exibirPostagem['3'];
            $partes_data = explode("-", $data);
            $foto_postagem = $exibirPostagem['4'];
            $foto_quem_postou = $u->retornarFoto($id_quem_postou);

            ?>
            <div id = "PostagemTextoContainer">
                <h3>Postagem</h3>
            </div>

            <div id="container-postagem">
                <form method="post"> 
                    <?php //Botão de deletar postagem?>
                    <div id="deletarBtn">
                        <input type="hidden" name = "id_deletar_post" value= "<?php echo $id_postagem; ?>" >
                        <input class="InputDeletar" type="submit" name = "deletarPostagem" value = "Deletar">
                    </div>
                </form>

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
            //Exibindo o comentários em sua respectiva postagem.
            $retornoC = $u->retornarComentarios($id_postagem);
            //Se não houver comentários, não precisa tentar imprimir.
            if($retornoC->rowCount() > 0){
                while($exibirComentario = $retornoC->fetch()){

                    $id_comentario = $exibirComentario['0'];
                    $nomeC = $u->apelidoUsuario($exibirComentario['1']);
                    $id_postagemC = $exibirComentario['2'];
                    $dataC = $exibirComentario['3'];
                    $textoC = $exibirComentario['4'];
                    $partes_dataC = explode("-", $dataC);
                    $foto_quem_comentou = $u->retornarFoto($exibirComentario['1']);
                    ?>
                    <div id="container-comentario">
                        <form method="post"> 
                            <div id="deletarBtn">
                                <?php //Botão de deletar comentário?>
                                <input type="hidden" name = "id_deletar_comentario" value= "<?php echo $id_comentario; ?>" >
                                <input class="InputDeletar" type="submit" name = "deletarComentario" value = "Deletar">
                            </div>
                        </form>

                        <?php
                            if(!empty($foto_quem_comentou)){
                                ?><img class = "usuarioImg" src = "<?php print("$foto_quem_comentou"); ?>" alt = "Foto_de_Perfil"> <?php

                            }else{
                                ?><img class = "usuarioImg" src = "../images/sem-foto.png" alt = "Foto_de_Perfil"> <?php
                            }
                        ?>

                        <h3><?php print("$nomeC");?> <span> <?php print("$partes_dataC[2]/$partes_dataC[1]/$partes_dataC[0] <br>");?> </span></h3>
                        <p> <?php print("$textoC"); ?> </p>
                    </div>    
                    <?php
                }
            }    
            ?>
            <div id = "containerComentar">
                <form method="post"> 
                    <?php //Armazedando o id de cada postagem em um array?> 
                    <div id = "InputComentar1" >
                        <input class = "InputComentar1" type="text" name="comentario[]" class = "campo" maxlength="1000" spellcheck="true" placeholder = "Comentar">
                    </div>
                    <input type="hidden" name = "id_post" value= "<?php echo $id_postagem; ?>" >
                    <div id = "InputComentar2">
                        <input class = "InputComentar2" type="submit" name = "Comentar" value = "Comentar">
                    </div>
                </form>
            </div>
            <?php

        }
        ?>

    </body>


</html>