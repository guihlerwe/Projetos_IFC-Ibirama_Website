<?php
session_start();
// Debug
echo json_encode(['debug' => $_SESSION]);
exit;

// Verificar se o usuário está logado
if (!isset($_SESSION['idPessoa'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Usuário não autenticado']);
    exit;
}

// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
//$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro na conexão']);
    exit;
}

$conn->set_charset("utf8");
$idPessoa = $_SESSION['idPessoa'];

// Validar se arquivo foi enviado
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum arquivo foi enviado']);
    exit;
}

$file = $_FILES['foto'];
$nomeArquivo = $file['name'];
$tmpName = $file['tmp_name'];
$tamanho = $file['size'];

// Validações
$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$tamMaximo = 5 * 1024 * 1024; // 5MB

$mimeType = mime_content_type($tmpName);

if (!in_array($mimeType, $tiposPermitidos)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Formato de imagem não permitido']);
    exit;
}

if ($tamanho > $tamMaximo) {
    echo json_encode(['sucesso' => false, 'erro' => 'Arquivo muito grande (máximo 5MB)']);
    exit;
}

// Gerar nome único para o arquivo
$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
$nomeUnico = 'perfil_' . $idPessoa . '_' . time() . '.' . $extensao;
$caminhoCompleto = $pastaUpload . $nomeUnico;

// Mover arquivo
if (!move_uploaded_file($tmpName, $caminhoCompleto)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar arquivo']);
    exit;
}

// Atualizar banco de dados
$caminhoRelativo = '../assets/photos/perfis/' . $nomeUnico;
$stmt = $conn->prepare("UPDATE pessoa SET foto = ? WHERE idPessoa = ?");
$stmt->bind_param("si", $caminhoRelativo, $idPessoa);

if ($stmt->execute()) {
    echo json_encode(['sucesso' => true, 'foto' => $caminhoRelativo]);
} else {
    // Deletar arquivo se falhar a atualização
    unlink($caminhoCompleto);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar banco de dados']);
}

$stmt->close();
$conn->close();
?>