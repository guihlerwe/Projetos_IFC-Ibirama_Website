<?php
    session_start();

    // conexão
    $host = 'localhost';
    $usuario = 'root';
    $senha = 'root';
    $banco = 'website';

    $conn = new mysqli($host, $usuario, $senha, $banco);

    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    // pegando os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    //$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // consultando no banco se já existe um usuário cadastrado
    $sql = "SELECT * FROM pessoa WHERE email = ? and senha = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senha);
    //print_r($stmt);
    $stmt->execute();
        

    $result = $stmt->get_result();
    //print_r($result->num_rows);
    if($result->num_rows > 0){
        $usuario = $result->fetch_assoc();

        //if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['idPessoa'] = $usuario['idPessoa'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $usuario['tipo'];
            
            echo($usuario['nome']);
            if ($usuario['tipo'] == 'coordenador') {
                header("Location: ../telaPainelCoordenador/painelCoordenador.php"); 
            } elseif ($usuario['tipo'] === 'bolsista') {
                header("Location: ../telaPainelBolsista/painelBolsista.php");
            } else {
                    header("Location: ../telaPrincipal/principal.php");
            }

        //} else {
            //echo "Senha incorreta!";
        //} 

    } else {
        echo "Usuário não encontrado!";
    }

    $stmt->close();
    $conn->close();
?>
