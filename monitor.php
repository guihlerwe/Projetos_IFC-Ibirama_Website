<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
$idPessoaLogado = $_SESSION['idPessoa'] ?? null;

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

function resolverCapaMonitoria(?string $pasta): string
{
    if (!$pasta) {
        return 'assets/photos/default-monitoria.jpg';
    }

    $pastaLimpa = trim($pasta, '/');
    $opcoes = [
        'assets/photos/monitoria/' . $pastaLimpa . '/capa.jpg',
        'assets/photos/monitorias/' . $pastaLimpa . '/capa.jpg'
    ];

    foreach ($opcoes as $opcao) {
        if (file_exists(__DIR__ . '/' . $opcao)) {
            return $opcao;
        }
    }

    return $opcoes[0];
}

$id = $_GET['id'] ?? null;
$monitoria = null;
$monitor = null;

if ($id) {
    // Buscar dados da monitoria
    $stmt = $conn->prepare("SELECT * FROM monitoria WHERE idMonitoria = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $monitoria = $result->fetch_assoc();
    $stmt->close();
    
    // Buscar dados do monitor vinculado
    if ($monitoria) {
        $stmtMonitor = $conn->prepare("
            SELECT p.idPessoa, p.nome, p.sobrenome, p.email, p.curso, p.foto_perfil, p.descricao 
            FROM monitoria_pessoa mp 
            INNER JOIN pessoa p ON p.idPessoa = mp.idPessoa 
            WHERE mp.idMonitoria = ? AND mp.tipoPessoa = 'monitor'
        ");
        $stmtMonitor->bind_param("i", $id);
        $stmtMonitor->execute();
        $resultMonitor = $stmtMonitor->get_result();
        $monitor = $resultMonitor->fetch_assoc();
        $stmtMonitor->close();
    }
}

// Função para gerar src da foto de perfil
$placeholderPerfil = 'assets/photos/fotos_perfil/sem_foto_perfil.jpg';

function gerarSrcPerfil(?string $foto, string $placeholder) {
    if ($foto === null || trim($foto) === '') {
        return $placeholder;
    }
    
    if (stripos($foto, 'data:image/') === 0 || stripos($foto, 'http://') === 0 || stripos($foto, 'https://') === 0) {
        return $foto;
    }
    
    if (strpos($foto, '../') === 0 || strpos($foto, '/assets') === 0 || strpos($foto, 'assets/') === 0) {
        return $foto;
    }
    
    if (!ctype_print($foto)) {
        static $finfo = null;
        if ($finfo === null && class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        }
        $mimeType = ($finfo instanceof finfo) ? $finfo->buffer($foto) : null;
        if (!$mimeType || strpos($mimeType, 'image/') !== 0) {
            $mimeType = 'image/jpeg';
        }
        return 'data:' . $mimeType . ';base64,' . base64_encode($foto);
    }
    
    return 'assets/photos/fotos_perfil/' . ltrim($foto, '/');
}

// Função para formatar dias da semana
function formatarDiasSemana($diasString) {
    if (empty($diasString)) return 'Não informado';
    
    $diasMap = [
        'segunda' => 'Segunda-feira',
        'terca' => 'Terça-feira',
        'quarta' => 'Quarta-feira',
        'quinta' => 'Quinta-feira',
        'sexta' => 'Sexta-feira',
        'sabado' => 'Sábado'
    ];
    
    $dias = explode(',', $diasString);
    $diasFormatados = array_map(function($dia) use ($diasMap) {
        return $diasMap[trim($dia)] ?? trim($dia);
    }, $dias);
    
    return implode(', ', $diasFormatados);
}

// Função para formatar horário
function formatarHorario($inicio, $fim) {
    if (empty($inicio) || empty($fim)) return 'Não informado';
    
    $inicioFormatado = date('H:i', strtotime($inicio));
    $fimFormatado = date('H:i', strtotime($fim));
    
    return "{$inicioFormatado} às {$fimFormatado}";
}

// Função para formatar tipo de monitoria
function formatarTipoMonitoria($tipo) {
    $tipoMap = [
        'tecnica-integrada' => 'Área Técnica Integrada',
        'ensino-medio' => 'Ensino Médio',
        'ensino-superior' => 'Ensino Superior'
    ];
    
    return $tipoMap[$tipo] ?? ucfirst($tipo);
}

// Função para formatar curso
function formatarCurso($curso) {
    $cursoMap = [
        'administracao' => 'Administração',
        'informatica' => 'Informática',
        'vestuario' => 'Vestuário',
        'moda' => 'Moda'
    ];
    
    return $cursoMap[strtolower($curso)] ?? ucfirst($curso);
}

if (!$monitoria) {
    echo "Monitoria não encontrada.";
    exit;
}

// Preparar dados para exibição
$capaPath = !empty($monitoria['capa']) ? resolverCapaMonitoria($monitoria['capa']) : 'assets/photos/default-monitoria.jpg';
$fotoMonitor = $monitor ? gerarSrcPerfil($monitor['foto_perfil'] ?? null, $placeholderPerfil) : $placeholderPerfil;
$nomeMonitor = $monitor ? $monitor['nome'] . ' ' . $monitor['sobrenome'] : 'Monitor não informado';
$emailMonitoria = $monitoria['email'] ?? 'Não informado';
$cursoMonitor = $monitor && !empty($monitor['curso']) ? formatarCurso($monitor['curso']) : '';
$descricaoMonitor = $monitor['descricao'] ?? ''; // Nova linha
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/monitor.css">
    <title><?php echo htmlspecialchars($monitoria['nome']); ?> - Monitorias IFC</title>
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

        <div class="monitoria-container">
            <!-- Cabeçalho com capa e informações principais -->
            <div class="monitoria-header">
                <div class="monitoria-capa">
                    <img src="<?php echo htmlspecialchars($capaPath); ?>" 
                         alt="Capa da monitoria"
                         onerror="this.onerror=null; this.src='assets/photos/default-monitoria.jpg';">
                </div>
                <div class="monitoria-info-principal">
                    <div class="tipo-monitoria">
                        <?php echo htmlspecialchars(formatarTipoMonitoria($monitoria['tipoMonitoria'])); ?>
                    </div>
                    <h1 class="monitoria-titulo"><?php echo htmlspecialchars($monitoria['nome']); ?></h1>
                </div>
            </div>

            <!-- Conteúdo principal -->
            <div class="monitoria-conteudo">
                <!-- Sobre a Monitoria -->
                <?php if (!empty($monitoria['textoSobre'])): ?>
                <section class="secao sobre-monitoria">
                    <h2>Sobre a Monitoria</h2>
                    <p><?php echo nl2br(htmlspecialchars($monitoria['textoSobre'])); ?></p>
                </section>
                <?php endif; ?>

                <!-- Monitor -->
                <section class="secao monitor-secao">
                    <h2>Monitor(a)</h2>
                    <div class="monitor-card-display">
                        <div class="monitor-foto">
                            <img src="<?php echo htmlspecialchars($fotoMonitor); ?>" 
                                 alt="<?php echo htmlspecialchars($nomeMonitor); ?>"
                                 onerror="this.onerror=null; this.src='<?php echo $placeholderPerfil; ?>';">
                        </div>
                        <div class="monitor-info">
                            <div class="monitor-dados">
                                <div class="monitor-nome"><?php echo htmlspecialchars($nomeMonitor); ?></div>
                                <?php if ($cursoMonitor): ?>
                                <div class="monitor-curso"><?php echo htmlspecialchars($cursoMonitor); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($emailMonitorPessoal)): ?>
                                <div class="monitor-email">
                                    <a href="mailto:<?php echo htmlspecialchars($emailMonitorPessoal); ?>">
                                        <?php echo htmlspecialchars($emailMonitorPessoal); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($descricaoMonitor)): ?>
                            <div class="monitor-descricao-inline">
                                <strong>Sobre o Monitor</strong>
                                <p><?php echo nl2br(htmlspecialchars($descricaoMonitor)); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Área de Contato e Horários lado a lado -->
                <div class="secao-dupla">
                    <!-- Horários de Atendimento - Agora à esquerda -->
                    <section class="secao-metade horarios-atendimento">
                        <h2>Horários de Atendimento</h2>
                        <div class="horario-info">
                            <div class="info-item">
                                <strong>Dias:</strong>
                                <span><?php echo formatarDiasSemana($monitoria['diasSemana']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Horário:</strong>
                                <span><?php echo formatarHorario($monitoria['horarioInicio'], $monitoria['horarioFim']); ?></span>
                            </div>
                        </div>
                    </section>

                    <!-- Contato - Agora à direita -->
                    <section class="secao-metade contato-monitoria">
                        <h2>Agendar Horário</h2>
                        <div class="contato-box">
                            <p class="texto-agendamento">Para agendar seu horário de monitoria, envie um e-mail para:</p>
                            <a href="mailto:<?php echo htmlspecialchars($emailMonitoria); ?>" class="email-destaque">
                                <?php echo htmlspecialchars($emailMonitoria); ?>
                            </a>
                        </div>
                    </section>
                </div>
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
</body>

</html>

<?php
$conn->close();
?>