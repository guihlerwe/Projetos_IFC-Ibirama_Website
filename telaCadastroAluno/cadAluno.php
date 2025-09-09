<?php
    // conectando com o banco
    $host = 'localhost';
    $usuario = 'root';
    $senha = 'Gui@15600';
    $banco = 'website';

    $conn = new mysqli($host, $usuario, $senha, $banco);

    // verificando conexão
    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    // recebe os dados do formulário
    $nome = $_POST["nome"];
    $sobrenome = $_POST["sobrenome"];
    $email = $_POST["email"];
    $senha = $_POST['senha'];
    //$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); 

    // inserção
    $stmt = $conn->prepare("INSERT INTO pessoa (nome, sobrenome, email, senha) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $sobrenome, $email, $senha);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso! Redirecionando...";
        header("refresh:2; url=../telaLogin/login.html");
        exit();
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
?>
