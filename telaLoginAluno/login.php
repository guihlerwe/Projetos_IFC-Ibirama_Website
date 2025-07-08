<?php
    session_start();

    // conexão
    $host = 'localhost';
    $usuario = 'root';
    $senha = '';
    $banco = 'website';

    $conn = new mysqli($host, $usuario, $senha, $banco);

    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    // pegando os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // consultando no banco se já existe um usuário cadastrado
    $sql = "SELECT * FROM pessoa WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($usuario = $result->fetch_assoc()) {
        // verificando a senha
        if (password_verify($senha, $usuario['senha'])) {
            // salvando na sessão
            $_SESSION['idPessoa'] = $usuario['idPessoa'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $usuario['tipo'];

            // Redireciona conforme o tipo
            if ($usuario['tipo'] == 'coordenador') {
                header("Location: ../painelCoordenador/painel.html"); 
            } else {
                header("Location: ../telaPrincipal/principal.html");
            }
            exit();
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }

    $stmt->close();
    $conn->close();
?>
