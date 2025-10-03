<?php
session_start();

// Configuração do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
//$senha = 'Gui@15600';
$banco = 'website';

// Conexão com o banco
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$idProjeto = 0;
if (isset($_GET['idProjeto'])) {
    $idProjeto = (int)$_GET['idProjeto'];
} elseif (isset($_GET['id'])) {
    $idProjeto = (int)$_GET['id'];
}

if ($idProjeto <= 0) {
    die("ID do projeto inválido. Use: ?idProjeto=X ou ?id=X onde X é o número do projeto.");
}

// Como sabemos que a coluna é 'idProjeto', vamos usar diretamente
$stmt = $conn->prepare("SELECT * FROM projeto WHERE idProjeto = ?");
$stmt->bind_param("i", $idProjeto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Projeto não encontrado.");
}

$projeto = $result->fetch_assoc();
$stmt->close();
$conn->close();

$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/tema-global.css">
    <link rel="stylesheet" href="../assets/css/projeto.css">
    <title><?php echo htmlspecialchars($projeto['nome']); ?></title>
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

        <div id="conteudo-projeto">
            <div id="banner">
                <?php if (!empty($projeto['banner'])): ?>
                    <img src="../assets/photos/projetos/<?php echo htmlspecialchars($projeto['banner']); ?>" alt="Banner do projeto" id="banner-img">
                <?php else: ?>
                    <div id="banner-placeholder">
                        <span>Banner do Projeto</span>
                    </div>
                <?php endif; ?>
            </div>

            <div id="info-projeto">
                <div id="div-capa">
                    <?php if (!empty($projeto['capa'])): ?>
                        <img src="../assets/photos/projetos/<?php echo htmlspecialchars($projeto['capa']); ?>" alt="Capa do projeto" id="capa-img">
                    <?php else: ?>
                        <span id="capa-icon">📷</span>
                    <?php endif; ?>
                </div>
                <div id="dados-projeto">
                    <div id="tags-projeto">
                        <span class="tag-tipo"><?php echo ucfirst(htmlspecialchars($projeto['tipo'])); ?></span>
                        <span class="tag-categoria"><?php echo ucwords(str_replace('-', ' ', htmlspecialchars($projeto['categoria']))); ?></span>
                        <?php if (!empty($projeto['anoInicio'])): ?>
                            <span class="tag-ano">Desde <?php echo htmlspecialchars($projeto['anoInicio']); ?></span>
                        <?php endif; ?>
                    </div>
                    <h1 id="nome-projeto"><?php echo htmlspecialchars($projeto['nome']); ?></h1>
                </div>
                <?php if (!empty($projeto['linkParaInscricao'])): ?>
                    <div id="link-inscricao">
                        <a href="<?php echo htmlspecialchars($projeto['linkParaInscricao']); ?>" target="_blank" class="btn-link">
                            📝 Inscrever-se no Projeto
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div id="detalhes-projeto">
                <?php if (!empty($projeto['textoSobre'])): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Sobre o Projeto</h2>
                        <p class="texto-sobre"><?php echo nl2br(htmlspecialchars($projeto['textoSobre'])); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projeto['linkSite'])): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Site do Projeto</h2>
                        <a href="<?php echo htmlspecialchars($projeto['linkSite']); ?>" target="_blank" class="link-site">
                            🌐 <?php echo htmlspecialchars($projeto['linkSite']); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projeto['nomeCoordenador']) || !empty($projeto['nomeBolsista'])): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Equipe</h2>
                        <div class="equipe-container">
                            <!-- coordenador -->
                            <?php if (!empty($projeto['nomeCoordenador'])): ?>
                                <div class="equipe-categoria">
                                    <h3 class="titulo-equipe">Coordenador(a)</h3>
                                    <div class="membros">
                                        <div class="membro">
                                            <div class="foto-membro">
                                                <?php if (!empty($projeto['fotoCoordenador'])): ?>
                                                    <img src="../assets/photos/projetos/<?php echo htmlspecialchars($projeto['fotoCoordenador']); ?>" alt="Foto do coordenador">
                                                <?php else: ?>
                                                    <span>👤</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="nome-membro"><?php echo htmlspecialchars($projeto['nomeCoordenador']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- bolsista -->
                            <?php if (!empty($projeto['nomeBolsista'])): ?>
                                <div class="equipe-categoria">
                                    <h3 class="titulo-equipe">Bolsista</h3>
                                    <div class="membros">
                                        <div class="membro">
                                            <div class="foto-membro">
                                                <?php if (!empty($projeto['fotoBolsista'])): ?>
                                                    <img src="../assets/photos/projetos/<?php echo htmlspecialchars($projeto['fotoBolsista']); ?>" alt="Foto do bolsista">
                                                <?php else: ?>
                                                    <span>👤</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="nome-membro"><?php echo htmlspecialchars($projeto['nomeBolsista']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projeto['linkBolsista'])): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Oportunidades de Bolsa</h2>
                        <a href="<?php echo htmlspecialchars($projeto['linkBolsista']); ?>" target="_blank" class="btn-link bolsa-link">
                            💼 Inscreva-se para Bolsa
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projeto['email']) || !empty($projeto['numero']) || !empty($projeto['linkInstagram'])): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Contato</h2>
                        <div class="contatos">
                            <?php if (!empty($projeto['email'])): ?>
                                <div class="contato-item">
                                    <span class="icone-contato">📧</span>
                                    <a href="mailto:<?php echo htmlspecialchars($projeto['email']); ?>" class="link-contato">
                                        <?php echo htmlspecialchars($projeto['email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($projeto['numero'])): ?>
                                <div class="contato-item">
                                    <span class="icone-contato">📱</span>
                                    <a href="tel:<?php echo htmlspecialchars($projeto['numero']); ?>" class="link-contato">
                                        <?php echo htmlspecialchars($projeto['numero']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($projeto['linkInstagram'])): ?>
                                <div class="contato-item">
                                    <span class="icone-contato">📷</span>
                                    <a href="<?php echo htmlspecialchars($projeto['linkInstagram']); ?>" target="_blank" class="link-contato">
                                        Instagram
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <footer>
        <div class="container footer-container">
            <div class="Aluno">
                <h2>Recursos</h2>
                <ul id="menu-aluno">
                    <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                    <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                    <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                    <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                    <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                    <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
                </ul>
            </div>
            <div class="Sobre">
                <h2>Sobre este site</h2>
                <p> 
                    O Campus Ibirama, inaugurado em 2010, com dezenas de profissionais, proporciona uma educação de 
                    qualidade e oferece cursos de Tecnologia da Informação, Administração e Vestuário, 
                    que são importantes para inovações e negócios.  

                    <b id="gab">Gabriella</b> e <b id="gui">Guilherme</b> criaram um site para facilitar o acesso a informações sobre projetos e monitorias,
                    que antes eram pouco divulgados. O site reúne dados sobre inscrições, horários de monitorias e contatos 
                    dos responsáveis pelos projetos, mostrando a aplicação de conhecimentos do curso de Tecnologia da Informação.
                </p>
                <span id="License"><i>Licença M.I.T.2025</i></span>
            </div>
        </div>
    </footer>
    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/projeto.js"></script></body>
</html>