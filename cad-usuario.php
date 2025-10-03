<?php
    // conectando com o banco
    $host = 'localhost';
    $usuario = 'root';
    //$senha = 'Gui@15600';
    $senha = 'root';
    $banco = 'website';

    $conn = new mysqli($host, $usuario, $senha, $banco);

    // verificando conexão
    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/cad-usuario.css">
    <title>Cadastrar-se como aluno</title>
</head>
<body>

    <div class="container">
        <header>
            <div class="logo">
                <div class="grid-icon">
                    <img src="../assets/photos/ifc-logo-preto.png" id="icone-ifc">
                </div>
                Projetos do Campus Ibirama
            </div>
            <div class="navegador">
                <div class="projetos-nav">Projetos</div>
                <div class="monitoria-nav">Monitoria</div>
                <div class="login-nav">Entrar</div>
            </div>
        </header>

        <!-- Formulário -->
        <div class="container-formulario">
            <div class="divformulario">
                <form action="cad-usuario.php" method="POST">
                    <div class="imagem-container">
                            <img src="../assets/photos/campus-image.jpg" id="foto-ifc">
                            <h1 class="titulo-sobre-imagem">Cadastro</h1>
                        </div>
                    <p class="subtitulo">Preencha seus dados</p>
                    <fieldset class="grupo">
                        <div class="campoEsquerda">
                            <input type="text" name="nome" class="campo" id="nome" placeholder="Nome" required>
                        </div>
        
                        <div class="campoDireita">
                            <input type="text" name="sobrenome" class="campo" id="sobrenome" placeholder="Sobrenome" required>
                        </div>
                    </fieldset>
        
                    <fieldset class="grupo">
                        <div class="campoEsquerda">
                            <input type="email" name="email" class="campo" id="email" placeholder="E-mail" required>
                        </div>
        
                        <div class="campoDireita">
                            <input type="password" name="senha" class="campo" id="senha" placeholder="Senha" required>
                        </div>
                    </fieldset>

                    <p class="subtitulo">Como você deseja cadastrar-se?</p>
                    <div class="radio-group">
                        <div class="custom-select">
                            <div class="radio-container">
                                <label>
                                    <input type="radio" name="usuario" value="aluno"> Aluno
                                </label>
                                <label>
                                    <input type="radio" name="usuario" value="coordenador"> Coordenador
                                </label>
                            </div>

                        </div>
                        <input type="hidden" name="tipo" id="inputTipo">
                    </div>

                    <div id="camposAluno" style="display:none;">
                        <div class="custom-select" id="curso-aluno">
                        <div class="select-selected">Curso</div>
                        <div class="select-items">
                            <div data-value="administracao">Administração</div>
                            <div data-value="informatica">Informática</div>
                            <div data-value="vestuario">Vestuário</div>
                            <div data-value="moda">Moda</div>
                            <div data-value="gestao-comercial">Gestão Comercial</div>
                        </div>
                        </div>
                        <input type="hidden" name="curso" id="inputCurso">

                        <input type="text" name="matricula" class="campo" placeholder="Digite sua matrícula">
                    </div>

                    <div id="camposCoordenador" style="display:none;">
                    <div class="custom-select" id="area-coordenador">
                        <div class="select-selected">Área de estudo</div>
                        <div class="select-items">
                            <div data-value="ciencias-naturais">Ciências Naturais</div>
                            <div data-value="ciencias-humanas">Ciências Humanas</div>
                            <div data-value="linguagens">Linguagens</div>
                            <div data-value="matematica">Matemática</div>
                            <div data-value="tecnico-administrativo">Técnico Administrativo</div>
                        </div>
                        </div>
                        <input type="hidden" name="area" class="campo" id="inputArea">
                    </div>

        
                    <button type="submit" class="botao">Cadastrar-se</button>
                </form>
            </div>
        </div>

        <footer>
            <div class="Aluno">
                <h2>Recursos</h2>
                <ul id="menu-aluno" >
                    <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                    <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                    <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                    <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                    <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                    <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
                </ul>

                <span id="License">Licença M.I.T.</span>
                    <span>2025</span>					
            </div>
        </footer>
        
    </div>

    <script src="./assets/js/global.js"></script>
    <script src="./assets/js/cad-usuario.js"></script>
</body>

</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // pegando os dados do formulário
    $nome = $_POST['nome'] ?? '';
    $sobrenome = $_POST['sobrenome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // se quiser validar
    if (empty($nome) || empty($sobrenome) || empty($email) || empty($senha)) {
        echo "Preencha todos os campos!";
    } else {
        // exemplo de cadastro no banco
        $sql = "INSERT INTO pessoa (nome, sobrenome, email, senha, tipo) VALUES (?, ?, ?, ?, 'aluno')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nome, $sobrenome, $email, $senha);
        
        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso!";
            // redirecionar se quiser: header("Location: login.php");
        } else {
            echo "Erro: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>
