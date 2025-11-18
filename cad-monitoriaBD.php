/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/


<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se é coordenador
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'coordenador') {
    header('Location: index.php');
    exit;
}

// Banco de dados
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

$baseDirMonitoria = __DIR__ . '/assets/photos/monitoria';
$baseDirMonitoriasLegacy = __DIR__ . '/assets/photos/monitorias';

// Verificar se é criação ou edição
$idMonitoria = isset($_POST['id-monitoria']) && !empty($_POST['id-monitoria']) ? (int)$_POST['id-monitoria'] : 0;
$modoEdicao = $idMonitoria > 0;

// Receber dados do formulário
$nomeMonitoria = $_POST['nome-monitoria'] ?? '';
$tipoMonitoria = $_POST['tipo-monitoria'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$email = $_POST['email'] ?? '';
$diasSemana = isset($_POST['dias-semana']) ? implode(',', $_POST['dias-semana']) : '';
$horarioInicio = $_POST['horario-inicio'] ?? '';
$horarioFim = $_POST['horario-fim'] ?? '';

// ID do monitor
$monitorId = isset($_POST['monitor_id']) && !empty($_POST['monitor_id']) 
    ? (int)$_POST['monitor_id'] 
    : null;

// Validações básicas
if (empty($nomeMonitoria) || empty($tipoMonitoria) || empty($diasSemana) || empty($horarioInicio) || empty($horarioFim)) {
    die("Erro: Preencha todos os campos obrigatórios!");
}

// Função para criar nome de pasta seguro
function criarNomePastaSeguro($nome) {
    // Remove acentos
    $nome = iconv('UTF-8', 'ASCII//TRANSLIT', $nome);
    // Remove caracteres especiais e substitui espaços por underline
    $nome = preg_replace('/[^a-zA-Z0-9\s-]/', '', $nome);
    $nome = preg_replace('/\s+/', '_', trim($nome));
    $nome = strtolower($nome);
    return $nome;
}

// Processar upload da capa
$nomePasta = null;
$capaArquivo = 'capa.jpg'; // Nome padrão da capa

if (isset($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
    $capaTemp = $_FILES['capa']['tmp_name'];
    $capaExtensao = strtolower(pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION));
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($capaExtensao, $extensoesPermitidas)) {
        die("Erro: Formato de imagem não permitido para capa!");
    }
    
    // Criar nome da pasta baseado no nome da monitoria
    $nomePasta = criarNomePastaSeguro($nomeMonitoria);
    
    // Diretório de destino
    $diretorioMonitorias = $baseDirMonitoria . '/' . $nomePasta;
    if (!is_dir($diretorioMonitorias)) {
        mkdir($diretorioMonitorias, 0755, true);
    }
    
    $capaDestino = $diretorioMonitorias . '/' . $capaArquivo;
    
    if (!move_uploaded_file($capaTemp, $capaDestino)) {
        die("Erro ao fazer upload da capa!");
    }
} elseif (!$modoEdicao) {
    die("Erro: Capa é obrigatória ao criar uma nova monitoria!");
}

// Iniciar transação
$conn->begin_transaction();

