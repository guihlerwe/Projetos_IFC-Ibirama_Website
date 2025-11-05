<?php
// contaBD.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1); // Mudar para 0 em produção
error_reporting(E_ALL);

session_start();
$idPessoa = $_SESSION['idPessoa'] ?? null;
if (!$idPessoa) {
    echo json_encode(['erro' => 'Usuário não autenticado.']);
    exit;
}

// Conexão com o banco
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    echo json_encode(['erro' => 'Erro na conexão com o banco.']);
    exit;
}
$conn->set_charset("utf8");

function resposta_json($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// GET -> retorna dados do usuário
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT idPessoa, nome, sobrenome, email, descricao, foto_perfil, curso, matricula, area FROM pessoa WHERE idPessoa = ?");
    $stmt->bind_param("i", $idPessoa);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = $res->fetch_assoc();
    if ($dados) {
        resposta_json($dados);
    } else {
        resposta_json(['erro' => 'Usuário não encontrado.']);
    }
}

// POST -> ações
$acao = $_POST['acao'] ?? ($_REQUEST['acao'] ?? '');
$acao = trim(strtolower($acao));

// ================================
// ATUALIZAR PERFIL (COM OU SEM FOTO)
// ================================
if ($acao === 'atualizar_perfil' || $acao === 'atualizar_com_foto' || $acao === 'salvar' || $acao === 'atualizar') {
    // Coleta dados
    $nome = trim((string)($_POST['nome'] ?? ''));
    $sobrenome = trim((string)($_POST['sobrenome'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $descricao = trim((string)($_POST['descricao'] ?? ''));
    $curso = isset($_POST['curso']) ? trim((string)$_POST['curso']) : null;
    $area = isset($_POST['area']) ? trim((string)$_POST['area']) : null;
    $matricula = isset($_POST['matricula']) ? trim((string)$_POST['matricula']) : null;
    $senha = isset($_POST['senha']) ? trim((string)$_POST['senha']) : '';

    // Validações
    if ($nome === '' || $sobrenome === '' || $email === '') {
        resposta_json(['erro' => 'Todos os campos obrigatórios devem ser preenchidos.']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        resposta_json(['erro' => 'Email inválido.']);
    }

    if (strlen($descricao) > 1000) {
        resposta_json(['erro' => 'Descrição muito longa (máx 1000 caracteres).']);
    }

    // Verifica email duplicado
    $stmt = $conn->prepare("SELECT idPessoa FROM pessoa WHERE email = ? AND idPessoa != ?");
    $stmt->bind_param("si", $email, $idPessoa);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        resposta_json(['erro' => 'Este email já está sendo usado por outro usuário.']);
    }

    // Processar upload de foto (se houver)
    $caminhoFoto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['foto'];
        
        // Validações da foto
        $tiposPermitidos = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            resposta_json(['erro' => 'Tipo de arquivo não permitido.']);
        }
        
        $tamanhoMax = 5 * 1024 * 1024;
        if ($arquivo['size'] > $tamanhoMax) {
            resposta_json(['erro' => 'Arquivo muito grande (máx 5MB).']);
        }

        // Diretório de upload (relativo ao contaBD.php)
        $diretorio_upload = __DIR__ . '/assets/photos/fotos_perfil/';
        
        // Cria pasta se não existir
        if (!file_exists($diretorio_upload)) {
            if (!mkdir($diretorio_upload, 0755, true)) {
                resposta_json(['erro' => 'Não foi possível criar o diretório de upload.']);
            }
        }

        // Nome do arquivo
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $nomeArquivo = 'perfil_' . $idPessoa . '_' . time() . '.' . $ext;
        $destino = $diretorio_upload . $nomeArquivo;
        
        // Caminho relativo para salvar no BD (a partir da raiz do site)
        $caminhoFoto = 'assets/photos/fotos_perfil/' . $nomeArquivo;

        // Remove foto anterior se existir
        $stmt = $conn->prepare("SELECT foto_perfil FROM pessoa WHERE idPessoa = ?");
        $stmt->bind_param("i", $idPessoa);
        $stmt->execute();
        $r = $stmt->get_result();
        $linha = $r->fetch_assoc();
        $fotoAnterior = $linha['foto_perfil'] ?? null;
        
        if ($fotoAnterior && !empty(trim($fotoAnterior))) {
            $caminhoAnterior = __DIR__ . '/' . $fotoAnterior;
            if (file_exists($caminhoAnterior)) {
                @unlink($caminhoAnterior);
            }
        }

        // Move o arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            resposta_json(['erro' => 'Falha ao salvar arquivo no servidor. Verifique as permissões da pasta.']);
        }
    }

    // Monta query de update
    if ($caminhoFoto) {
        // Atualizar COM foto
        if ($senha !== '') {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE pessoa SET nome = ?, sobrenome = ?, email = ?, descricao = ?, senha = ?, curso = ?, area = ?, matricula = ?, foto_perfil = ? WHERE idPessoa = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) resposta_json(['erro' => 'Erro interno (prepare).']);
            $stmt->bind_param("sssssssssi", $nome, $sobrenome, $email, $descricao, $senhaHash, $curso, $area, $matricula, $caminhoFoto, $idPessoa);
        } else {
            $sql = "UPDATE pessoa SET nome = ?, sobrenome = ?, email = ?, descricao = ?, curso = ?, area = ?, matricula = ?, foto_perfil = ? WHERE idPessoa = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) resposta_json(['erro' => 'Erro interno (prepare).']);
            $stmt->bind_param("ssssssssi", $nome, $sobrenome, $email, $descricao, $curso, $area, $matricula, $caminhoFoto, $idPessoa);
        }
    } else {
        // Atualizar SEM foto
        if ($senha !== '') {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE pessoa SET nome = ?, sobrenome = ?, email = ?, descricao = ?, senha = ?, curso = ?, area = ?, matricula = ? WHERE idPessoa = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) resposta_json(['erro' => 'Erro interno (prepare).']);
            $stmt->bind_param("ssssssssi", $nome, $sobrenome, $email, $descricao, $senhaHash, $curso, $area, $matricula, $idPessoa);
        } else {
            $sql = "UPDATE pessoa SET nome = ?, sobrenome = ?, email = ?, descricao = ?, curso = ?, area = ?, matricula = ? WHERE idPessoa = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) resposta_json(['erro' => 'Erro interno (prepare).']);
            $stmt->bind_param("sssssssi", $nome, $sobrenome, $email, $descricao, $curso, $area, $matricula, $idPessoa);
        }
    }

    if ($stmt->execute()) {
        resposta_json(['sucesso' => 'Perfil atualizado com sucesso.']);
    } else {
        resposta_json(['erro' => 'Erro ao atualizar perfil: ' . $stmt->error]);
    }
}

