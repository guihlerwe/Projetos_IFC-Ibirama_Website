<?php
    // Conexão com o banco
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

    // Pega o token da URL
    $token = $_GET['token'] ?? '';

    if (empty($token)) {
        echo "Token inválido!";
        exit;
    }

    // Verifica se existe algum usuário com esse token
    $sql = "SELECT idPessoa, confirmado FROM pessoa WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Token inválido ou já utilizado!";
        exit;
    }

    $usuario = $result->fetch_assoc();

    if ($usuario['confirmado']) {
        echo "Este usuário já está confirmado!";
        exit;
    }

    // Atualiza o usuário para confirmado
    $sqlUpdate = "UPDATE pessoa SET confirmado = 1, token = NULL WHERE idPessoa = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $usuario['idPessoa']);

    if ($stmtUpdate->execute()) {
        // Redireciona para login com mensagem de sucesso
        header("Location: login.php?msg=confirmado");
        exit;
    } else {
        echo "Erro ao confirmar usuário: " . $stmtUpdate->error;
    }

    $stmt->close();
    $stmtUpdate->close();
    $conn->close();
?>
