<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';

// Conexão com o banco de dados (reaproveitado de outros arquivos do projeto)
$host = 'localhost';
$usuario = 'root';
//$senha = 'root';
$senha = 'Gui@15600';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Buscar coordenadores para popular o select
$coordenadores = [];
$sql = "SELECT idPessoa, nome, sobrenome, email FROM pessoa WHERE tipo = 'coordenador' ORDER BY nome, sobrenome";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $coordenadores[] = $row;
    }
    $result->free();
}

$bolsistas = [];
$sqlb = "SELECT idPessoa, nome, sobrenome, email FROM pessoa WHERE tipo = 'bolsista' ORDER BY nome, sobrenome";
if ($result = $conn->query($sqlb)) {
    while ($row = $result->fetch_assoc()) {
        $bolsistas[] = $row;
    }
    $result->free();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/tema-global.css">
    <link rel="stylesheet" href="../assets/css/cad-projeto.css">
    <title>Criar/Editar Projeto</title>
</head>
<body>
<script>
    sessionStorage.setItem('usuarioLogado', '<?php echo $nome; ?>');
    sessionStorage.setItem('tipoUsuario', '<?php echo $tipo; ?>');
</script>

<div class="container">
    <header>
        <div class="logo">
            <div class="icone-nav">
                <img src="../assets/photos/ifc-logo-preto.png" id="icone-ifc">
            </div>
            Projetos do Campus Ibirama
        </div>
        <div class="navegador">
            <div class="projetos-nav">Projetos</div>
            <div class="monitoria-nav">Monitoria</div>
            <?php include 'menuUsuario.php'; ?>
        </div>
    </header>

    <form id="formulario" action="cadastrarBD.php" method="POST" enctype="multipart/form-data">
        
        <div id="banner" style="position: relative; width: 100%; height: 200px; background-color: #f0f0f0; overflow: hidden;">
            <label id="upload-banner" style="display: block; width: 100%; height: 100%; cursor: pointer; position: relative;">
                <input type="file" id="banner-projeto" name="banner" accept="image/*" hidden>
                <span id="banner-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2; color: #666; font-size: 16px;">Clique para adicionar banner</span>
                <img id="banner-preview" style="display: none;">
            </label>
        </div>

        <div id="info-projeto">
            <div id="div-capa">
                <label id="upload-capa">
                    <input type="file" id="foto-capa" name="capa" accept="image/*" hidden required>
                    <span id="capa-icon">📷</span>
                    <img id="capa-preview" style="display: none;">
                </label>
            </div>
            <div id="dados-projeto">
                <div class="div-eixo--categoria-ano">
                    <select id="eixo" name="eixo" required>
                        <option value="">Tipo</option>
                        <option value="ensino">Ensino</option>
                        <option value="pesquisa">Pesquisa</option>
                        <option value="extensao">Extensão</option>
                    </select>
                    <select id="categoria" name="categoria" required>
                        <option value="">Área de estudo</option>
                        <option value="ciencias_naturais">Ciências Naturais</option>
                        <option value="ciencias_humanas">Ciências Humanas</option>
                        <option value="linguagens">Linguagens</option>
                        <option value="matematica">Matemática</option>
                        <option value="administracao">Administração</option>
                        <option value="informatica">Informática</option>
                        <option value="vestuario">Vestuário</option>
                    </select>
                    <input type="text" id="ano-inicio" name="ano-inicio" placeholder="Desde (ano)">
                </div>
                <input type="text" id="nome-projeto" name="nome-projeto" placeholder="Nome do Projeto" required>
            </div>
            <input type="text" id="txt-link-inscricao" name="txt-link-inscricao" placeholder="Link p/ formulário de inscrição">
        </div>

        <div id="conteudo">
            <h2 class="subtitulo">Sobre (2000 max.)</h2>
            <textarea id="descricao" name="descricao" maxlength="2000" placeholder="Descreva o projeto..."></textarea>

            <input type="text" id="site-projeto" name="site-projeto" placeholder="Insira Link do site">

            <div class="equipe">
                <h2 class="subtitulo">Coordenadores(as)</h2>
                <div class="membros">
                    <div class="membro">
                        <div class="foto-membro">
                            <label>
                                <input type="file" name="foto-coordenador" accept="image/*" hidden>
                                <span>📷</span>
                            </label>
                        </div>
                            <div class="form-group">
                                            <select name="coordenador_id">
                                                <option value="">Selecione um coordenador...</option>
                                                <?php foreach ($coordenadores as $coord): ?>
                                                    <option value="<?php echo $coord['idPessoa']; ?>">
                                                        <?php echo htmlspecialchars($coord['nome'] . ' ' . $coord['sobrenome'] . ' (' . $coord['email'] . ')'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                </div>                    
                            </div>
                    </div>
            </div>

            <div class="equipe">
                <h2 class="subtitulo">Bolsistas</h2>
                <div class="membros">
                    <div class="membro">
                        <div class="foto-membro">
                            <label>
                                <input type="file" name="foto-bolsista" accept="image/*" hidden>
                                <span>📷</span>
                            </label>
                        </div>
                        <div class="form-group">
                                            <select name="bolsista_id">
                                                <option value="">Selecione um Bolsista...</option>
                                                <?php foreach ($bolsista as $bolsis): ?>
                                                    <option value="<?php echo $bolsis['idPessoa']; ?>">
                                                        <?php echo htmlspecialchars($bolsis['nome'] . ' ' . $bolsis['sobrenome'] . ' (' . $coord['email'] . ')'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                </div>
                    </div>
                </div>
            </div>

            <input type="text" id="link-bolsista" name="link-bolsista" placeholder="Se há vagas para bolsistas, cole o link para inscrição aqui">

            <div id="contato">
                <h2 class="subtitulo">Contato com Projeto</h2>
                <input type="email" id="email" name="email" placeholder="E-mail para o projeto">
                <input type="text" id="numero-telefone" name="numero-telefone" placeholder="Número para contato (opcional)">
                <input type="text" id="instagram" name="instagram" placeholder="Instagram do projeto (opcional)">
            </div>

            <button type="submit" id="bt-criar-projeto">Criar Projeto</button>
        </div>
    </form>
</div>
<script src="./assets/js/global.js"></script>
<script src="./assets/js/cad-aluno.js"></script>
<?php
// Fechar conexão com o banco
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

</body>
</html>