<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';

// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
//$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Buscar todos os projetos cadastrados
$sql = "SELECT idProjeto, nome, tipo, categoria, capa, textoSobre, anoInicio FROM projeto ORDER BY nome ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/tema-global.css">
    <link rel="stylesheet" href="../assets/css/principal.css">
    <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=1, user-scalable=no">
    <title>Projetos do Campus Ibirama</title>
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
            if ($resultado->num_rows > 0) {
                while($projeto = $resultado->fetch_assoc()) {
                    // Determinar a classe de cor baseada no tipo
                    $corClass = '';
                    switch($projeto['tipo']) {
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
                    // Caminho da imagem de capa
                    $nomePastaProjeto = $projeto['capa'];
                    $imagemCapa = !empty($nomePastaProjeto) ? 'assets/photos/projetos/' . $nomePastaProjeto . '/capa.jpg' : 'assets/photos/campus-image.jpg';
                    
                    // Limitar o texto do nome para não quebrar o layout
                    $nomeExibido = strlen($projeto['nome']) > 40 ? substr($projeto['nome'], 0, 40) . '...' : $projeto['nome'];
                    
                    $viewUrl = 'projeto.php?id=' . (int) $projeto['idProjeto'];
                    echo '<div class="project-card tipo-' . htmlspecialchars($projeto['tipo']) . ' categoria-' . htmlspecialchars($projeto['categoria']) . '" data-id="' . (int) $projeto['idProjeto'] . '" data-tipo="' . htmlspecialchars($projeto['tipo']) . '" data-categoria="' . htmlspecialchars($projeto['categoria']) . '" data-view-url="' . htmlspecialchars($viewUrl) . '">';
                    echo '<img src="' . htmlspecialchars($imagemCapa) . '" alt="' . htmlspecialchars($projeto['nome']) . '" class="project-image" onerror="this.onerror=null;this.src=\'assets/photos/campus-image.jpg\';">';
                    echo '<div class="project-label ' . $corClass . '">' . htmlspecialchars($nomeExibido) . '</div>';
                    echo '</div>';
                }
            } else {
                // Caso não tenha projetos cadastrados, mostrar mensagem
                echo '<div class="no-projects">';
                echo '<p id="no-projects">Nenhum projeto cadastrado ainda.</p>';
                if ($tipo === 'coordenador') {
                    echo '<p><a href="menuCad-projeto.php">Clique aqui para cadastrar o primeiro projeto</a></p>';
                }
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
    <div class="Ativos">
        <h2>Dados Atuais</h2>
        <div id="dados-ativos">
            <p id="num-projetos">
                <span>Projetos</span>
                <h1><?php echo isset($resultado) ? $resultado->num_rows : 0; ?></h1>
            </p>
            <p id="num-bolsistas">
                <span>Bolsistas</span>
                <h1>18</h1>
            </p>
            <p id="num-coordenadores">
                <span>Coordenadores</span>
                <h1>4</h1>
            </p>
        </div>
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