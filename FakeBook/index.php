<?php
    require_once 'servidor/connection.php';
    $u = new connection();
?>

<!DOCTYPE HTML>

<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>FakeBook | Entrar</title>
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="images/twitter.ico" />
    </head>

    <body>
        <section>

            <div id = "container-login">
                <h1>Entrar</h1>
                <form method="post">
                    <input type="email" name="email" class = "campo" maxlength="40" required autofocus placeholder="Email">
                    <input type="password" name="senha" class = "campo" maxlength="16" required placeholder="Senha">
                    <input id ="input-logar" type="submit" value="Logar" class="btn">
                </form>

                <h4>Ainda não tem uma conta? <a href="cadastro/index.php">Cadastre-se!</a></h4>
            </div>

        </section>

        <?php
            if (isset($_POST['email'])){
                //Se o botão foi precionado
                $email = addslashes($_POST['email']);
                $senha = addslashes($_POST['senha']);

                if(!empty($email) && !empty($senha)){
                    //Se todos os campos estão preenchidos
                    $u->conectar();
                    if($u->msgError == ""){
                        //Se não houver erro na conexão
                        if($u->logar($email, $senha)){
                            //login realizado com sucesso
                            header("location: perfil/index.php");

                        }else{
                         //Email e senha não encontrados
                            echo "Email e/ou senha estão incorretos!";
                        }
                    }else{
                        //erro na conexão
                        echo "Erro: ".$u->msgError;
                    }

                }else{
                    //
                    echo "Preencha todos os campos!";
                }
        
            }
        ?>
    </body>
</html>