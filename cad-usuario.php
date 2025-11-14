<?php
// ===== Conexão com o banco =====
$host = 'localhost';
$usuario = 'root';
//$senha = 'root';
$senha = 'Gui@15600';
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
    } elseif ($tipo === 'aluno' && empty($curso)) {
        $mensagem = "Selecione o curso antes de concluir o cadastro.";
    } elseif ($tipo === 'aluno' && (empty($matricula) || !preg_match('/^\d{10}$/', $matricula))) {
        $mensagem = "A matrícula deve conter exatamente 10 números.";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));

        $verifica = explode('@', $email);
        $dominio = $verifica[1] ?? '';

        // Validação específica por tipo de usuário
        $dominioValido = false;
        if ($tipo === 'aluno' && $dominio === 'estudantes.ifc.edu.br') {
            $dominioValido = true;
        } elseif ($tipo === 'coordenador' && $dominio === 'ifc.edu.br') {
            $dominioValido = true;
        }

        if ($dominioValido) { 
                $stmtExiste = $conn->prepare("SELECT 1 FROM pessoa WHERE email = ? LIMIT 1");
                $stmtExiste->bind_param("s", $email);
                $stmtExiste->execute();
                $stmtExiste->store_result();

                if ($stmtExiste->num_rows > 0) {
                    $mensagem = "Este e-mail já está cadastrado. Utilize outro endereço ou recupere o acesso.";
                } else {
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
                }

                $stmtExiste->close();
            } else {
                if ($tipo === 'aluno') {
                    $mensagem = "Alunos devem usar e-mail com domínio <strong>@estudantes.ifc.edu.br</strong>";
                } elseif ($tipo === 'coordenador') {
                    $mensagem = "Coordenadores devem usar e-mail com domínio <strong>@ifc.edu.br</strong>";
                } else {
                    $mensagem = "Domínio de e-mail inválido";
                }
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
            <div class="icone-nav">
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
                
                <div class="form-body">
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

                    <div class="campoDireita campo-senha">
                        <input type="password" name="senha" class="campo" id="senha" placeholder="Senha" required>
                        <button type="button" class="toggle-senha" aria-label="Mostrar ou ocultar senha">
                            <span class="icone-olho" aria-hidden="true"></span>
                        </button>
                    </div>
                </fieldset>

                <p class="subtitulo">Como você deseja cadastrar-se?</p>
                <div class="toggle-switch-container">
                    <div class="toggle-switch">
                        <input type="radio" name="usuario" value="aluno" id="toggle-aluno" checked>
                        <input type="radio" name="usuario" value="coordenador" id="toggle-coordenador">
                        <label for="toggle-aluno" class="toggle-option">Aluno</label>
                        <label for="toggle-coordenador" class="toggle-option">Coordenador</label>
                        <div class="toggle-slider"></div>
                    </div>
                </div>
                <input type="hidden" name="tipo" id="inputTipo">

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
                    <input type="text" name="matricula" class="campo" placeholder="Digite sua matrícula" inputmode="numeric" pattern="\d{10}" maxlength="10" title="Informe exatamente 10 dígitos" required>
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
                </div>
            </form>

            <?php if (!empty($mensagem)): ?>
                <div id="mensagemRetorno" class="mensagem-retorno">
                    <?php echo $mensagem; ?>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const msg = document.getElementById("mensagemRetorno");
                        if (msg) {
                            const text = msg.textContent.toLowerCase();
                            const isError = text.includes("erro") || text.includes("preencha");
                            msg.scrollIntoView({ behavior: "smooth" });
                            msg.classList.add("visivel");
                            msg.classList.toggle("erro", isError);
                            msg.classList.toggle("sucesso", !isError);
                            setTimeout(() => msg.remove(), 7000);
                        }
                    });
                </script>
            <?php endif; ?>

        </div>
    </div>
</div>
<footer>
        <div class="linha">
            <div class="footer-container">
                <div class="Recursos">
                    <h2>Recursos</h2>
                    <ul>
                        <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                        <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                        <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                        <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                        <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                        <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
                    </ul>
                </div>
                <div class="Comunidade">
                    <h2>Comunidade</h2>
                    <ul>
                        <li><a href="http://acessoainformacao.ifc.edu.br/">Acesso à Informação</a></li>
                        <li><a href="https://ifc.edu.br/comite-de-crise/">Calendários Acadêmicos</a></li>
                        <li><a href="https://cepsh.ifc.edu.br/">CEPSH</a></li>
                        <li><a href="https://consuper.ifc.edu.br/">Conselho Superior</a></li>
                        <li><a href="https://sig.ifc.edu.br/public/jsp/portal.jsf">Portal Público</a></li>
                        <li><a href="https://editais.ifc.edu.br/">Editais IFC</a></li>
                        <li><a href="http://www.camboriu.ifc.edu.br/pos-graduacao/treinador-e-instrutor-de-caes-guia/">Projetos Cães-guia</a></li>
                        <li><a href="https://trabalheconosco.ifc.edu.br/">Trabalhe no IFC</a></li>
                    </ul>
                </div>
                <div class="Servidor">
                    <h2>Servidor</h2>
                    <ul>
                        <li><a href="https://ifc.edu.br/desenvolvimento-do-servidor/">Desenvolvimento do Servidor</a></li>
                        <li><a href="https://manualdoservidor.ifc.edu.br/">Manual do Servidor</a></li>
                        <li><a href="https://www.siapenet.gov.br/Portal/Servico/Apresentacao.asp">Portal SIAPENET</a></li>
                        <li><a href="http://suporte.ifc.edu.br/">Suporte TI</a></li>
                        <li><a href="https://sig.ifc.edu.br/sigrh/public/home.jsf">Sistema Integrado de Gestão (SIG)</a></li>
                        <li><a href="https://mail.google.com/mail/u/0/#inbox">Webmail</a></li>
                    </ul>
                </div>
                <div class="Sites-Relacionados">
                    <h2>Sites Relacionados</h2>
                    <ul>
                        <li><a href="https://www.gov.br/pt-br">Brasil - GOV</a></li>
                        <li><a href="https://www.gov.br/capes/pt-br">CAPES - Chamadas Públicas</a></li>
                        <li><a href="https://www-periodicos-capes-gov-br.ez317.periodicos.capes.gov.br/index.php?">Capes - Portal de Periódicos</a></li>
                        <li><a href="https://www.gov.br/cnpq/pt-br">CNPq - Chamadas Públicas</a></li>
                        <li><a href="http://informativo.ifc.edu.br/">Informativo IFC</a></li>
                        <li><a href="https://www.gov.br/mec/pt-br">MEC - Ministério da Educação</a></li>
                        <li><a href="https://www.transparencia.gov.br/">Transparência Pública</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="Sobre">
            <h2>Sobre este site</h2>
            <span id="License"><i>Licença M.I.T.2025</i></span>
        </div>
        <div class="acesso-info">
            <a href="https://www.gov.br/acessoainformacao/pt-br">
                <img src="assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
            </a>
        </div>
    </footer>
<script src="./assets/js/global.js"></script>
<script src="./assets/js/cad-usuario.js"></script>

</body>
</html>
