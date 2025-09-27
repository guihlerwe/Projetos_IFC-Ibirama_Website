<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../telaLogin/login.html");
    exit();
}

// Conectando com o banco
$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
//$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificando conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$usuario_id = $_SESSION['usuario_id'];
$resposta = array();

// Processar diferentes ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    switch ($acao) {
        case 'atualizar_perfil':
            atualizarPerfil($conn, $usuario_id);
            break;
        
        case 'upload_foto':
            uploadFoto($conn, $usuario_id);
            break;
        
        case 'remover_foto':
            removerFoto($conn, $usuario_id);
            break;
        
        case 'excluir_conta':
            excluirConta($conn, $usuario_id);
            break;
        
        default:
            $resposta['erro'] = 'Ação inválida';
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Buscar dados do usuário
    $dados = buscarDadosUsuario($conn, $usuario_id);
    if ($dados) {
        $resposta = $dados;
    } else {
        $resposta['erro'] = 'Usuário não encontrado';
    }
}

// Função para buscar dados do usuário
function buscarDadosUsuario($conn, $usuario_id) {
    $stmt = $conn->prepare("SELECT nome, sobrenome, email, descricao, foto_perfil FROM pessoa WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($linha = $resultado->fetch_assoc()) {
        return $linha;
    }
    
    return null;
}

// Função para atualizar perfil
function atualizarPerfil($conn, $usuario_id) {
    global $resposta;
    
    $nome = trim($_POST['nome'] ?? '');
    $sobrenome = trim($_POST['sobrenome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    
    // Validações básicas
    if (empty($nome) || empty($sobrenome) || empty($email)) {
        $resposta['erro'] = 'Todos os campos obrigatórios devem ser preenchidos';
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $resposta['erro'] = 'Email inválido';
        return;
    }
    
    // Validar tamanho da descrição
    if (strlen($descricao) > 1000) {
        $resposta['erro'] = 'A descrição não pode ter mais de 1000 caracteres';
        return;
    }
    
    // Verificar se o email já existe para outro usuário
    $stmt = $conn->prepare("SELECT id FROM pessoa WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $resposta['erro'] = 'Este email já está sendo usado por outro usuário';
        return;
    }
    
    // Atualizar dados
    $stmt = $conn->prepare("UPDATE pessoa SET nome = ?, sobrenome = ?, email = ?, descricao = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nome, $sobrenome, $email, $descricao, $usuario_id);
    
    if ($stmt->execute()) {
        $resposta['sucesso'] = 'Perfil atualizado com sucesso';
    } else {
        $resposta['erro'] = 'Erro ao atualizar perfil: ' . $stmt->error;
    }
}

// Função para upload de foto
function uploadFoto($conn, $usuario_id) {
    global $resposta;
    
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $resposta['erro'] = 'Erro no upload da foto';
        return;
    }
    
    $arquivo = $_FILES['foto'];
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $tamanho_maximo = 5 * 1024 * 1024; // 5MB
    
    // Validar tipo de arquivo
    if (!in_array($arquivo['type'], $tipos_permitidos)) {
        $resposta['erro'] = 'Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP';
        return;
    }
    
    // Validar tamanho
    if ($arquivo['size'] > $tamanho_maximo) {
        $resposta['erro'] = 'Arquivo muito grande. Máximo 5MB';
        return;
    }
    
    // Criar diretório se não existir
    $diretorio_upload = '../uploads/fotos_perfil/';
    if (!file_exists($diretorio_upload)) {
        mkdir($diretorio_upload, 0755, true);
    }
    
    // Gerar nome único para o arquivo
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nome_arquivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extensao;
    $caminho_arquivo = $diretorio_upload . $nome_arquivo;
    
    // Remover foto anterior se existir
    $stmt = $conn->prepare("SELECT foto_perfil FROM pessoa WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($linha = $resultado->fetch_assoc()) {
        $foto_anterior = $linha['foto_perfil'];
        if ($foto_anterior && file_exists($foto_anterior)) {
            unlink($foto_anterior);
        }
    }
    
    // Mover arquivo
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
        // Atualizar banco de dados
        $stmt = $conn->prepare("UPDATE pessoa SET foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("si", $caminho_arquivo, $usuario_id);
        
        if ($stmt->execute()) {
            $resposta['sucesso'] = 'Foto atualizada com sucesso';
            $resposta['caminho_foto'] = $caminho_arquivo;
        } else {
            $resposta['erro'] = 'Erro ao salvar foto no banco de dados';
            unlink($caminho_arquivo); // Remove o arquivo se não conseguiu salvar no BD
        }
    } else {
        $resposta['erro'] = 'Erro ao salvar arquivo no servidor';
    }
}

// Função para remover foto
function removerFoto($conn, $usuario_id) {
    global $resposta;
    
    // Buscar caminho da foto atual
    $stmt = $conn->prepare("SELECT foto_perfil FROM pessoa WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($linha = $resultado->fetch_assoc()) {
        $foto_atual = $linha['foto_perfil'];
        
        // Remover foto do servidor
        if ($foto_atual && file_exists($foto_atual)) {
            unlink($foto_atual);
        }
        
        // Atualizar banco de dados
        $stmt = $conn->prepare("UPDATE pessoa SET foto_perfil = NULL WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            $resposta['sucesso'] = 'Foto removida com sucesso';
        } else {
            $resposta['erro'] = 'Erro ao remover foto do banco de dados';
        }
    }
}

// Função para excluir conta
function excluirConta($conn, $usuario_id) {
    global $resposta;
    
    // Buscar e remover foto de perfil se existir
    $stmt = $conn->prepare("SELECT foto_perfil FROM pessoa WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($linha = $resultado->fetch_assoc()) {
        $foto_perfil = $linha['foto_perfil'];
        if ($foto_perfil && file_exists($foto_perfil)) {
            unlink($foto_perfil);
        }
    }
    
    // Excluir usuário do banco
    $stmt = $conn->prepare("DELETE FROM pessoa WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    
    if ($stmt->execute()) {
        // Destruir sessão
        session_destroy();
        $resposta['sucesso'] = 'Conta excluída com sucesso';
    } else {
        $resposta['erro'] = 'Erro ao excluir conta: ' . $stmt->error;
    }
}

// Retornar resposta JSON
header('Content-Type: application/json');
echo json_encode($resposta);

$conn->close();
?>