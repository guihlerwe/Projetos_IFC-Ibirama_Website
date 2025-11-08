<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$sobrenome = $_SESSION['sobrenome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
$email = $_SESSION['email'] ?? '';
$idPessoa = $_SESSION['idPessoa'] ?? null;

// Verificar se é coordenador e tem o e-mail autorizado
if ($tipo !== 'coordenador' || $email !== 'cge@ifc.edu.br') {
    header('Location: principal.php');
    exit;
}

// Conexão com o banco de dados
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

// Buscar todas as monitorias
$monitorias = [];
$sql = "SELECT idMonitoria, nome, tipoMonitoria, capa FROM monitoria ORDER BY nome ASC";
if ($resultado = $conn->query($sql)) {
    $monitorias = $resultado->fetch_all(MYSQLI_ASSOC);
    $resultado->free();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/monitorias.css">
    <title>Editar Monitorias - IFC Ibirama</title>
</head>

<body>
    <script>
        sessionStorage.setItem('usuarioLogado', '<?php echo $nome; ?>');
        sessionStorage.setItem('tipoUsuario', '<?php echo $tipo; ?>');
    </script>
    <div class="container">
        <header>
            <div class="logo">
                <div class="icone-nav">
                    <img src="assets/photos/ifc-logo-preto.png" id="icone-ifc">
                </div>
                Monitorias do Campus Ibirama
            </div>
            <div class="navegador">
                <div class="projetos-nav">Projetos</div>
                <div class="monitoria-nav">Monitoria</div>
                <div class="login-nav"> <?php include 'menuUsuario.php'; ?> </div>
            </div>
        </header>

        <div class="barra-pesquisar">
            <input type="text" class="input-pesquisar" placeholder="Pesquisar" id="input-pesquisa">
            <button class="btn-filtrar tecnico" data-filtro="tecnica-integrada">Área Técnica Integrada</button>
            <button class="btn-filtrar geral" data-filtro="ensino-medio">Ensino Médio</button>
            <button class="btn-filtrar superior" data-filtro="ensino-superior">Superior</button>
            <button id="limpar-filtros">Limpar filtros</button>
        </div>

        <div class="projects-grid">
            <?php
            $placeholderCapa = 'assets/photos/default-monitoria.jpg';
            
            if (!empty($monitorias)) {
                foreach ($monitorias as $monitoria) {
                    $corClass = '';
                    switch ($monitoria['tipoMonitoria']) {
                        case 'tecnica-integrada':
                            $corClass = 'verde';
                            break;
                        case 'ensino-medio':
                            $corClass = 'vermelho';
                            break;
                        case 'ensino-superior':
                            $corClass = 'azul';
                            break;
                    }

                    $imagemCapa = $placeholderCapa;
                    if (!empty($monitoria['capa'])) {
                        $imagemCapa = 'assets/photos/monitorias/' . $monitoria['capa'] . '/capa.jpg';
                    }

                    $nomeCompleto = $monitoria['nome'] ?? '';
                    $nomeExibido = (strlen($nomeCompleto) > 40) ? substr($nomeCompleto, 0, 40) . '...' : $nomeCompleto;

                    $idMonitoria = (int) ($monitoria['idMonitoria'] ?? 0);
                    $editUrl = 'menuCad-monitoria.php?idMonitoria=' . $idMonitoria;
                    $tipoMonitoria = htmlspecialchars($monitoria['tipoMonitoria']);
                    
                    $cardClasses = 'project-card tipo-' . $tipoMonitoria . ' project-card-editable';
                    $badgeMarkup = '<span class="project-edit-badge">Editar</span>';

                    echo '<div class="' . $cardClasses . '" data-id="' . $idMonitoria . '" data-tipo="' . $tipoMonitoria . '" data-view-url="' . htmlspecialchars($editUrl) . '">';
                    echo '<img src="' . htmlspecialchars($imagemCapa) . '" alt="' . htmlspecialchars($nomeCompleto) . '" class="project-image" onerror="this.onerror=null;this.src=\'' . $placeholderCapa . '\';">';
                    echo $badgeMarkup;
                    echo '<div class="project-label ' . $corClass . '">' . htmlspecialchars($nomeExibido) . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-projects">';
                echo '<p>Nenhuma monitoria cadastrada ainda.</p>';
                echo '<p><a href="menuCad-monitoria.php">Clique aqui para cadastrar a primeira monitoria</a></p>';
                echo '</div>';
            }
            ?>
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
            <h2 id="Sobre">
                Sobre este site
                <span class="arrow">↗</span>
            </h2>
            <span id="License"><i>Licença M.I.T.2025</i></span>
        </div>
        <div class="acesso-info">
            <a href="https://www.gov.br/acessoainformacao/pt-br">
                <img src="assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
            </a>
        </div>
    </footer>
    <script src="assets/js/global.js"></script>
    <script src="assets/js/monitorias.js"></script>
</body>

</html>

<?php
$conn->close();
?>