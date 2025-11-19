<?php
/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/



session_start();
$nome = $_SESSION['nome'] ?? '';
$sobrenome = $_SESSION['sobrenome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
$idPessoa = $_SESSION['idPessoa'] ?? null;

if (!function_exists('redirectWithFeedback')) {
    function redirectWithFeedback(string $type, string $message): void
    {
        $_SESSION['delete_feedback'] = [
            'type' => $type,
            'message' => $message
        ];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$deleteFeedback = $_SESSION['delete_feedback'] ?? null;
if (isset($_SESSION['delete_feedback'])) {
    unset($_SESSION['delete_feedback']);
}

// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'yourpasswordhere';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$deleteRequest = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_id']);
if ($deleteRequest) {
    if ($tipo !== 'coordenador' || !$idPessoa) {
        redirectWithFeedback('error', 'Você não tem permissão para excluir projetos.');
    }

    $deleteId = filter_input(INPUT_POST, 'delete_project_id', FILTER_VALIDATE_INT);
    $confirmText = trim($_POST['confirm_text'] ?? '');

    if (!$deleteId) {
        redirectWithFeedback('error', 'Projeto inválido.');
    }

    if (mb_strtolower($confirmText, 'UTF-8') !== 'confirmar') {
        redirectWithFeedback('error', 'Para excluir, digite "confirmar" no campo solicitado.');
    }

    $ownershipStmt = $conn->prepare("SELECT 1 FROM pessoa_projeto WHERE idPessoa = ? AND idProjeto = ? AND tipoPessoa = 'coordenador' LIMIT 1");
    if ($ownershipStmt) {
        $ownershipStmt->bind_param('ii', $idPessoa, $deleteId);
        if ($ownershipStmt->execute()) {
            $ownershipStmt->store_result();
            if ($ownershipStmt->num_rows === 0) {
                $ownershipStmt->close();
                redirectWithFeedback('error', 'Você só pode excluir projetos em que é coordenador.');
            }
        } else {
            $ownershipStmt->close();
            redirectWithFeedback('error', 'Falha ao validar suas permissões.');
        }
        $ownershipStmt->close();
    } else {
        redirectWithFeedback('error', 'Não foi possível validar as permissões para excluir este projeto.');
    }

    $deleteStmt = $conn->prepare('DELETE FROM projeto WHERE idProjeto = ?');
    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $deleteId);
        if ($deleteStmt->execute()) {
            $deleteStmt->close();
            redirectWithFeedback('success', 'Projeto excluído com sucesso.');
        }
        $deleteStmt->close();
    }

    redirectWithFeedback('error', 'Não foi possível excluir o projeto. Tente novamente.');
}

$projetos = [];

if ($idPessoa) {
    if ($tipo === 'coordenador') {
        $stmt = $conn->prepare("SELECT p.idProjeto, p.nome, p.tipo, p.categoria, p.capa, p.textoSobre, p.anoInicio FROM projeto p INNER JOIN pessoa_projeto pp ON pp.idProjeto = p.idProjeto WHERE pp.idPessoa = ? AND pp.tipoPessoa = 'coordenador' ORDER BY p.nome ASC");
        if ($stmt) {
            $stmt->bind_param('i', $idPessoa);
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                $projetos = $resultado->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
        }
    } elseif ($tipo === 'bolsista') {
        $stmt = $conn->prepare("SELECT p.idProjeto, p.nome, p.tipo, p.categoria, p.capa, p.textoSobre, p.anoInicio FROM projeto p INNER JOIN pessoa_projeto pp ON pp.idProjeto = p.idProjeto WHERE pp.idPessoa = ? AND pp.tipoPessoa = 'bolsista' ORDER BY p.nome ASC");
        if ($stmt) {
            $stmt->bind_param('i', $idPessoa);
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();
                $projetos = $resultado->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
        }
    } else {
        $sql = "SELECT idProjeto, nome, tipo, categoria, capa, textoSobre, anoInicio FROM projeto ORDER BY nome ASC";
        if ($resultado = $conn->query($sql)) {
            $projetos = $resultado->fetch_all(MYSQLI_ASSOC);
            $resultado->free();
        }
    }
} else {
    $sql = "SELECT idProjeto, nome, tipo, categoria, capa, textoSobre, anoInicio FROM projeto ORDER BY nome ASC";
    if ($resultado = $conn->query($sql)) {
        $projetos = $resultado->fetch_all(MYSQLI_ASSOC);
        $resultado->free();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" id="favicon" href="" type="image/png">
    <link rel="stylesheet" href="../assets/css/tema-global.css">
    <link rel="stylesheet" href="../assets/css/principal.css">
    <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=1, user-scalable=no">
    <title>Projetos do Campus Ibirama</title>
    <script>
        (function() {
            const favicon = document.getElementById('favicon');
            const updateFavicon = () => {
                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                favicon.href = isDark ? '../assets/photos/ifc-logo-branco.png' : '../assets/photos/ifc-logo-preto.png';
            };
            updateFavicon();
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateFavicon);
        })();
    </script>
</head>

<body>
    <script>
        sessionStorage.setItem('usuarioLogado', '<?php echo $nome; ?>');
        sessionStorage.setItem('tipoUsuario', '<?php echo $tipo; ?>');
    </script>
    <div class="container">

        <?php if (!empty($deleteFeedback)) : ?>
            <div class="delete-feedback <?php echo htmlspecialchars($deleteFeedback['type']); ?>">
                <?php echo htmlspecialchars($deleteFeedback['message']); ?>
            </div>
        <?php endif; ?>

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
                <div class="login-nav"> <?php include 'menuUsuario.php'; ?> </div>
            </div>
        </header>

        <div class="barra-pesquisar">
            <input type="text" class="input-pesquisar" placeholder="Pesquisar" id="input-pesquisa">
            <button class="btn-filtrar pesquisa" data-filtro="pesquisa">Pesquisa</button>
            <button class="btn-filtrar ensino" data-filtro="ensino">Ensino</button>
            <button class="btn-filtrar extensao" data-filtro="extensao">Extensão</button>
            
            <div class="custom-select" id="categorias-filtrar">
                <div class="select-selected">Categorias</div>
                    <div class="select-items">
                        <div data-value="ciencias_naturais">Ciências Naturais</div>
                        <div data-value="ciencias_humanas">Ciências Humanas</div>
                        <div data-value="linguagens">Linguagens</div>
                        <div data-value="matematica">Matemática</div>
                        <div data-value="administracao">Administração</div>
                        <div data-value="informatica">Informática</div>
                        <div data-value="vestuario">Vestuário</div>
                    </div>
                </div>   
                
                <button id="limpar-filtros">Limpar filtros</button>
            </div>

            

        <div class="projects-grid">
            <?php
            $placeholderCapa = '../assets/photos/campus-image.jpg';
            if (!empty($projetos)) {
                foreach ($projetos as $projeto) {
                    $corClass = '';
                    switch ($projeto['tipo']) {
                        case 'pesquisa':
                            $corClass = 'azul';
                            break;
                        case 'ensino':
                            $corClass = 'verde';
                            break;
                        case 'extensao':
                            $corClass = 'vermelho';
                            break;
                    }

                    $imagemCapa = $placeholderCapa;
                    if (!empty($projeto['capa'])) {
                        $basePath = '../assets/photos/projetos/' . $projeto['capa'];
                        $imagemCapa = (pathinfo($basePath, PATHINFO_EXTENSION)) ? $basePath : $basePath . '/capa.jpg';
                    }

                    $nomeCompleto = $projeto['nome'] ?? '';
                    $nomeExibido = (strlen($nomeCompleto) > 40) ? substr($nomeCompleto, 0, 40) . '...' : $nomeCompleto;
                    $nomeCompletoSeguro = htmlspecialchars($nomeCompleto, ENT_QUOTES, 'UTF-8');

                    $idProjeto = (int) ($projeto['idProjeto'] ?? 0);
                    $viewUrl = 'projeto.php?id=' . $idProjeto;
                    $badgeMarkup = '';

                    $tipoProjeto = htmlspecialchars($projeto['tipo']);
                    $categoriaProjeto = htmlspecialchars($projeto['categoria']);
                    $cardClasses = 'project-card tipo-' . $tipoProjeto . ' categoria-' . $categoriaProjeto;

                	if ($tipo === 'coordenador') {
                        $viewUrl = 'menuCad-projeto.php?idProjeto=' . $idProjeto;
                        $cardClasses .= ' project-card-editable';
                        $badgeMarkup = '<span class="project-edit-badge">Editar</span>';
                    }

                    echo '<div class="' . $cardClasses . '" data-id="' . $idProjeto . '" data-tipo="' . $tipoProjeto . '" data-categoria="' . $categoriaProjeto . '" data-view-url="' . htmlspecialchars($viewUrl) . '">';
                    echo '<img src="' . htmlspecialchars($imagemCapa) . '" alt="' . htmlspecialchars($nomeCompleto) . '" class="project-image" onerror="this.onerror=null;this.src=\'' . $placeholderCapa . '\';">';
                    echo $badgeMarkup;
                    if ($tipo === 'coordenador') {
                        echo '<button type="button" class="project-delete-btn" data-project-id="' . $idProjeto . '" data-project-name="' . $nomeCompletoSeguro . '" aria-label="Excluir projeto ' . $nomeCompletoSeguro . '">Excluir</button>';
                    }
                    echo '<div class="project-label ' . $corClass . '">' . htmlspecialchars($nomeExibido) . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-projects">';
                if ($tipo === 'coordenador') {
                    echo '<p id="no-projects">Você ainda não possui projetos cadastrados.</p>';
                    echo '<p><a href="menuCad-projeto.php">Clique aqui para cadastrar o primeiro projeto</a></p>';
                } elseif ($tipo === 'bolsista') {
                    echo '<p id="no-projects">Você ainda não participa de nenhum projeto.</p>';
                } else {
                    echo '<p id="no-projects">Nenhum projeto encontrado.</p>';
                }
                echo '</div>';
            }
            ?>
        </div> 
    </div>

<?php if ($tipo === 'coordenador') : ?>
    <div id="delete-modal" class="delete-modal" aria-hidden="true">
        <div class="delete-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
            <button type="button" class="delete-modal__close" id="delete-modal-close" aria-label="Fechar">&times;</button>
            <h3 id="delete-modal-title">Excluir projeto</h3>
            <p class="delete-modal__text">Essa ação não pode ser desfeita. Digite <strong>confirmar</strong> para concluir a exclusão do projeto selecionado.</p>
            <form method="POST" id="delete-form">
                <input type="hidden" name="delete_project_id" id="delete-project-id">
                <label for="delete-confirm-input">Confirmação</label>
                <input type="text" name="confirm_text" id="delete-confirm-input" autocomplete="off" placeholder="Escreva confirmar" required>
                <p class="delete-modal__error" id="delete-error" role="alert"></p>
                <div class="delete-modal__actions">
                    <button type="button" class="delete-modal__secondary" id="delete-modal-cancel">Cancelar</button>
                    <button type="submit" class="delete-modal__cta">Excluir projeto</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

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
            <img src="../assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
        </a>
    </div>
</footer>
    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/principal.js"></script>
</body>
</html>

<?php
$conn->close();
?>