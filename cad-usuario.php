<?php
// ===== Conexão com o banco =====
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
//$senha = 'Gui@15600';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// PHPMailer sem composer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensagem = ""; // inicializa a variável

// ===== Processamento do formulário =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $sobrenome = $_POST['sobrenome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    
    $curso = $matricula = $area = null;
    if ($tipo === 'aluno') {
        $curso = $_POST['curso'] ?? null;
        $matricula = $_POST['matricula'] ?? null;
    } elseif ($tipo === 'coordenador') {
        $area = $_POST['area'] ?? null;
    }

    if (empty($nome) || empty($sobrenome) || empty($email) || empty($senha) || empty($tipo)) {
        $mensagem = "Preencha todos os campos obrigatórios!";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));

        $verifica = explode('@', $email);
        $dominio = $verifica[1] ?? '';

        // Corrigido: domínios válidos
        if ($dominio == 'ifc.edu.br' || $dominio == 'estudantes.ifc.edu.br') { 
            $sql = "INSERT INTO pessoa (nome, sobrenome, email, senha, tipo, curso, matricula, area, confirmado, token) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $nome, $sobrenome, $email, $senhaHash, $tipo, $curso, $matricula, $area, $token);

            if ($stmt->execute()) {
                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;

                $mail->Username = 'projetos.ifc.ibirama@gmail.com';
                $mail->Password = 'jsfi pcrf zumq xfcv';

                $mail->setFrom('projetos.ifc.ibirama@gmail.com', 'IFC Projetos');
                $mail->addAddress($email, $nome);
                $mail->isHTML(true);
                $mail->Subject = 'Confirme seu cadastro';
                $linkConfirmacao = "http://localhost:8080/confirmar.php?token=$token";
                $mail->Body = "
                    <h2>Olá, $nome!</h2>
                    <p>Obrigado por se cadastrar no site de Projetos do IFC.</p>
                    <p>Clique no link abaixo para confirmar seu e-mail e ativar sua conta:</p>
                    <p><a href='$linkConfirmacao'>$linkConfirmacao</a></p>
                    <p>Se você não se cadastrou, ignore este e-mail.</p>
                ";

                if ($mail->send()) {
                    $mensagem = "Cadastro realizado! Verifique seu e-mail para confirmar o cadastro.";
                } else {            
                    $mensagem = "Erro ao enviar e-mail: " . $mail->ErrorInfo;
                }
            } else {
                $mensagem = "Erro ao cadastrar: " . $stmt->error;
            }

            $stmt->close();

        } else {
            $mensagem = "Somente e-mail com domínio <strong>@ifc.edu.br</strong> ou <strong>@estudantes.ifc.edu.br</strong>";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/cad-usuario.css">
    <title>Cadastrar-se como aluno</title>
</head>
<body>

<div class="container">
    <header>
        <div class="logo">
            <div class="grid-icon">
                <img src="../assets/photos/ifc-logo-preto.png" id="icone-ifc">
            </div>
            Projetos do Campus Ibirama
        </div>
        <div class="navegador">
            <div class="projetos-nav">Projetos</div>
            <div class="monitoria-nav">Monitoria</div>
            <div class="login-nav">Entrar</div>
        </div>
    </header>

    <!-- Formulário -->
    <div class="container-formulario">
        <div class="divformulario">
            <form action="cad-usuario.php" method="POST">
                <div class="imagem-container">
                    <img src="../assets/photos/campus-image.jpg" id="foto-ifc">
                    <h1 class="titulo-sobre-imagem">Cadastro</h1>
                </div>
                <p class="subtitulo">Preencha seus dados</p>
                <fieldset class="grupo">
                    <div class="campoEsquerda">
                        <input type="text" name="nome" class="campo" id="nome" placeholder="Nome" required>
                    </div>

                    <div class="campoDireita">
                        <input type="text" name="sobrenome" class="campo" id="sobrenome" placeholder="Sobrenome" required>
                    </div>
                </fieldset>

                <fieldset class="grupo">
                    <div class="campoEsquerda">
                        <input type="email" name="email" class="campo" id="email" placeholder="E-mail" required>
                    </div>

                    <div class="campoDireita">
                        <input type="password" name="senha" class="campo" id="senha" placeholder="Senha" required>
                    </div>
                </fieldset>

                <p class="subtitulo">Como você deseja cadastrar-se?</p>
                <div class="radio-group">
                    <div class="custom-select" id="tipo-usuario">
                        <div class="radio-container">
                            <label>
                                <input type="radio" name="usuario" value="aluno"> Aluno
                            </label>
                            <label>
                                <input type="radio" name="usuario" value="coordenador"> Coordenador
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="tipo" id="inputTipo">
                </div>

                <div id="camposAluno" style="display:none;">
                    <div class="custom-select" id="curso-aluno">
                        <div class="select-selected">Curso</div>
                        <div class="select-items">
                            <div data-value="administracao">Administração</div>
                            <div data-value="informatica">Informática</div>
                            <div data-value="vestuario">Vestuário</div>
                            <div data-value="moda">Moda</div>
                            <div data-value="gestao-comercial">Gestão Comercial</div>
                        </div>
                    </div>
                    <input type="hidden" name="curso" id="inputCurso">
                    <input type="text" name="matricula" class="campo" placeholder="Digite sua matrícula">
                </div>

                <div id="camposCoordenador" style="display:none;">
                    <div class="custom-select" id="area-coordenador">
                        <div class="select-selected">Área de estudo</div>
                        <div class="select-items">
                            <div data-value="ciencias-naturais">Ciências Naturais</div>
                            <div data-value="ciencias-humanas">Ciências Humanas</div>
                            <div data-value="linguagens">Linguagens</div>
                            <div data-value="matematica">Matemática</div>
                            <div data-value="administracao">Administração</div>
                            <div data-value="informatica">Informática</div>
                            <div data-value="vestuario">Vestuário</div>
                            <div data-value="coordenacao">Coordenação</div>
                            <div data-value="tecnico-administrativo">Técnico Administrativo</div>
                        </div>
                    </div>
                    <input type="hidden" name="area" class="campo" id="inputArea">
                </div>

                <button type="submit" class="botao">Cadastrar-se</button>
            </form>

            <?php if (!empty($mensagem)): ?>
                <div id="mensagemRetorno" class="mensagem-retorno">
                    <?php echo $mensagem; ?>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const msg = document.getElementById("mensagemRetorno");
                        if (msg) {
                            msg.scrollIntoView({ behavior: "smooth" });
                            msg.classList.add("visivel");
                            setTimeout(() => msg.remove(), 7000);
                        }
                    });
                </script>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="./assets/js/global.js"></script>
<script src="./assets/js/cad-usuario.js"></script>

</body>
</html>
