<?php

/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/


session_start();

// Configuração do banco de dados
$host = 'localhost';
$usuario = 'root';
//$senha = 'root';
$senha = 'Gui@15600';
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

// Buscar membros do projeto (coordenador e bolsista)
$coordenadores = [];
$bolsistas = [];
$voluntarios = [];
$sqlMembros = "SELECT p.idPessoa, p.nome, p.sobrenome, p.foto_perfil, pp.tipoPessoa FROM pessoa_projeto pp JOIN pessoa p ON pp.idPessoa = p.idPessoa WHERE pp.idProjeto = ?";
$stmtM = $conn->prepare($sqlMembros);
$stmtM->bind_param("i", $idProjeto);
$stmtM->execute();
$resM = $stmtM->get_result();
while ($row = $resM->fetch_assoc()) {
    if ($row['tipoPessoa'] === 'coordenador') {
        $coordenadores[] = $row;
    } elseif ($row['tipoPessoa'] === 'bolsista') {
        $bolsistas[] = $row;
    } elseif ($row['tipoPessoa'] === 'voluntario') {
        $voluntarios[] = $row;
    }
}
$stmtM->close();
$conn->close();


function gerarSrcFotoPerfil(?string $fotoPerfil): string
{
    $fallback = 'assets/photos/fotos_perfil/sem_foto_perfil.jpg';

    if ($fotoPerfil === null) {
        return $fallback;
    }

    $fotoPerfil = trim((string) $fotoPerfil);
    if ($fotoPerfil === '') {
        return $fallback;
    }

    if (stripos($fotoPerfil, 'data:image/') === 0 || stripos($fotoPerfil, 'http://') === 0 || stripos($fotoPerfil, 'https://') === 0) {
        return $fotoPerfil;
    }

    if (strpos($fotoPerfil, 'assets/') === 0 || strpos($fotoPerfil, 'uploads/') === 0) {
        return $fotoPerfil;
    }

    if (!ctype_print($fotoPerfil)) {
        static $finfo = null;
        if ($finfo === null && class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        }

        $mimeType = ($finfo instanceof finfo) ? $finfo->buffer($fotoPerfil) : null;
        if (!$mimeType || strpos($mimeType, 'image/') !== 0) {
            $mimeType = 'image/jpeg';
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($fotoPerfil);
    }

    return 'assets/photos/fotos_perfil/' . ltrim($fotoPerfil, '/');
}

foreach ($coordenadores as &$coord) {
    $coord['foto_src'] = gerarSrcFotoPerfil($coord['foto_perfil'] ?? null);
}
unset($coord);

foreach ($bolsistas as &$bol) {
    $bol['foto_src'] = gerarSrcFotoPerfil($bol['foto_perfil'] ?? null);
}
unset($bol);

foreach ($voluntarios as &$vol) {
    $vol['foto_src'] = gerarSrcFotoPerfil($vol['foto_perfil'] ?? null);
}
unset($vol);

function buscarImagemProjeto(?string $pasta, string $prefixo): ?string
{
    if (!$pasta) {
        return null;
    }

    $baseDir = __DIR__ . '/assets/photos/projetos/' . $pasta . '/';
    if (!is_dir($baseDir)) {
        return null;
    }

    $arquivos = glob($baseDir . $prefixo . '.*');
    if (!$arquivos) {
        return null;
    }

    $arquivo = basename($arquivos[0]);
    return 'assets/photos/projetos/' . $pasta . '/' . $arquivo;
}

$bannerPath = buscarImagemProjeto($projeto['banner'] ?? null, 'banner');
$capaPath = buscarImagemProjeto($projeto['capa'] ?? null, 'capa');

$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" id="favicon" href="" type="image/png">
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/projeto.css">
    <!-- Adicionar os arquivos CSS e JS necessários -->
    <link rel="stylesheet" href="assets/css/popup.css">
    <script src="assets/js/popup.js"></script>
    <title><?php echo htmlspecialchars($projeto['nome']); ?></title>
    <script>
        (function() {
            const favicon = document.getElementById('favicon');
            const updateFavicon = () => {
                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                favicon.href = isDark ? 'assets/photos/ifc-logo-branco.png' : 'assets/photos/ifc-logo-preto.png';
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
                <div class="login-nav"> <?php include 'menuUsuario.php'; ?> </div>
            </div>
        </header>

        <div id="conteudo-projeto">
            <div id="banner">
                <?php if ($bannerPath): ?>
                    <img src="<?php echo htmlspecialchars($bannerPath); ?>" alt="Banner do projeto" id="banner-img">
                <?php else: ?>
                    <div id="banner-placeholder">
                        <span>Banner do Projeto</span>
                    </div>
                <?php endif; ?>
            </div>

            <div id="info-projeto">
                <div id="div-capa">
                    <?php if ($capaPath): ?>
                        <img src="<?php echo htmlspecialchars($capaPath); ?>" alt="Capa do projeto" id="capa-img">
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
                <?php if (!empty($projeto['linkParaInscricao']) && (!empty($nome) || $projeto['tipo'] === 'extensao')): ?>
                    <div id="link-inscricao">
                        <a href="<?php echo htmlspecialchars($projeto['linkParaInscricao']); ?>" target="_blank" class="btn-link">
                            ✓ Inscrever-se no Projeto
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div id="detalhes-projeto">
                <?php if (!empty($projeto['textoSobre'])): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Sobre o Projeto</h2>
                        <p class="texto-sobre"><?php echo nl2br(htmlspecialchars($projeto['textoSobre'])); ?></p>
                        <?php if (!empty($projeto['linkSite'])): ?>
                            <a href="<?php echo htmlspecialchars($projeto['linkSite']); ?>" target="_blank" class="link-site">
                                → Visitar Site do Projeto
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($coordenadores) || !empty($bolsistas) || !empty($voluntarios)): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Equipe</h2>
                        <div class="equipe-container">
                            <?php if (!empty($coordenadores)): ?>
                                <div class="equipe-categoria">
                                    <h3 class="titulo-equipe">Coordenador(a)</h3>
                                    <div class="membros">
                                        <?php foreach ($coordenadores as $coordenador): ?>
                                            <div class="membro">
                                                <div class="foto-membro">
                                                    <!-- Substituir as imagens dos coordenadores: -->
                                                    <img src="<?php echo $coordenador['foto_src']; ?>"
                                                         alt="Foto de <?php echo htmlspecialchars($coordenador['nome']); ?>"
                                                         onclick="toggleUserInfo(<?php echo $coordenador['idPessoa']; ?>, event)"
                                                         class="foto-perfil">
                                                </div>
                                                <span class="nome-membro">
                                                    <?php echo htmlspecialchars($coordenador['nome'] . ' ' . $coordenador['sobrenome']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($bolsistas)): ?>
                                <div class="equipe-categoria">
                                    <h3 class="titulo-equipe">Bolsista</h3>
                                    <div class="membros">
                                        <?php foreach ($bolsistas as $bolsista): ?>
                                            <div class="membro">
                                                <div class="foto-membro">
                                                    <!-- Substituir as imagens dos bolsistas: -->
                                                    <img src="<?php echo $bolsista['foto_src']; ?>"
                                                         alt="Foto de <?php echo htmlspecialchars($bolsista['nome']); ?>"
                                                         onclick="toggleUserInfo(<?php echo $bolsista['idPessoa']; ?>, event)"
                                                         class="foto-perfil">
                                                </div>
                                                <span class="nome-membro">
                                                    <?php echo htmlspecialchars($bolsista['nome'] . ' ' . $bolsista['sobrenome']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($voluntarios)): ?>
                                <div class="equipe-categoria">
                                    <h3 class="titulo-equipe">Voluntário(a)</h3>
                                    <div class="membros">
                                        <?php foreach ($voluntarios as $voluntario): ?>
                                            <div class="membro">
                                                <div class="foto-membro">
                                                    <img src="<?php echo $voluntario['foto_src']; ?>"
                                                         alt="Foto de <?php echo htmlspecialchars($voluntario['nome']); ?>"
                                                         onclick="toggleUserInfo(<?php echo $voluntario['idPessoa']; ?>, event)"
                                                         class="foto-perfil">
                                                </div>
                                                <span class="nome-membro">
                                                    <?php echo htmlspecialchars($voluntario['nome'] . ' ' . $voluntario['sobrenome']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projeto['linkBolsista']) && !empty($nome)): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Oportunidades de Bolsa</h2>
                        <a href="<?php echo htmlspecialchars($projeto['linkBolsista']); ?>" target="_blank" class="btn-link bolsa-link">
                            ★ Inscreva-se para Bolsa
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($projeto['email']) || !empty($projeto['numero']) || !empty($projeto['linkInstagram'])): ?>
                    <div class="secao">
                        <h2 class="subtitulo">Contato</h2>
                        <div class="contatos">
                            <?php if (!empty($projeto['email'])): ?>
                                <div class="contato-item">
                                    <span class="icone-contato">✉</span>
                                    <a href="mailto:<?php echo htmlspecialchars($projeto['email']); ?>" class="link-contato">
                                        <?php echo htmlspecialchars($projeto['email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($projeto['numero'])): ?>
                                <div class="contato-item">
                                    <span class="icone-contato">☎</span>
                                    <a href="tel:<?php echo htmlspecialchars($projeto['numero']); ?>" class="link-contato">
                                        <?php echo htmlspecialchars($projeto['numero']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($projeto['linkInstagram'])): ?>
                                <div class="contato-item">
                                    <span class="icone-contato">@</span>
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
    <script src="assets/js/projeto.js"></script>

    <script>
        console.log('=== TESTE DE DEBUG ===');
        console.log('Função showUserInfo existe?', typeof showUserInfo);
        console.log('Testando chamar a função...');
        if (typeof showUserInfo === 'function') {
            showUserInfo(9);
        } else {
            console.error('ERRO: Função showUserInfo não encontrada!');
        }
    </script>
</body>
</html>