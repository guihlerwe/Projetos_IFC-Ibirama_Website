<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cadProjeto.css">
    <title>Criar/Editar Projeto</title>
</head>
<body>
<script>
    sessionStorage.setItem('usuarioLogado', '<?php echo $nome; ?>');
    sessionStorage.setItem('tipoUsuario', '<?php echo $tipo; ?>');
</script>

<div class="container">
    <header>
        <div id="logo">
            <div id="icone-nav">
                <img src="../telaPrincipal/img/ifc-logo-preto.png" id="icone-ifc">
            </div>
            Projetos do Campus Ibirama
        </div>

        <div id="navegador">
            <div id="projetos-nav">Projetos</div>
            <div id="monitoria-nav">Monitoria</div>
            <div id="sobre-nav">Sobre</div>
            <?php include '../telaPrincipal/menuUsuario.php'; ?>
        </div>
    </header>

    <div id="banner">
        <label id="upload-banner">
            <input type="file" id="banner-projeto" name="banner" accept="image/*" hidden required>
            Clique para adicionar banner
        </label>
    </div>

    <form id="formulario" action="cadastrarBD.php" method="POST" enctype="multipart/form-data">

        <div id="info-projeto">
            <div id="div-capa">
                <label id="upload-capa">
                    <input type="file" id="foto-capa" name="capa" accept="image/*" hidden required>
                    📷
                </label>
            </div>
            <div id="dados-projeto">
                <div class="div-eixo--categoria-ano">
                    <select id="eixo" name="tipo">
                        <option value="">Tipo</option>
                        <option value="Ensino">Ensino</option>
                        <option value="Pesquisa">Pesquisa</option>
                        <option value="Extensao">Extensão</option>
                    </select>
                    <select id="categoria" name="categoria">
                        <option value="">Área de estudo</option>
                        <option value="Ciencias Naturais">Ciências Naturais</option>
                        <option value="Ciencias Humanas">Ciências Humanas</option>
                        <option value="Linguagens">Linguagens</option>
                        <option value="Matemática">Matemática</option>
                        <option value="Administração">Administração</option>
                        <option value="Informática">Informática</option>
                        <option value="Vestuário">Vestuário</option>
                    </select>
                    <input type="text" id="ano-inicio" name="anoInicio" placeholder="Desde (ano)">
                </div>
                <input type="text" id="nome-projeto" name="nome" placeholder="Nome do Projeto">
            </div>
            <input type="text" id="txt-link-inscricao" name="linkParaInscricao" placeholder="Link p/ formulário de inscrição">
        </div>

        <div id="conteudo">
            <h2 class="subtitulo">Sobre (2000 max.)</h2>
            <textarea id="descricao" name="textoSobre" maxlength="2000" placeholder="Descreva o projeto..."></textarea>

            <input type="text" id="site-projeto" name="linkSite" placeholder="Insira Link do site">

            <div class="equipe">
                <h2 class="subtitulo">Coordenadores(as)</h2>
                <div class="membros">
                    <div class="membro">
                        <div class="foto-membro">
                            <label>
                                <input type="file" accept="image/*" hidden>
                                📷
                            </label>
                        </div>
                        <input type="text" id="nome-coordenador" placeholder="Nome do coordenador(a)">
                    </div>
                    <div class="membro">
                        <div class="foto-membro add-membro">
                            +
                        </div>
                        <input type="text" class="nome-membro" placeholder="Adicionar nome">
                    </div>
                </div>
            </div>

            <div class="equipe">
                <h2 class="subtitulo">Bolsistas</h2>
                <div class="membros">
                    <div class="membro">
                        <div class="foto-membro add-membro">
                            <label>
                                <input type="file" accept="image/*" hidden>
                                📷
                            </label>
                        </div>
                        <input type="text" id="nome-bolsista" placeholder="Adicionar nome">
                    </div>
                </div>
            </div>

            <input type="text" id="link-bolsista" name="linkBolsista" placeholder="Se há vagas para bolsistas, cole o link para inscrição aqui">

            <div id="contato">
                <h2 class="subtitulo">Contato com Projeto</h2>
                <input type="email" id="email" name="email" placeholder="E-mail para o projeto">
                <input type="text" id="numero-telefone" name="numero" placeholder="Número para contato (opcional)">
                <input type="text" id="instagram" name="linkInstagram" placeholder="Instagram do projeto (opcional)">
            </div>

            <button type="submit" id="bt-criar-projeto">Criar Projeto</button>
        </div>
    </form>
</div>
<script src="./cadProjeto.js"></script>
</body>
</html>