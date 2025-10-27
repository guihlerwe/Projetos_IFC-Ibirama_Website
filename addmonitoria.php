<?php
session_start();

// Verificar se é coordenador ou admin
if (!isset($_SESSION['tipo']) || !in_array($_SESSION['tipo'], ['coordenador', 'colaborador'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$mensagem = '';
$erro = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $textoSobre = $_POST['textoSobre'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $email = $_POST['email'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $capa = $_POST['capa'] ?? '';
    $banner = $_POST['banner'] ?? '';
    $coordenador_id = $_POST['coordenador_id'] ?? '';
    $monitores_ids = $_POST['monitores'] ?? [];
    
    if ($nome && $textoSobre && $categoria) {
        // Inserir monitoria
        $stmt = $conn->prepare("
            INSERT INTO projeto (anoInicio, nome, textoSobre, email, numero, capa, banner, tipo, categoria) 
            VALUES (YEAR(CURDATE()), ?, ?, ?, ?, ?, ?, 'ensino', ?)
        ");
        $stmt->bind_param("sssssss", $nome, $textoSobre, $email, $numero, $capa, $banner, $categoria);
        
        if ($stmt->execute()) {
            $projeto_id = $conn->insert_id;
            
            // Adicionar coordenador
            if ($coordenador_id) {
                $stmt2 = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'coordenador')");
                $stmt2->bind_param("ii", $coordenador_id, $projeto_id);
                $stmt2->execute();
                $stmt2->close();
            }
            
            // Adicionar monitores
            if (!empty($monitores_ids)) {
                $stmt3 = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'bolsista')");
                foreach ($monitores_ids as $monitor_id) {
                    $stmt3->bind_param("ii", $monitor_id, $projeto_id);
                    $stmt3->execute();
                }
                $stmt3->close();
            }
            
            $mensagem = "Monitoria cadastrada com sucesso! ID: " . $projeto_id;
        } else {
            $erro = "Erro ao cadastrar monitoria: " . $conn->error;
        }
        $stmt->close();
    } else {
        $erro = "Preencha todos os campos obrigatórios!";
    }
}

// Buscar pessoas para selecionar coordenadores e monitores
$pessoas = $conn->query("SELECT idPessoa, nome, sobrenome, email, tipo FROM pessoa ORDER BY nome");
$coordenadores = [];
$possiveis_monitores = [];

while ($pessoa = $pessoas->fetch_assoc()) {
    if (in_array($pessoa['tipo'], ['coordenador', 'colaborador'])) {
        $coordenadores[] = $pessoa;
    }
    if (in_array($pessoa['tipo'], ['aluno', 'bolsista'])) {
        $possiveis_monitores[] = $pessoa;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Monitoria</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2e7d32;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        input[type="text"],
        input[type="email"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .checkbox-group {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }
        
        .checkbox-item {
            margin-bottom: 8px;
        }
        
        .checkbox-item input {
            margin-right: 8px;
        }
        
        button {
            background: #2e7d32;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        button:hover {
            background: #1b5e20;
        }
        
        .btn-secondary {
            background: #666;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        .mensagem {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Adicionar Nova Monitoria</h1>
        
        <?php if ($mensagem): ?>
            <div class="mensagem sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <?php if ($erro): ?>
            <div class="mensagem erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nome da Monitoria <span class="required">*</span></label>
                <input type="text" name="nome" required placeholder="Ex: Monitoria de Matemática">
            </div>
            
            <div class="form-group">
                <label>Descrição <span class="required">*</span></label>
                <textarea name="textoSobre" required placeholder="Descreva os objetivos e atividades da monitoria..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Categoria <span class="required">*</span></label>
                <select name="categoria" required>
                    <option value="">Selecione...</option>
                    <option value="matematica">Matemática</option>
                    <option value="informatica">Informática</option>
                    <option value="linguagens">Linguagens</option>
                    <option value="ciencias-naturais">Ciências Naturais</option>
                    <option value="ciencias-humanas">Ciências Humanas</option>
                    <option value="administracao">Administração</option>
                    <option value="vestuario">Vestuário</option>
                    <option value="moda">Moda</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Email de Contato</label>
                <input type="email" name="email" placeholder="monitoria@ifc.edu.br">
            </div>
            
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="numero" placeholder="(47) 3357-8484">
            </div>
            
            <div class="form-group">
                <label>URL da Imagem de Capa</label>
                <input type="text" name="capa" placeholder="../assets/photos/monitorias/capa.jpg">
            </div>
            
            <div class="form-group">
                <label>URL da Imagem de Banner</label>
                <input type="text" name="banner" placeholder="../assets/photos/monitorias/banner.jpg">
            </div>
            
            <div class="form-group">
                <label>Coordenador</label>
                <select name="coordenador_id">
                    <option value="">Selecione um coordenador...</option>
                    <?php foreach ($coordenadores as $coord): ?>
                        <option value="<?php echo $coord['idPessoa']; ?>">
                            <?php echo htmlspecialchars($coord['nome'] . ' ' . $coord['sobrenome'] . ' (' . $coord['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Monitores (Bolsistas)</label>
                <div class="checkbox-group">
                    <?php foreach ($possiveis_monitores as $monitor): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" name="monitores[]" value="<?php echo $monitor['idPessoa']; ?>" 
                                   id="monitor_<?php echo $monitor['idPessoa']; ?>">
                            <label for="monitor_<?php echo $monitor['idPessoa']; ?>" style="display: inline; font-weight: normal;">
                                <?php echo htmlspecialchars($monitor['nome'] . ' ' . $monitor['sobrenome'] . ' (' . $monitor['email'] . ')'); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit">Cadastrar Monitoria</button>
                <button type="button" class="btn-secondary" onclick="window.history.back()">Voltar</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>