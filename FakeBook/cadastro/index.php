<?php
    require_once '../servidor/connection.php';
    $u = new connection;
?>


<!DOCTYPE HTML>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Cadastro</title>
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="images/twitter.ico" />

    </head>

    <body>
        <div id="container-cadastro">
            <h1>Cadastro</h1>
            <form method="post">
                <input type="text" name="nome" class = "campo" maxlength="45" required autofocus placeholder="Nome Completo">
                <input type="text" name="usuario" class = "campo" maxlength="16" required placeholder="Usuário">
                <input type="email" name="email" class = "campo" maxlength="40" required placeholder = "Email">
                <input type="password" name="senha" class = "campo" maxlength="16" required placeholder = "Senha">
                <input type="password" name="confsenha" class = "campo" maxlength="16" required placeholder = "Confirmar senha">
                <input id = "input-cadastrar" type="submit" value="Cadastrar" class="btn">  
            </form>

            <div id="container-voltar">
                <a href="../index.php">Voltar</a>
            </div>
        </div>
        <?php
            if (isset($_POST['nome'])){
                //Se o botão foi precionado
                $nome = addslashes($_POST['nome']);
                $usuario = addslashes($_POST['usuario']);
                $email = addslashes($_POST['email']);
                $senha = addslashes($_POST['senha']);
                $confirmaSenha = addslashes($_POST['confsenha']);

                if(!empty($nome) && !empty($usuario) && !empty($email) && !empty($senha) && !empty($confirmaSenha)){
                //Se todos os campos estão preenchidos

                    $u->conectar();
                    if($u->msgError == ""){
                        //nehum erro aconteceu
                        if($senha == $confirmaSenha){

                            if($u->cadastrar($nome, $usuario, $email, $senha)){
                                
                                echo "Conta cadastrada com sucesso!";

                            }else{
                                echo "O email já está cadastrado!";
                            }
                        }else{

                            echo "As senhas devem ser iguais!";
                        }

                    }else{
                        //Algum erro na conexão aconteceu
                        echo "Erro: ".$u->msgError;
                    }
                }else{

                    echo "Preencha todos os campos!";
                }
            }
        ?>
    </body>
</html>