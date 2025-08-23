<?php
session_start();

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'bolsista') {
    header("Location: ../telaLogin/login.html");
    exit();
}

$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';

// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Buscar todos os projetos cadastrados (igual à tela principal)
$sql = "SELECT idProjeto, nome, tipo, categoria, capa, textoSobre, anoInicio FROM projeto ORDER BY nome ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="principal.css">
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
            <div id="logo">
                <div id="icone-nav">
                    <img src="../telaPrincipal/img/ifc-logo-preto.png" id="icone-ifc">
                </div>
                Projetos do Campus Ibirama
            </div>

            <div id="navegador">
                <div id="projetos-nav">Projetos</div>
                <div id="monitoria-nav">Monitoria</div>
                <div id="sobre-nav">Sobre</div>
                <?php include '../telaPrincipal/menuUsuario.php'; ?>
            </div>
        </header>

        <div class="barra-pesquisar">
            <input type="text" class="input-pesquisar" placeholder="Pesquisar" id="input-pesquisa">
            <button class="btn-filtrar pesquisa" data-filtro="pesquisa">Pesquisa</button>
            <button class="btn-filtrar ensino" data-filtro="ensino">Ensino</button>
            <button class="btn-filtrar extensao" data-filtro="extensao">Extensão</button>
            <button class="btn-filtrar todos" data-filtro="">Todos</button>
            
            <select id="categorias-filtrar">
                <option value="">Categorias</option>
                <option value="ciencias_naturais">Ciências Naturais</option>
                <option value="ciencias_humanas">Ciências Humanas</option>
                <option value="linguagens">Linguagens</option>
                <option value="matematica">Matemática</option>
                <option value="administracao">Administração</option>
                <option value="informatica">Informática</option>
                <option value="vestuario">Vestuário</option>
            </select>    
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
                    $imagemCapa = !empty($projeto['capa']) ? '../telaPrincipal/img/' . $projeto['capa'] : '../telaPrincipal/img/campus-image.jpg';
                    
                    // Limitar o texto do nome para não quebrar o layout
                    $nomeExibido = strlen($projeto['nome']) > 40 ? substr($projeto['nome'], 0, 40) . '...' : $projeto['nome'];
                    
                    echo '<div class="project-card ' . $projeto['tipo'] . ' categoria-' . $projeto['categoria'] . '" data-id="' . $projeto['idProjeto'] . '" data-tipo="' . $projeto['tipo'] . '" data-categoria="' . $projeto['categoria'] . '">';
                    echo '<img src="' . $imagemCapa . '" alt="' . htmlspecialchars($projeto['nome']) . '" class="project-image">';
                    echo '<div class="project-label ' . $corClass . '">' . htmlspecialchars($nomeExibido) . '</div>';
                    echo '</div>';
                }
            } else {
                // Caso não tenha projetos cadastrados, mostrar mensagem
                echo '<div class="no-projects">';
                echo '<p id="desc">Nenhum projeto cadastrado ainda.</p>';
                echo '</div>';
            }
            ?>
        </div> 
    </div>

    <footer>
    <div class="container footer-container">
        <div class="Aluno">
            <h2>Recursos</h2>
            <ul id="menu-aluno">
                <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
            </ul>
        </div>
        <div class="Sobre">
            <h2>Sobre este site</h2>
            <p> 
                O Campus Ibirama, inaugurado em 2010, com dezenas de profissionais, proporciona uma educação de 
                qualidade e oferece cursos de Tecnologia da Informação, Administração e Vestuário, 
                que são importantes para inovações e negócios.  

                <b id="gab">Gabriella</b> e <b id="gui">Guilherme</b> criaram um site para facilitar o acesso a informações sobre projetos e monitorias,
                que antes eram pouco divulgados. O site reúne dados sobre inscrições, horários de monitorias e contatos 
                dos responsáveis pelos projetos, mostrando a aplicação de conhecimentos do curso de Tecnologia da Informação.
            </p>
            <span id="License"><i>Licença M.I.T.2025</i></span>
        </div>
    </div>
</footer>
    <script src="./principal.js"></script>
</body>
</html>

<?php
$conn->close();
?>