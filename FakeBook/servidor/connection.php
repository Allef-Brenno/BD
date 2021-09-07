<?php

    Class connection{

        private $pdo;
        public $msgError = "";

        public function conectar(){
            global $pdo;
            global $msgError;

            $nomebd = "mydb";
            $host = "localhost";
            $usuario = "root";
            $senha = "root";
            try{
                $pdo = new PDO("mysql:dbname=".$nomebd.";host=".$host,$usuario,$senha);
            }catch(PDOException $e){
                $msgError = $e->getMessage();
            }
            
        }

        public function cadastrar($nome, $usuario ,$email, $senha){
            global $pdo;

            $sql = $pdo->prepare("SELECT id_usuario FROM perfil_usuario WHERE email = :e OR apelido = :u");
            $sql->bindValue(":e", $email);
            $sql->bindValue(":u", $usuario);
            $sql->execute();

            if($sql->rowCount() > 0){

                return false; //Pessoa Já Cadastrada
            }else{
                //Cadastro realizado com sucesso
                $sql = $pdo->prepare("INSERT INTO perfil_usuario (nome, apelido ,email, senha) Values (:n, :u, :e, :s)");
                $sql->bindValue(":n", $nome);
                $sql->bindValue(":u", $usuario);
                $sql->bindValue(":e", $email);
                $sql->bindValue(":s", md5($senha));

                $sql->execute();

                return true;
            }

        }

        public function logar($email, $senha){
            global $pdo;

            $sql = $pdo->prepare("SELECT id_usuario FROM perfil_usuario WHERE
                email = :e AND senha = :s");
            $sql->bindValue(":e", $email);
            $sql->bindValue(":s", md5($senha));
            $sql->execute();

            if($sql->rowCount()> 0){
                //login efetuado com sucesso
                $dado = $sql->fetch();
                session_start();
                $_SESSION['id_usuario'] = $dado['id_usuario'];

                return true;
            }else{
                //login ou senha errado
                return false;
            }
        }

        public function postar($id_usuario, $postagem, $data, $imagem){
            global $pdo;

            $sql = $pdo->prepare("INSERT INTO postagem (id_usuario, data_postagem, texto, foto) Values (:i, :d, :t, :f)");
            $sql->bindValue(":i", $id_usuario);
            $sql->bindValue(":t", $postagem);
            $sql->bindValue(":f", $imagem);
            $sql->bindValue(":d", $data);
            $sql->execute();

            return true;
        }

        public function comentar($id_usuario, $id_postagem, $data, $comentario){
            global $pdo;

            $sql = $pdo->prepare("INSERT INTO comentarios (id_Usuario, id_Postagem, Data_Comentario, Texto) Values (:u, :p, :d, :t)");

            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":p", $id_postagem);
            $sql->bindValue(":d", $data);
            $sql->bindValue(":t", $comentario);
            $sql->execute();
        }

        public function retornarPostagens($id_usuario){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM postagem WHERE id_usuario = :i ORDER BY id_postagem DESC");
            $sql->bindValue(":i", $id_usuario);
            $sql->execute();

            return $sql;

        }
        
        public function retornarComentarios($id_postagem){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM comentarios WHERE id_Postagem = :i");
            $sql->bindValue(":i", $id_postagem);
            $sql->execute();

            return $sql;
        }

        public function retornarPerfil($id_usuario){
            global $pdo;
            $sql = $pdo->prepare("SELECT * FROM perfil_usuario WHERE id_usuario = :i");
            $sql->bindValue(":i", $id_usuario);
            $sql->execute();

            return $sql;
        }

        public function apelidoUsuario($id_usuario){
            global $pdo;

            $sql = $pdo->prepare("SELECT apelido FROM perfil_usuario WHERE id_usuario = :i");
            $sql->bindValue(":i", $id_usuario);
            $sql->execute();

            $nome = $sql->fetch();
            return $nome['apelido'];
        }

        public function pesquisarUsuario($pesquisa){
            global $pdo;
            
            $sql = $pdo->prepare("SELECT * FROM perfil_usuario WHERE Nome LIKE :p OR Apelido LIKE :p OR Biografia LIKE :p");
            $sql->bindValue(":p", ("%".$pesquisa."%"));
            $sql->execute();

            return $sql;
        }

        public function pesquisarUsuarioPorSeguidor($pesquisa){
            global $pdo;
            
            $sql = $pdo->prepare("SELECT * FROM perfil_usuario WHERE Nome LIKE :p OR Apelido LIKE :p OR Biografia LIKE :p ORDER BY Numero_seguidores DESC");
            $sql->bindValue(":p", ("%".$pesquisa."%"));
            $sql->execute();

            return $sql;
        }

        public function alterarBiografia($id_usuario, $texto){
            global $pdo;

            $sql = $pdo->prepare("UPDATE perfil_usuario SET Biografia = :t WHERE id_Usuario = :u");
            $sql->bindValue(":t", $texto);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

        }

        public function alterarPrivacidade($id_usuario, $privacidade){
            global $pdo;

            $sql = $pdo->prepare("UPDATE perfil_usuario SET Privacidade = :p WHERE id_Usuario = :u");
            $sql->bindValue(":p", $privacidade);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

        }

        public function verificarSeSegue($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM seguidos WHERE id_Usuario = :u AND id_Seguido = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            if(!(empty($sql)) && ($sql->rowCount()) > 0){
                //Se achou alguma linha, o usuario segue o target.
                return true;
            }else{
                //O usuario não segue a pessoa.
                return false;
            }
        }

        public function seguir($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("INSERT INTO seguidos (id_Usuario, id_Seguido) VALUES (:u, :p)");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            $sql = $pdo->prepare("SELECT Numero_seguidores FROM perfil_usuario WHERE id_Usuario = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->execute();

            $dado = $sql->fetch();
            $valor = $dado[0] + 1;

            $sql = $pdo->prepare("UPDATE perfil_usuario SET Numero_seguidores = :v WHERE id_Usuario = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":v", $valor);
            $sql->execute();

        }

        public function pararSeguir($id_usuario ,$id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM seguidos WHERE id_Usuario = :u AND id_Seguido = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            $retorno = $sql->rowCount();

            if($retorno > 0){
                $sql = $pdo->prepare("SELECT Numero_seguidores FROM perfil_usuario WHERE id_Usuario = :p");
                $sql->bindValue(":p", $id_pessoa);
                $sql->execute();

                $dado = $sql->fetch();

                if($dado[0] >= 1){
                    $valor = $dado[0] - 1;
            
                    $sql = $pdo->prepare("UPDATE perfil_usuario SET Numero_seguidores = :v WHERE id_Usuario = :p");
                    $sql->bindValue(":p", $id_pessoa);
                    $sql->bindValue(":v", $valor);
                    $sql->execute();
                }
            }
        }

        public function FazerPararDeSeguir($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM seguidos WHERE id_Usuario = :p AND id_Seguido = :u");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            $retorno = $sql->rowCount();

            if($retorno > 0){
                $sql = $pdo->prepare("SELECT Numero_seguidores FROM perfil_usuario WHERE id_Usuario = :u");
                $sql->bindValue(":u", $id_usuario);
                $sql->execute();
    
                $dado = $sql->fetch();
    
                if($dado[0] >= 1){
                    $valor = $dado[0] - 1;
                
                    $sql = $pdo->prepare("UPDATE perfil_usuario SET Numero_seguidores = :v WHERE id_Usuario = :u");
                    $sql->bindValue(":u", $id_usuario);
                    $sql->bindValue(":v", $valor);
                    $sql->execute();
                }
            }
        }

        public function retornarSeguidos($id_usuario){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM seguidos WHERE id_Usuario = :u");
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            return $sql;
        }

        public function retornarSeguidores($id_usuario){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM seguidos WHERE id_Seguido = :u");
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            return $sql;
        }

        public function deletarPostagem($id_postagem){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM comentarios where id_postagem = :p");
            $sql->bindValue(":p", $id_postagem);
            $sql->execute();

            $sql = $pdo->prepare("DELETE FROM postagem where id_postagem = :p");
            $sql->bindValue(":p", $id_postagem);
            $sql->execute();
        }

        public function deletarComentario($id_comentario){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM comentarios where id_comentario = :c");
            $sql->bindValue(":c", $id_comentario);
            $sql->execute();
        }

        public function solicitar($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("INSERT INTO Solicitacoes (id_Usuario, id_Solicitado) VALUES (:u, :p)");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

        }

        public function removerSolicitacao($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM Solicitacoes WHERE id_Usuario = :u AND id_Solicitado = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

        }

        public function existeSolicitacao($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM Solicitacoes WHERE id_Usuario = :u AND id_Solicitado = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            if($sql->rowCount() == 1){
                return true;
            }else{
                return false;
            }
        }

        public function retornarSolicitacoes($id_usuario){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM Solicitacoes WHERE id_Solicitado = :u");
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            return $sql;
        }

        public function aceitarSolicitacao($id_usuario, $id_pessoa){
            global $pdo;

            //Deleta a solicitação
            $sql = $pdo->prepare("DELETE FROM Solicitacoes WHERE id_Solicitado = :u AND id_Usuario = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            //Fazer a pessoa que solocitou, seguir o usuário.
            $sql = $pdo->prepare("INSERT INTO seguidos (id_Usuario, id_Seguido) VALUES (:p, :u)");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();


            $sql = $pdo->prepare("SELECT Numero_seguidores FROM perfil_usuario WHERE id_Usuario = :u");
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            $dado = $sql->fetch();
            $valor = $dado[0] + 1;

            $sql = $pdo->prepare("UPDATE perfil_usuario SET Numero_seguidores = :v WHERE id_Usuario = :u");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":v", $valor);
            $sql->execute();
            
        }

        public function recusarSolicitacao($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM Solicitacoes WHERE id_Solicitado = :u AND id_Usuario = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();
        }

        public function deletarComentarioBloqueado($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE c.* FROM comentarios AS c INNER JOIN postagem AS p ON c.id_postagem = p.id_postagem WHERE c.id_usuario = :p AND p.id_usuario = :u");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();
        }

        public function deletarTopicoBloqueado($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE t.* FROM topicos AS t INNER JOIN postagem AS p ON t.id_postagem = p.id_postagem WHERE p.id_usuario = :u AND t.id_usuario = :p");
            $sql->bindValue(":p", $id_pessoa);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();
        }

        public function bloquear($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("INSERT INTO bloqueados (id_Usuario, id_Bloqueado) VALUES (:u, :p)");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":p", $id_pessoa);
            $sql->execute();
        }

        public function desbloquear($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM bloqueados WHERE id_Usuario = :u AND id_Bloqueado = :p");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":p", $id_pessoa);
            $sql->execute();
        }

        public function verificarBloqueado($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM bloqueados WHERE id_Usuario = :u AND id_Bloqueado = :p");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":p", $id_pessoa);
            $sql->execute();

            if($sql->rowCount() > 0 ){
                //Se a pessoa estiver bloqueada retorna true.
                return true;
            }else{
                //Se a pessoa não estiver bloqueada retorna false.
                return false;
            }
        }

        public function retornarNotificacao($id_usuario){
            global $pdo;
            $sql = $pdo->prepare("SELECT * FROM notificacoes WHERE id_Usuario = :u ORDER BY id_notificacao DESC");
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            return $sql;
        }

        public function notificarSeguidor($id_usuario, $id_pessoa){
            global $pdo;

            $solicitacao = 0;
            $texto = "Agora segue você!";
            $sql = $pdo->prepare("INSERT INTO notificacoes (id_Usuario, id_Notificou, Mensagem ,Solicitacao) VALUES (:u, :n, :m ,:s)");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->bindValue(":m", $texto);
            $sql->bindValue(":s", $solicitacao);
            $sql->execute();

        }

        public function notificarAceitouSeguidor($id_usuario, $id_pessoa){
            global $pdo;

            $solicitacao = 0;
            $texto = "Aceitou sua solicitação.";
            $sql = $pdo->prepare("INSERT INTO notificacoes (id_Usuario, id_Notificou, Mensagem ,Solicitacao) VALUES (:n, :u, :m ,:s)");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->bindValue(":m", $texto);
            $sql->bindValue(":s", $solicitacao);
            $sql->execute();

        }

        public function notificarSolicitacao($id_usuario, $id_pessoa){
            global $pdo;

            $solicitacao = 1;
            $texto = "Deseja seguir você!";
            $sql = $pdo->prepare("INSERT INTO notificacoes (id_Usuario, id_Notificou, Mensagem ,Solicitacao) VALUES (:n, :u, :m ,:s)");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->bindValue(":m", $texto);
            $sql->bindValue(":s", $solicitacao);
            $sql->execute();

        }

        public function removerNotificacao($id_usuario, $id_pessoa){
            global $pdo;
            $solicitacao = 1;

            $sql = $pdo->prepare("DELETE FROM notificacoes WHERE id_Usuario = :u AND id_Notificou = :n AND solicitacao = :s");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->bindValue(":s", $solicitacao);
            $sql->execute();

        }

        public function removerNotificacaoBloqueado($id_usuario, $id_pessoa){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM notificacoes WHERE id_Usuario = :u AND id_Notificou = :n");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->execute();

            $sql = $pdo->prepare("DELETE FROM notificacoes WHERE id_Usuario = :n AND id_Notificou = :u");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->execute();

        }

        public function removerNotificacaoPostagem($id_postagem){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM notificacoes WHERE id_postagem = :p");
            $sql->bindValue(":p", $id_postagem);
            $sql->execute();

        }

        public function removerNotificacaoComentario($id_comentario){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM notificacoes WHERE id_comentario = :c");
            $sql->bindValue(":c", $id_comentario);
            $sql->execute();
        }

        public function notificarMarcacao($id_usuario, $id_pessoa, $id_postagem){
            global $pdo;

            $solicitacao = 0;
            $texto = "Marcou você em uma postagem!";

            $sql = $pdo->prepare("INSERT INTO notificacoes (id_Usuario, id_Notificou, id_postagem ,Mensagem ,Solicitacao) VALUES (:n, :u, :p ,:m ,:s)");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->bindValue(":p", $id_postagem);
            $sql->bindValue(":m", $texto);
            $sql->bindValue(":s", $solicitacao);
            $sql->execute();
        }

        public function notificarMarcacaoComentario($id_usuario, $id_pessoa, $id_postagem, $id_comentario){
            global $pdo;

            $solicitacao = 0;
            $texto = "Marcou você em um Comentário!";

            $sql = $pdo->prepare("INSERT INTO notificacoes (id_Usuario, id_Notificou, id_postagem ,Mensagem ,Solicitacao, id_comentario) VALUES (:n, :u, :p ,:m ,:s, :c)");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":n", $id_pessoa);
            $sql->bindValue(":p", $id_postagem);
            $sql->bindValue(":m", $texto);
            $sql->bindValue(":s", $solicitacao);
            $sql->bindValue(":c", $id_comentario);
            $sql->execute();
        }

        public function adicionarTopicoPostagem($id_usuario, $id_postagem, $texto){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM topicos WHERE id_Usuario = :u AND Nome = :t AND id_Postagem = :p");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":p", $id_postagem);
            $sql->bindValue(":t", $texto);
            $sql->execute();

            $retorno = $sql->rowCount();
            //Se não existir nenhum tópico com o mesmo nome na mesma postagem pode criar um tópico.
            if($retorno == 0){
                $sql = $pdo->prepare("INSERT INTO topicos (id_Usuario, Nome ,id_postagem) VALUES (:u, :t, :p)");
                $sql->bindValue(":u", $id_usuario);
                $sql->bindValue(":p", $id_postagem);
                $sql->bindValue(":t", $texto);
                $sql->execute();
            }
        }
        
        public function adicionarTopicoComentario($id_usuario, $id_postagem, $id_comentario, $texto){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM topicos WHERE id_Usuario = :u AND Nome = :t AND id_Postagem = :p AND id_comentario = :c");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":p", $id_postagem);
            $sql->bindValue(":t", $texto);
            $sql->bindValue(":c", $id_comentario);
            $sql->execute();

            $retorno = $sql->rowCount();
            //Se não existir nenhum tópico com o mesmo nome na mesma postagem pode criar um tópico.
            if($retorno == 0){
                $sql = $pdo->prepare("INSERT INTO topicos (id_Usuario, Nome ,id_postagem, id_comentario) VALUES (:u, :t, :p, :c)");
                $sql->bindValue(":u", $id_usuario);
                $sql->bindValue(":p", $id_postagem);
                $sql->bindValue(":t", $texto);
                $sql->bindValue(":c", $id_comentario);
                $sql->execute();
            }
        }

        public function removerTopicoPostagem($id_postagem){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM topicos WHERE id_postagem = :p");
            $sql->bindValue(":p", $id_postagem);
            $sql->execute();
        }

        public function removerTopicoComentario($id_comentario){
            global $pdo;

            $sql = $pdo->prepare("DELETE FROM topicos WHERE id_comentario = :c");
            $sql->bindValue(":c", $id_comentario);
            $sql->execute();
        }

        public function retornarIdPostagem($id_usuario, $texto, $data){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM postagem WHERE id_Usuario = :u AND data_postagem = :d AND texto= :t");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":d", $data);
            $sql->bindValue(":t", $texto);
            $sql->execute();

            $retorno = $sql->fetch();

            return $retorno[0];
        }

        public function retornarIdComentario($id_usuario, $id_postagem, $data, $texto){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM comentarios WHERE id_Usuario = :u AND id_postagem = :p AND data_comentario = :d AND texto= :t");
            $sql->bindValue(":u", $id_usuario);
            $sql->bindValue(":d", $data);
            $sql->bindValue(":t", $texto);
            $sql->bindValue(":p", $id_postagem);
            $sql->execute();

            $retorno = $sql->fetch();

            return $retorno[0];
        }

        public function pesquisarTopico($pesquisa){
            global $pdo;
            
            $sql = $pdo->prepare("SELECT * FROM topicos WHERE Nome LIKE :p ORDER BY id_topico DESC");
            $sql->bindValue(":p", ("%".$pesquisa."%"));
            $sql->execute();

            return $sql;
        }

        public function pesquisarPessoaApelido($apelido){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM perfil_usuario WHERE Apelido = :a");
            $sql->bindValue(":a", $apelido);
            $sql->execute();

            return $sql;
        }

        public function retornarIdPessoaPostagem($id_postagem){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM postagem WHERE id_postagem = :p");
            $sql->bindValue(":p", $id_postagem);
            $sql->execute();

            $retorno = $sql->fetch();

            if(!empty($retorno)){
                return $retorno['1'];
            }    
        }

        public function retornarPostagemSeguido($id_usuario){
            global $pdo;
            
            $sql = $pdo->prepare("SELECT p.id_postagem, p.id_usuario, p.data_postagem, p.texto, p.Foto FROM postagem AS p INNER JOIN seguidos AS s 
            ON p.id_usuario = s.id_seguido WHERE s.id_usuario = :u ORDER BY p.id_postagem DESC;");
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            return $sql;
        }

        public function alterarFoto($id_usuario, $foto){
            global $pdo;

            $sql = $pdo->prepare("UPDATE perfil_usuario SET Foto = :f WHERE id_Usuario = :u");
            $sql->bindValue(":f", $foto);
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

        }

        public function retornarFoto($id_usuario){
            global $pdo;

            $sql = $pdo->prepare("SELECT * FROM perfil_usuario WHERE id_Usuario = :u");
            $sql->bindValue(":u", $id_usuario);
            $sql->execute();

            $retorno = $sql->fetch();

            return $retorno[8];
        }

        public function tirarAcentos($string){
            return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
        }
    }
?>