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
        <title>FakeBook | Configurações</title>
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

        <?php 
            //Obtendo o valor da privacidade.
            $u->conectar();
            $retorno = $u->retornarPerfil($id_user);
            $exibirPerfil = $retorno->fetch();
            $privacidade = $exibirPerfil[3];
            $Biografia = $exibirPerfil[4];

            if(!$privacidade){
                $privacidadeT = "Público";
            }else{
                $privacidadeT = "Privado";
            }
        ?>

        <div id = "containerTitulo">
            <h3>Privacidade</h3>
        </div>
        
        <div id = "containerPrivacidade">
            <h4>Seu perfil é <?php echo $privacidadeT ?></h4>
            <form method = "post">
                <input class = "InputPrivacidade" type = "submit" name = "AlterarP" value = "Alterar" >
            </form>
        </div>

        <?php
            //Alterando o valor da privacidade.
            if(isset($_POST['AlterarP'])){
                
                //Se o perfil é público, transforme em privado.
                if(!$privacidade){
                    $u->conectar();
                    $u->alterarPrivacidade($id_user, 1);
                    header("Location: index.php");
                    exit;
                }else{
                    //Se o perfil é privado, transforme em público.
                    $u->conectar();
                    $u->alterarPrivacidade($id_user, 0);
                    header("Location: index.php");
                    exit;
                }
            } 
        ?>
        
        <div id = "containerTitulo">
            <h3>Biografia</h3>
        </div>

        <div id = "containerBiografia">
            <p class = "biografiaTexto"><?php echo $Biografia ?></p>
        </div>

        <div id = "containerAlterarBiografia">
            <form method = "post">
                <div id = "BiografiaInput1">
                    <input class = "InputTextoBiografia" type = "text" name = "novaBio" maxlength = "500" spellcheck = "true" placeholder = "Nova Biografia">
                </div>

                <div id = "BiografiaInput2">
                    <input class = "InputBiografia" type = "submit" name = "AlterarB" value = "Alterar">
                </div>
            </form>
        </div>    
        <?php 
            if(isset($_POST['AlterarB'])){

                $novaBio = $_POST['novaBio'];

                if(!(empty($novaBio))){
                    $u->conectar();
                    $u->alterarBiografia($id_user, $novaBio);

                    header("Location: index.php");
                    exit;
                }else{
                    echo "Você precisa preencer o campo para alterar a biografia.";
                }

            }
        ?>

        <div id = "containerTitulo">
            <h3>Alterar Foto</h3>
        </div>

        <div id = "containerAlterarFoto">
            <form method = "post">
                <div id = "UrlInput1">
                    <input class = "InputTextoUrl" type = "text" name = "novaFoto" maxlength = "2083" placeholder = "URL Imagem">
                </div>

                <div id = "UrlInput2">
                    <input class = "InputUrl" type = "submit" name = "AlterarF" value = "Alterar">
                </div>
            </form>
        </div>

        <?php 
            if(isset($_POST['AlterarF'])){

                $novaFoto = $_POST['novaFoto'];

                if(!(empty($novaFoto))){
                    $u->conectar();
                    $u->alterarFoto($id_user, $novaFoto);

                    header("Location: index.php");
                    exit;
                }else{
                    echo "Você precisa preencer o campo para alterar sua Foto.";
                }

            }
        ?>

    </body>

</html>
