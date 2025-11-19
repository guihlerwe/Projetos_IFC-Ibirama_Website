<?php
/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/



// contaBD.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1); // Mudar para 0 em produção
error_reporting(E_ALL);

require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
$idPessoa = $_SESSION['idPessoa'] ?? null;
if (!$idPessoa) {
    echo json_encode(['erro' => 'Usuário não autenticado.']);
    exit;
}

// Conexão com o banco
$host = 'localhost';
$usuario = 'root';
$senha = 'yourpasswordhere';
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

const RESET_TOKEN_PREFIX = 'RS';
const RESET_TOKEN_TOTAL_BYTES = 16; // mantém token em 32 caracteres hex
const RESET_TOKEN_TTL = 3600; // 1 hora

function gerar_token_reset(): string {
    $expireAt = time() + max(60, RESET_TOKEN_TTL);
    $prefix = RESET_TOKEN_PREFIX;
    $randomLength = RESET_TOKEN_TOTAL_BYTES - strlen($prefix) - 4; // 4 bytes reservados para timestamp
    $payload = $prefix . pack('N', $expireAt) . random_bytes($randomLength);
    return bin2hex($payload);
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
    $senhaConfirmacao = isset($_POST['senha_confirmacao']) ? trim((string)$_POST['senha_confirmacao']) : '';

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

    if ($senhaConfirmacao === '') {
        resposta_json(['erro' => 'Informe sua senha para confirmar as alterações.']);
    }

    $stmtDadosAtuais = $conn->prepare("SELECT senha, email FROM pessoa WHERE idPessoa = ? LIMIT 1");
    if (!$stmtDadosAtuais) {
        resposta_json(['erro' => 'Não foi possível validar os dados atuais.']);
    }
    $stmtDadosAtuais->bind_param("i", $idPessoa);
    if (!$stmtDadosAtuais->execute()) {
        $stmtDadosAtuais->close();
        resposta_json(['erro' => 'Falha ao buscar dados do usuário.']);
    }

    $stmtDadosAtuais->bind_result($hashAtual, $emailAtualDb);
    $temRegistro = $stmtDadosAtuais->fetch();
    $stmtDadosAtuais->close();

    if (!$temRegistro || !$hashAtual || !password_verify($senhaConfirmacao, $hashAtual)) {
        resposta_json(['erro' => 'Senha incorreta.']);
    }

    $emailAtual = trim((string)($emailAtualDb ?? ''));
    $emailAlterado = strcasecmp($emailAtual, $email) !== 0;

    if ($emailAlterado) {
        $stmt = $conn->prepare("SELECT idPessoa FROM pessoa WHERE email = ? AND idPessoa != ?");
        $stmt->bind_param("si", $email, $idPessoa);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            resposta_json(['erro' => 'Este email já está sendo usado por outro usuário.']);
        }
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

    $sql = "UPDATE pessoa SET nome = ?, sobrenome = ?, email = ?, descricao = ?, curso = ?, area = ?, matricula = ?";
    $params = [$nome, $sobrenome, $email, $descricao, $curso, $area, $matricula];
    $tipos = "sssssss";

    if ($caminhoFoto) {
        $sql .= ", foto_perfil = ?";
        $params[] = $caminhoFoto;
        $tipos .= "s";
    }

    $sql .= " WHERE idPessoa = ?";
    $params[] = $idPessoa;
    $tipos .= "i";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        resposta_json(['erro' => 'Erro interno (prepare).']);
    }

    $stmt->bind_param($tipos, ...$params);

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
    $senhaConfirmacao = isset($_POST['senha_confirmacao']) ? trim((string)$_POST['senha_confirmacao']) : '';

    if ($senhaConfirmacao === '') {
        resposta_json(['erro' => 'Informe sua senha para confirmar a exclusão da conta.']);
    }

    // Verificar senha
    $stmtDadosAtuais = $conn->prepare("SELECT senha, foto_perfil FROM pessoa WHERE idPessoa = ? LIMIT 1");
    if (!$stmtDadosAtuais) {
        resposta_json(['erro' => 'Não foi possível validar os dados atuais.']);
    }
    $stmtDadosAtuais->bind_param("i", $idPessoa);
    if (!$stmtDadosAtuais->execute()) {
        $stmtDadosAtuais->close();
        resposta_json(['erro' => 'Falha ao buscar dados do usuário.']);
    }

    $stmtDadosAtuais->bind_result($hashAtual, $foto);
    $temRegistro = $stmtDadosAtuais->fetch();
    $stmtDadosAtuais->close();

    if (!$temRegistro || !$hashAtual || !password_verify($senhaConfirmacao, $hashAtual)) {
        resposta_json(['erro' => 'Senha incorreta.']);
    }

    // Remove foto física
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

if ($acao === 'solicitar_reset') {
    $stmtDados = $conn->prepare("SELECT nome, email FROM pessoa WHERE idPessoa = ? LIMIT 1");
    $stmtDados->bind_param("i", $idPessoa);
    $stmtDados->execute();
    $info = $stmtDados->get_result()->fetch_assoc();
    $stmtDados->close();

    if (!$info) {
        resposta_json(['erro' => 'Usuário não encontrado.']);
    }

    $token = gerar_token_reset();

    $stmtToken = $conn->prepare("UPDATE pessoa SET token = ? WHERE idPessoa = ?");
    if (!$stmtToken) {
        resposta_json(['erro' => 'Não foi possível gerar o token.']);
    }
    $stmtToken->bind_param("si", $token, $idPessoa);

    if (!$stmtToken->execute()) {
        resposta_json(['erro' => 'Erro ao salvar token de redefinição.']);
    }
    $stmtToken->close();

    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($basePath === '.') {
        $basePath = '';
    }
    $linkReset = $protocolo . '://' . $host . $basePath . '/redefinir_senha.php?token=' . urlencode($token);

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->Username = 'projetos.ifc.ibirama@gmail.com';
        $mail->Password = 'jsfi pcrf zumq xfcv';
        $mail->setFrom('projetos.ifc.ibirama@gmail.com', 'IFC Projetos');
        $mail->addAddress($info['email'], $info['nome']);
        $mail->isHTML(true);
        $mail->Subject = 'Redefinição de senha - Projetos IFC Ibirama';
        $mail->Body = '<p>Olá, ' . htmlspecialchars($info['nome']) . '!</p>' .
            '<p>Recebemos uma solicitação para redefinir sua senha. Clique no botão abaixo em até 1 hora:</p>' .
            '<p><a href="' . $linkReset . '" style="display:inline-block;padding:12px 18px;background:#1e4d2b;color:#fff;text-decoration:none;border-radius:6px;">Redefinir senha</a></p>' .
            '<p>Se o botão não funcionar, copie e cole este endereço no navegador:</p>' .
            '<p>' . htmlspecialchars($linkReset) . '</p>' .
            '<p>Se você não solicitou, ignore este e-mail.</p>';
        $mail->AltBody = "Olá, {$info['nome']}! Acesse o link para redefinir sua senha: {$linkReset}";
        $mail->send();
    } catch (Exception $e) {
        resposta_json(['erro' => 'Não foi possível enviar o e-mail: ' . $mail->ErrorInfo]);
    }

    resposta_json(['sucesso' => 'Enviamos um e-mail com instruções para redefinir a senha.']);
}

resposta_json(['erro' => 'Ação inválida ou não informada.']);
$conn->close();