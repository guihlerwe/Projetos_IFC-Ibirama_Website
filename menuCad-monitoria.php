<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
$email = $_SESSION['email'] ?? '';

// Verificar se é coordenador e tem o e-mail autorizado
if ($tipo !== 'coordenador' || $email !== 'cge@ifc.edu.br') {
    header('Location: principal.php');
    exit;
}

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

$idMonitoriaEditar = isset($_GET['idMonitoria']) ? (int) $_GET['idMonitoria'] : 0;

// Buscar bolsistas/monitores (apenas alunos e bolsistas)
$monitores = [];
$sqlm = "SELECT p.idPessoa, p.nome, p.sobrenome, p.email, p.curso, p.foto_perfil 
         FROM pessoa p 
         WHERE p.tipo IN ('bolsista', 'aluno')
         ORDER BY p.nome, p.sobrenome";

$stmt = $conn->prepare($sqlm);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $monitores[] = $row;
    }
}
$stmt->close();

$placeholderPerfil = '../assets/photos/fotos_perfil/sem_foto_perfil.jpg';

function gerarSrcPerfil(?string $foto, string $placeholder)
{
    if ($foto === null) {
        return $placeholder;
    }

    $foto = trim((string) $foto);
    if ($foto === '') {
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

    return '../assets/photos/fotos_perfil/' . ltrim($foto, '/');
}

foreach ($monitores as &$mon) {
    $mon['foto_src'] = gerarSrcPerfil($mon['foto_perfil'] ?? null, $placeholderPerfil);
}
unset($mon);

$idPessoaLogado = $_SESSION['idPessoa'] ?? null;
$modoEdicao = false;
$monitoriaSelecionada = null;
$capaAtual = null;
$monitoresMonitoria = [];

// Mapeamento dos cursos
$cursoLabelMap = [
    'administracao' => 'Administração',
    'informatica' => 'Informática',
    'vestuario' => 'Vestuário',
    'moda' => 'Moda',
];

// Se estiver editando, buscar dados da monitoria
if ($idMonitoriaEditar > 0 && $idPessoaLogado && $tipo === 'coordenador') {
    // Buscar monitoria
    $stmtMonitoria = $conn->prepare("SELECT * FROM monitoria WHERE idMonitoria = ?");
    
    if ($stmtMonitoria) {
        $stmtMonitoria->bind_param('i', $idMonitoriaEditar);
        if ($stmtMonitoria->execute()) {
            $resultadoMonitoria = $stmtMonitoria->get_result();
            if ($dadosMonitoria = $resultadoMonitoria->fetch_assoc()) {
                $modoEdicao = true;
                $monitoriaSelecionada = $dadosMonitoria;
                
                // Buscar capa
                if (!empty($dadosMonitoria['capa'])) {
                    $capaAtual = '../assets/photos/monitorias/' . $dadosMonitoria['capa'];
                }
                
                // Buscar monitor vinculado
                $stmtEquipe = $conn->prepare("
                    SELECT pe.idPessoa, pe.nome, pe.sobrenome, pe.email, pe.curso, pe.foto_perfil
                    FROM monitoria_pessoa mp 
                    INNER JOIN pessoa pe ON pe.idPessoa = mp.idPessoa 
                    WHERE mp.idMonitoria = ? AND mp.tipoPessoa = 'monitor'
                    ORDER BY pe.nome, pe.sobrenome
                ");
                
                if ($stmtEquipe) {
                    $stmtEquipe->bind_param('i', $idMonitoriaEditar);
                    if ($stmtEquipe->execute()) {
                        $resultadoEquipe = $stmtEquipe->get_result();
                        while ($linha = $resultadoEquipe->fetch_assoc()) {
                            $linha['foto_src'] = gerarSrcPerfil($linha['foto_perfil'] ?? null, $placeholderPerfil);
                            $monitoresMonitoria[] = $linha;
                        }
                    }
                    $stmtEquipe->close();
                }
            }
        }
        $stmtMonitoria->close();
    }
}

$monitoriaEditData = null;
if ($modoEdicao && $monitoriaSelecionada) {
    // Processar dias da semana (assumindo que está armazenado como string separada por vírgulas)
    $diasSemana = !empty($monitoriaSelecionada['diasSemana']) ? explode(',', $monitoriaSelecionada['diasSemana']) : [];
    
    $monitoriaEditData = [
        'idMonitoria' => (int) $monitoriaSelecionada['idMonitoria'],
        'nome' => $monitoriaSelecionada['nome'] ?? '',
        'tipoMonitoria' => $monitoriaSelecionada['tipoMonitoria'] ?? '',
        'tipoLabel' => ucfirst($monitoriaSelecionada['tipoMonitoria'] ?? ''),
        'descricao' => $monitoriaSelecionada['textoSobre'] ?? '',
        'diasSemana' => $diasSemana,
        'horarioInicio' => $monitoriaSelecionada['horarioInicio'] ?? '',
        'horarioFim' => $monitoriaSelecionada['horarioFim'] ?? '',
        'email' => $monitoriaSelecionada['email'] ?? '',
        'capaPath' => $capaAtual,
    ];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/tema-global.css">
    <link rel="stylesheet" href="../assets/css/cad-monitoria.css">
    <title><?php echo $modoEdicao ? 'Editar Monitoria' : 'Criar Monitoria'; ?></title>
</head>
<body>
<script>
    sessionStorage.setItem('usuarioLogado', '<?php echo $nome; ?>');
    sessionStorage.setItem('tipoUsuario', '<?php echo $tipo; ?>');
    
    // Dados dos monitores para JavaScript
    const monitoresData = <?php echo json_encode($monitores); ?>;
    const monitoriaSelecionada = <?php echo $monitoriaEditData ? json_encode($monitoriaEditData, JSON_UNESCAPED_UNICODE) : 'null'; ?>;
    const monitoresMonitoriaSelecionados = <?php echo $modoEdicao ? json_encode(array_values($monitoresMonitoria), JSON_UNESCAPED_UNICODE) : '[]'; ?>;
</script>

<div class="container">
    <header>
        <div class="logo">
            <div class="icone-nav">
                <img src="../assets/photos/ifc-logo-preto.png" id="icone-ifc">
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
        <h1 class="page-title"><?php echo $modoEdicao ? 'Editar Monitoria' : 'Criar Nova Monitoria'; ?></h1>
        
        <form id="formulario" action="cad-monitoriaBD.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id-monitoria" id="id-monitoria"
                value="<?php echo $modoEdicao ? (int) $idMonitoriaEditar : ''; ?>">

            <!-- Cabeçalho: Capa + Tipo e Nome -->
            <div class="monitoria-header-form">
                <div class="form-group-capa">
                    <label class="form-label">Capa da Monitoria *</label>
                    <div id="div-capa">
                        <label id="upload-capa">
                            <input type="file" id="foto-capa" name="capa" accept="image/*" hidden <?php echo !$modoEdicao ? 'required' : ''; ?>>
                            <span id="capa-icon"<?php echo ($modoEdicao && $capaAtual) ? ' style="display:none;"' : ''; ?>>📷</span>
                            <img id="capa-preview" <?php if ($modoEdicao && $capaAtual) { echo 'src="' . htmlspecialchars($capaAtual) . '" style="display:block; width:100%; height:100%; object-fit:cover;"'; } else { echo 'style="display: none;"'; } ?>>
                        </label>
                    </div>
                </div>

                <div class="monitoria-info-form">
                    <div class="form-group">
                        <label class="form-label">Tipo de Monitoria *</label>
                        <div class="custom-select" id="tipo-select">
                            <div class="select-selected"><?php echo ($modoEdicao && $monitoriaEditData) ? htmlspecialchars($monitoriaEditData['tipoLabel']) : 'Selecione o tipo'; ?></div>
                            <div class="select-items">
                                <div data-value="tecnica-integrada">Área Técnica Integrada</div>
                                <div data-value="ensino-medio">Ensino Médio</div>
                                <div data-value="ensino-superior">Ensino Superior</div>
                            </div>
                        </div>
                        <input type="hidden" id="tipo-monitoria" name="tipo-monitoria" value="<?php echo ($modoEdicao && $monitoriaEditData) ? htmlspecialchars($monitoriaEditData['tipoMonitoria']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nome da Monitoria *</label>
                        <input type="text" id="nome-monitoria" name="nome-monitoria" placeholder="Ex: Ciências da Natureza" value="<?php echo ($modoEdicao && $monitoriaEditData) ? htmlspecialchars($monitoriaEditData['nome']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <!-- Sobre a Monitoria -->
            <div class="form-section">
                <h2 class="section-title">Sobre a Monitoria</h2>
                <div class="form-group">
                    <label class="form-label">Descrição (máx. 1000 caracteres)</label>
                    <textarea id="descricao" name="descricao" maxlength="1000" placeholder="Descreva os objetivos, conteúdos abordados e como a monitoria pode ajudar os alunos..." rows="6"><?php echo ($modoEdicao && $monitoriaEditData) ? htmlspecialchars($monitoriaEditData['descricao']) : ''; ?></textarea>
                    <span class="char-counter"><span id="char-count">0</span>/1000</span>
                </div>
            </div>

            <!-- Monitor -->
            <div class="form-section">
                <h2 class="section-title">Monitor(a)</h2>
                <div class="form-group">
                    <div id="monitor-info-container">
                        <div class="selecionar-monitor" id="selecionar-monitor">
                            <button type="button" class="btn-selecionar">+ Selecionar Monitor(a)</button>
                        </div>
                    </div>
                    <input type="hidden" name="monitor_id" id="monitor_id" required>
                </div>
            </div>

            <!-- Horários e Contato lado a lado -->
            <div class="form-section-dupla">
                <!-- Horários -->
                <div class="form-section-metade">
                    <h2 class="section-title">Horários de Atendimento</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Dias de Atendimento *</label>
                        <div class="checkbox-group">
                            <?php
                            $dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
                            $diasLabel = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                            $diasSelecionados = ($modoEdicao && $monitoriaEditData) ? $monitoriaEditData['diasSemana'] : [];
                            
                            for ($i = 0; $i < count($dias); $i++) {
                                $checked = in_array($dias[$i], $diasSelecionados) ? 'checked' : '';
                                echo '<label class="checkbox-label">';
                                echo '<input type="checkbox" name="dias-semana[]" class="dia-checkbox" value="' . $dias[$i] . '" ' . $checked . '>';
                                echo '<span>' . $diasLabel[$i] . '</span>';
                                echo '</label>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Horário de Atendimento *</label>
                        <div class="horario-inputs">
                            <input type="time" name="horario-inicio" id="horario-inicio" 
                                   value="<?php echo ($modoEdicao && $monitoriaEditData) ? htmlspecialchars($monitoriaEditData['horarioInicio']) : ''; ?>" required>
                            <span class="horario-separator">às</span>
                            <input type="time" name="horario-fim" id="horario-fim"
                                   value="<?php echo ($modoEdicao && $monitoriaEditData) ? htmlspecialchars($monitoriaEditData['horarioFim']) : ''; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Contato -->
                <div class="form-section-metade">
                    <h2 class="section-title">Agendar Horário</h2>
                    
                    <div class="form-group">
                        <label class="form-label">E-mail para Agendamento *</label>
                        <input type="email" id="email" name="email" placeholder="email@ifc.edu.br" value="<?php echo ($modoEdicao && $monitoriaEditData) ? htmlspecialchars($monitoriaEditData['email']) : ''; ?>" required>
                        <p class="form-hint">Os alunos usarão este e-mail para agendar horários de monitoria.</p>
                    </div>
                </div>
            </div>

            <!-- Botões de ação -->
            <div class="form-actions">
                <button type="button" class="btn-cancelar" onclick="window.location.href='principal.php'">Cancelar</button>
                <button type="submit" class="btn-salvar"><?php echo $modoEdicao ? 'Salvar Alterações' : 'Criar Monitoria'; ?></button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/global.js"></script>
<script src="../assets/js/cad-monitoria.js"></script>

<?php $conn->close(); ?>

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
    
    </div>
    <div class="acesso-info">
        <a href="https://www.gov.br/acessoainformacao/pt-br">
            <img src="../assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
        </a>
    </div>
</footer>
</body>
</html>