// ================================
// REMOVER FOTO
// ================================
if ($acao === 'remover_foto' || $acao === 'removerfoto') {
    $stmt = $conn->prepare("SELECT foto_perfil FROM pessoa WHERE idPessoa = ?");
    $stmt->bind_param("i", $idPessoa);
    $stmt->execute();
    $r = $stmt->get_result();
    $linha = $r->fetch_assoc();
    $foto = $linha['foto_perfil'] ?? null;

    if ($foto) {
        $caminhoFoto = __DIR__ . '/' . $foto;
        if (file_exists($caminhoFoto)) {
            @unlink($caminhoFoto);
        }
    }

    $stmt = $conn->prepare("UPDATE pessoa SET foto_perfil = NULL WHERE idPessoa = ?");
    $stmt->bind_param("i", $idPessoa);
    if ($stmt->execute()) {
        resposta_json(['sucesso' => 'Foto removida com sucesso.']);
    } else {
        resposta_json(['erro' => 'Erro ao remover foto do banco.']);
    }
}

// ================================
// EXCLUIR CONTA
// ================================
if ($acao === 'excluir_conta' || $acao === 'excluir') {
    // Remove foto física
    $stmt = $conn->prepare("SELECT foto_perfil FROM pessoa WHERE idPessoa = ?");
    $stmt->bind_param("i", $idPessoa);
    $stmt->execute();
    $r = $stmt->get_result();
    $linha = $r->fetch_assoc();
    $foto = $linha['foto_perfil'] ?? null;
    
    if ($foto) {
        $caminhoFoto = __DIR__ . '/' . $foto;
        if (file_exists($caminhoFoto)) {
            @unlink($caminhoFoto);
        }
    }

    // Excluir registro
    $stmt = $conn->prepare("DELETE FROM pessoa WHERE idPessoa = ?");
    $stmt->bind_param("i", $idPessoa);
    if ($stmt->execute()) {
        session_unset();
        session_destroy();
        resposta_json(['sucesso' => 'Conta excluída com sucesso.']);
    } else {
        resposta_json(['erro' => 'Erro ao excluir conta: ' . $stmt->error]);
    }
}

resposta_json(['erro' => 'Ação inválida ou não informada.']);
$conn->close();