try {
    if ($modoEdicao) {
        // Buscar nome da pasta atual
        $stmtBusca = $conn->prepare("SELECT capa FROM monitoria WHERE idMonitoria = ?");
        $stmtBusca->bind_param('i', $idMonitoria);
        $stmtBusca->execute();
        $resultBusca = $stmtBusca->get_result();
        $dadosAntigos = $resultBusca->fetch_assoc();
        $stmtBusca->close();
        
        // Se não houver nova capa, mantém a pasta antiga
        if ($nomePasta === null && isset($dadosAntigos['capa'])) {
            $nomePasta = $dadosAntigos['capa'];
        }

        // Determinar caminho atual da pasta da capa (novo padrão ou legado)
        $pastaAtual = null;
        if (isset($dadosAntigos['capa'])) {
            $possiveisPastas = [
                $baseDirMonitoria . '/' . $dadosAntigos['capa'],
                $baseDirMonitoriasLegacy . '/' . $dadosAntigos['capa']
            ];
            foreach ($possiveisPastas as $possivel) {
                if (is_dir($possivel)) {
                    $pastaAtual = $possivel;
                    break;
                }
            }
        }

        // Se houver nova capa e o nome mudou, renomear/mover pasta para o novo padrão
        if ($nomePasta && isset($dadosAntigos['capa']) && $nomePasta !== $dadosAntigos['capa']) {
            $pastaNova = $baseDirMonitoria . '/' . $nomePasta;
            if ($pastaAtual && !is_dir($pastaNova)) {
                rename($pastaAtual, $pastaNova);
            }
        } elseif ($pastaAtual && strpos($pastaAtual, '/assets/photos/monitorias/') !== false) {
            // Se não houve mudança de nome mas a pasta ainda está no diretório legado, move para o novo
            $pastaNova = $baseDirMonitoria . '/' . $dadosAntigos['capa'];
            if (!is_dir($pastaNova)) {
                rename($pastaAtual, $pastaNova);
            }
        }
        
        // Atualizar monitoria
        $sql = "UPDATE monitoria SET 
                nome = ?, 
                tipoMonitoria = ?, 
                textoSobre = ?, 
                email = ?, 
                diasSemana = ?, 
                horarioInicio = ?, 
                horarioFim = ?,
                capa = ?
                WHERE idMonitoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssi', 
            $nomeMonitoria, $tipoMonitoria, $descricao, $email, 
            $diasSemana, $horarioInicio, $horarioFim, $nomePasta, $idMonitoria
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar monitoria: " . $stmt->error);
        }
        $stmt->close();
        
        // Remover relacionamentos antigos de monitores
        $stmtDelete = $conn->prepare("DELETE FROM monitoria_pessoa WHERE idMonitoria = ? AND tipoPessoa = 'monitor'");
        $stmtDelete->bind_param('i', $idMonitoria);
        $stmtDelete->execute();
        $stmtDelete->close();
        
    } else {
        // Inserir nova monitoria
        $sql = "INSERT INTO monitoria (nome, tipoMonitoria, textoSobre, email, diasSemana, horarioInicio, horarioFim, capa) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssss', 
            $nomeMonitoria, $tipoMonitoria, $descricao, $email, 
            $diasSemana, $horarioInicio, $horarioFim, $nomePasta
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao criar monitoria: " . $stmt->error);
        }
        
        $idMonitoria = $conn->insert_id;
        $stmt->close();
    }
    
    // Inserir monitor (se houver)
    if ($monitorId) {
        $stmtMonitor = $conn->prepare("INSERT INTO monitoria_pessoa (idMonitoria, idPessoa, tipoPessoa) VALUES (?, ?, 'monitor')");
        $stmtMonitor->bind_param('ii', $idMonitoria, $monitorId);
        if (!$stmtMonitor->execute()) {
            throw new Exception("Erro ao vincular monitor: " . $stmtMonitor->error);
        }
        $stmtMonitor->close();
    }
    
    // Commit da transação
    $conn->commit();
    
    // Redirecionar para a página da monitoria
    header("Location: monitor.php?id=" . $idMonitoria);
    exit;
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    
    // Remover pasta criada se houve erro
    if ($nomePasta && !$modoEdicao) {
        $diretorioMonitorias = $baseDirMonitoria . '/' . $nomePasta;
        if (is_dir($diretorioMonitorias)) {
            // Remove a capa se existir
            $capaPath = $diretorioMonitorias . '/' . $capaArquivo;
            if (file_exists($capaPath)) {
                unlink($capaPath);
            }
            // Remove a pasta
            rmdir($diretorioMonitorias);
        }
    }
    
    die("Erro: " . $e->getMessage());
}

$conn->close();
?>