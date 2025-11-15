<?php
session_start();

// conexão
$host = 'localhost';
$usuario = 'root';
//$senha = 'Gui@15600';
$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Processar login antes de exibir o HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senhaDigitada = $_POST['senha'] ?? '';

    // Buscar usuário pelo e-mail
    $sql = "SELECT * FROM pessoa WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // Verificar a senha
        if (password_verify($senhaDigitada, $usuario['senha'])) {
            // Login bem-sucedido - salvar todas as informações na sessão
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['sobrenome'] = $usuario['sobrenome'] ?? '';
            $_SESSION['tipo'] = $usuario['tipo'];
            $_SESSION['idPessoa'] = $usuario['idPessoa'];
            $_SESSION['email'] = $usuario['email']; // ADICIONAR ESTA LINHA
            $_SESSION['curso'] = $usuario['curso'] ?? '';

            // Redirecionar para a página principal
            header("Location: principal.php");
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "E-mail não encontrado!";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/tema-global.css"> 
    <link rel="stylesheet" href="assets/css/login.css">
    <title>Entrar</title>
</head>
<body>

    <div class="container">
        <!-- Formulário -->
        <header>
            <div class="logo">
                <div class="icone-nav">
                    <img src="assets/photos/ifc-logo-preto.png" id="icone-ifc">
                </div>
                Projetos do Campus Ibirama
            </div>
            
            <div class="navegador">
                <div class="projetos-nav">Projetos</div>
                <div class="monitoria-nav">Monitoria</div>
                <div class="login-nav ativo">Entrar</div>
            </div>
        </header>
        

        <!-- Formulário -->
        <div class="container-formulario">
            <div class="divformulario">
                <?php if (isset($erro)): ?>
                    <div class="mensagem-erro">
                        <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">            
                    <div class="imagem-container">
                        <img src="assets/photos/campus-image.jpg" id="foto-ifc">
                        <h1 class="titulo-sobre-imagem">Entrar</h1>
                    </div>
                    <div class="form-body">
                        <p class="subtitulo">Preencha os dados para entrar.</p>
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
                        <button type="submit" class="botao">Entrar</button>
                    </div>
                </form>
            </div>
            
            <div class="cadastrar">
                <a href="cad-usuario.php" class="btn-cadastro">Ainda não possui cadastro? Cadastre-se</a>
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
    
    <script src="assets/js/global.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>
