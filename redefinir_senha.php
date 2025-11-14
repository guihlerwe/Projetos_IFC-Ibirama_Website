<?php
session_start();

$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
//$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}
$conn->set_charset('utf8');

const RESET_TOKEN_PREFIX = 'RS';
const RESET_TOKEN_BYTES = 16;

function buscarUsuarioComTokenReset(mysqli $conn, string $tokenHex): ?array {
    if (!preg_match('/^[a-f0-9]{32}$/i', $tokenHex)) {
        return null;
    }

    $bin = @hex2bin($tokenHex);
    if ($bin === false || strlen($bin) !== RESET_TOKEN_BYTES) {
        return null;
    }

    $prefixLen = strlen(RESET_TOKEN_PREFIX);
    if (strncmp($bin, RESET_TOKEN_PREFIX, $prefixLen) !== 0) {
        return null;
    }

    $expData = unpack('Nexpira', substr($bin, $prefixLen, 4));
    if (!$expData) {
        return null;
    }

    $expira = (int)$expData['expira'];
    if ($expira < time()) {
        return null;
    }

    $stmt = $conn->prepare("SELECT idPessoa FROM pessoa WHERE token = ? AND confirmado = 1 LIMIT 1");
    $stmt->bind_param('s', $tokenHex);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        return null;
    }

    return [
        'idPessoa' => (int)$usuario['idPessoa'],
        'expira' => $expira,
    ];
}

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$mensagem = '';
$erro = '';
$exibirFormulario = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = trim($_POST['nova_senha'] ?? '');
    $confirmacao = trim($_POST['confirmar_senha'] ?? '');

    if (!$token) {
        $erro = 'Token ausente.';
    } elseif ($novaSenha === '' || $confirmacao === '') {
        $erro = 'Informe e confirme a nova senha.';
    } elseif ($novaSenha !== $confirmacao) {
        $erro = 'As senhas não conferem.';
    } elseif (strlen($novaSenha) < 8) {
        $erro = 'A senha deve possuir pelo menos 8 caracteres.';
    } else {
        $usuario = buscarUsuarioComTokenReset($conn, $token);

        if (!$usuario) {
            $erro = 'Token inválido ou expirado.';
            $exibirFormulario = false;
        } else {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmtUpdate = $conn->prepare("UPDATE pessoa SET senha = ?, token = NULL WHERE idPessoa = ?");
            $stmtUpdate->bind_param('si', $senhaHash, $usuario['idPessoa']);
            if ($stmtUpdate->execute()) {
                $mensagem = 'Senha redefinida com sucesso! Você já pode fazer login.';
                $exibirFormulario = false;
            } else {
                $erro = 'Não foi possível redefinir a senha. Tente novamente.';
            }
            $stmtUpdate->close();
        }
    }
} else {
    if (!$token) {
        $erro = 'Token não informado.';
        $exibirFormulario = false;
    } else {
        $usuario = buscarUsuarioComTokenReset($conn, $token);
        if (!$usuario) {
            $erro = 'Token inválido ou expirado.';
            $exibirFormulario = false;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha</title>
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <style>
        body {
            background: var(--primary-bg, #f7f7f7);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        .reset-container {
            max-width: 420px;
            margin: 80px auto;
            background: var(--card-bg, #fff);
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            font-size: 1.8rem;
            text-align: center;
        }
        .input-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid var(--input-border, #dcdcdc);
            background: var(--input-bg, #fff);
            color: var(--input-text, #222);
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #1e4d2b;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
        }
        .mensagem {
            margin-bottom: 16px;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }
        .mensagem.erro { background: #fdecea; color: #c0392b; }
        .mensagem.sucesso { background: #e5f6ed; color: #238636; }
        .link-login {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #1e4d2b;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h1>Redefinir senha</h1>

        <?php if ($erro): ?>
            <div class="mensagem erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <?php if ($mensagem): ?>
            <div class="mensagem sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
            <a class="link-login" href="login.php">Ir para o login</a>
        <?php endif; ?>

        <?php if ($exibirFormulario): ?>
            <form method="POST" action="redefinir_senha.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="input-group">
                    <label for="nova_senha">Nova senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" minlength="8" required>
                </div>
                <div class="input-group">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" required>
                </div>
                <button type="submit">Salvar nova senha</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
