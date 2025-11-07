<?php
header('Content-Type: application/json');

// Configuração do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
$banco = 'website';

// Conexão com o banco
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Erro na conexão com o banco de dados']);
    exit;
}

$conn->set_charset("utf8");

// Pegar o ID da pessoa
$idPessoa = isset($_GET['idPessoa']) ? (int)$_GET['idPessoa'] : 0;

if ($idPessoa <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Buscar informações do usuário
$stmt = $conn->prepare("
    SELECT 
        p.idPessoa,
        p.nome,
        p.sobrenome,
        p.email,
        p.foto_perfil,
        p.descricao,
        p.curso,
        p.area,
        pp.tipoPessoa
    FROM pessoa p
    LEFT JOIN pessoa_projeto pp ON p.idPessoa = pp.idPessoa
    WHERE p.idPessoa = ?
    LIMIT 1
");

$stmt->bind_param("i", $idPessoa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Usuário não encontrado']);
    exit;
}

$usuario = $result->fetch_assoc();

// Processar a foto de perfil
function gerarSrcFotoPerfil(?string $fotoPerfil): string
{
    $fallback = 'assets/photos/fotos_perfil/sem_foto_perfil.jpg';

    if ($fotoPerfil === null || trim($fotoPerfil) === '') {
        return $fallback;
    }

    $fotoPerfil = trim($fotoPerfil);

    if (stripos($fotoPerfil, 'data:image/') === 0 || 
        stripos($fotoPerfil, 'http://') === 0 || 
        stripos($fotoPerfil, 'https://') === 0) {
        return $fotoPerfil;
    }

    if (strpos($fotoPerfil, 'assets/') === 0 || strpos($fotoPerfil, 'uploads/') === 0) {
        return $fotoPerfil;
    }

    if (!ctype_print($fotoPerfil)) {
        $mimeType = 'image/jpeg';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->buffer($fotoPerfil);
            if ($detectedMime && strpos($detectedMime, 'image/') === 0) {
                $mimeType = $detectedMime;
            }
        }
        return 'data:' . $mimeType . ';base64,' . base64_encode($fotoPerfil);
    }

    return 'assets/photos/fotos_perfil/' . ltrim($fotoPerfil, '/');
}

// Formatar dados para retorno
$response = [
    'nome' => $usuario['nome'],
    'sobrenome' => $usuario['sobrenome'],
    'email' => $usuario['email'],
    'foto' => gerarSrcFotoPerfil($usuario['foto_perfil']),
    'descricao' => $usuario['descricao'] ?? '',
    'tipo' => $usuario['tipoPessoa'] ?? 'aluno'
];

// Adicionar curso ou área dependendo do tipo
if ($usuario['tipoPessoa'] === 'coordenador') {
    $response['area'] = $usuario['area'] ?? '';
} else {
    $response['curso'] = $usuario['curso'] ?? '';
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>