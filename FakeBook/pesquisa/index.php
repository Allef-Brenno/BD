<?php
    session_start();
    if(!isset($_SESSION['id_usuario'])){

        header("location: ../index.php");
        exit;
    }
    $id_user = $_SESSION['id_usuario'];
    $pesquisa = $_SESSION['pesquisa'];
    require_once '../servidor/connection.php';
    $u = new connection;

?>

<!DOCTYPE HTML>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Pesquisar</title>
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="../images/twitter.ico" />
    </head>

    <body>

        <div id="container-menu">
            <div id = "retornar-inicio">
                <a href="../perfil/index.php">Home</a>
            </div>

            <div id = "itens-menu">
                <form method="post" class="hMenu">
                    <input class ="inputMenu" type="text" name="pesquisar" class = "campo" maxlength="25" required placeholder="Pesquisar">
                    <input class = "inputMenuImg" type="image" src="../images/search.png" alt="submit" value = "pesquisar">
                </form>
            </div>

            <div id = "sair-menu">
                <a href="../sair/index.php">Sair</a>
            </div>
        </div>

        <hr class = "hrMenu">
        <?php
            //Apenas pesquisa quando uma pesquisa foi feira
            if(!empty($pesquisa)){
                if($pesquisa[0] != '#'){

                    $u->conectar();
                    $retornoP = $u->pesquisarUsuarioPorSeguidor($pesquisa);
                    $quantidadeResultados = $retornoP->rowCount();

                    if($quantidadeResultados > 1 || $quantidadeResultados == 0){
                        ?><div id = "containerTitulo">
                            <h3> Foram Encontrados <span><?php print("$quantidadeResultados"); ?></span>Resultados!</h3>
                        </div><?php    
                    }else{
                        ?><div id = "containerTitulo">
                            <h3>Foi encontrado <span><?php print("$quantidadeResultados"); ?></span>Resultado!</h3>
                        </div><?php
                    }

                    while($exibirRetorno = $retornoP->fetch()){
                        $id_pesquisado = $exibirRetorno[0];
                        $Apelido = $exibirRetorno[2];
                        $numSeguidores = $exibirRetorno[7];

                        ?>
                        <!--Botão para ir ao usuario selecionado -->
                        <div id = "containerResultados">
                            <form method="post"> 
                                <?php //Armazedando o id de cada postagem em um array?>  
                                <input type="hidden" name = "id_P_Usuario" value= "<?php echo $id_pesquisado; ?>" >
                                <input class = "InputVisitar" type="submit" name = "p_Usuario" value = "Visitar">
                                <label class = "Label2"><?php print("$numSeguidores Seguidores");?> </label>
                                <label class = "Label1"><?php print("@$Apelido");?> </label>
                            </form>
                        </div>
                        <?php
                    }
                }else{

                    $semHastag = substr($pesquisa, 1);
                    $u->conectar();
                    $retornoT = $u->pesquisarTopico($semHastag);
                    $quantidadeResultados = $retornoT->rowCount();

                    if($quantidadeResultados > 1 || $quantidadeResultados == 0){
                        ?><div id = "containerTitulo">
                            <h3> Foram Encontrados <span><?php print("$quantidadeResultados"); ?></span>Resultados!</h3>
                        </div><?php    
                    }else{
                        ?><div id = "containerTitulo">
                            <h3>Foi encontrado <span><?php print("$quantidadeResultados"); ?></span>Resultado!</h3>
                        </div><?php
                    }


                    while($exibirRetorno = $retornoT->fetch()){
                        $id_pesquisado = $exibirRetorno[3];
                        $id_dono_postagem = $u->retornarIdPessoaPostagem($id_pesquisado);
                        $Apelido = $u->apelidoUsuario($id_dono_postagem);
                        $nomeTopico = $exibirRetorno[2];

                        ?>
                        <!--Botão para ir ao usuario selecionado -->
                        <div id = "containerResultados">
                            <form method="post"> 
                                <?php //Armazedando o id de cada postagem em um array?>  
                                <input type="hidden" name = "id_P_Usuario" value= "<?php echo $id_dono_postagem; ?>" >
                                <input class = "InputVisitar" type="submit" name = "p_Usuario" value = "Visitar">
                                <label class = "Label2"><?php print("@$Apelido");?> </label>
                                <label class = "Label1"><?php print("#$nomeTopico");?> </label>
                            </form>
                        </div>
                        <?php
                    }
                }
                
            }

            if(isset($_POST["pesquisar"])){
                $pesquisa = $_POST['pesquisar'];
                $_SESSION['pesquisa'] = $pesquisa;
                header("location: index.php");

            }

            if(isset($_POST["p_Usuario"])){
                $id_usuario_pesquisado = $_POST['id_P_Usuario'];

                if($id_usuario_pesquisado == $id_user){
                    //Se a pessoa escolhida for você mesmo, vá para o própio perfil.
                    header("location: ../perfil/index.php");
                }else{
                    //Caso contrário, vá para o perfil do usuário selecionado.
                    $_SESSION['id_usuario_pesquisado'] = $id_usuario_pesquisado;
                    header("location: ../perfil_visitado/index.php"); 
                }
                

            }

        ?>
    </body>

</html>