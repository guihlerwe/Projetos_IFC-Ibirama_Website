<?php
/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/


session_start();

$emailAutorizado = 'cge.ibirama@ifc.edu.br';
$tipoUsuario = $_SESSION['tipo'] ?? null;
$emailUsuario = strtolower(trim($_SESSION['email'] ?? ''));

if ($tipoUsuario !== 'coordenador' || $emailUsuario !== $emailAutorizado) {
    header('Location: principal.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id-monitoria'])) {
    header('Location: menuEditMonitorias.php');
    exit;
}

$idMonitoria = (int) $_POST['id-monitoria'];

$host = 'localhost';
$usuario = 'root';
$senha = 'yourpasswordhere';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}
$conn->set_charset('utf8');

$stmtBusca = $conn->prepare('SELECT capa FROM monitoria WHERE idMonitoria = ?');
$stmtBusca->bind_param('i', $idMonitoria);
$stmtBusca->execute();
$result = $stmtBusca->get_result();
$monitoria = $result->fetch_assoc();
$stmtBusca->close();

if (!$monitoria) {
    $conn->close();
    header('Location: menuEditMonitorias.php?status=nao-encontrada');
    exit;
}

$baseDirMonitoria = __DIR__ . '/assets/photos/monitoria';
$baseDirMonitoriasLegacy = __DIR__ . '/assets/photos/monitorias';
$pastaCapa = $monitoria['capa'] ?? null;

$conn->begin_transaction();
$redirectUrl = 'menuEditMonitorias.php?status=excluida';

try {
    $stmtDelete = $conn->prepare('DELETE FROM monitoria WHERE idMonitoria = ?');
    $stmtDelete->bind_param('i', $idMonitoria);
    if (!$stmtDelete->execute()) {
        throw new Exception('Erro ao excluir monitoria: ' . $stmtDelete->error);
    }
    $stmtDelete->close();

    $conn->commit();

    if ($pastaCapa) {
        $caminhos = [
            $baseDirMonitoria . '/' . $pastaCapa,
            $baseDirMonitoriasLegacy . '/' . $pastaCapa
        ];
        foreach ($caminhos as $caminho) {
            if (is_dir($caminho)) {
                removerDiretorio($caminho);
            }
        }
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log('Erro ao excluir monitoria: ' . $e->getMessage());
    $redirectUrl = 'menuEditMonitorias.php?status=erro';
}

$conn->close();
header('Location: ' . $redirectUrl);
exit;

function removerDiretorio(string $diretorio): void
{
    if (!is_dir($diretorio)) {
        return;
    }

    $itens = scandir($diretorio);
    foreach ($itens as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $caminho = $diretorio . DIRECTORY_SEPARATOR . $item;
        if (is_dir($caminho)) {
            removerDiretorio($caminho);
        } else {
            @unlink($caminho);
        }
    }

    @rmdir($diretorio);
}
?>
