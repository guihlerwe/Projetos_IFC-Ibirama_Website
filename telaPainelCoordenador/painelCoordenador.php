<?php
session_start();

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'coordenador') {
    header("Location: ../telaLogin/login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="hover-effect.css">
    <link rel="stylesheet" href="painelCoordenador.css">
    <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=1, user-scalable=no">
    <title>Projetos do Campus Ibirama</title>
</head>

<body>
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

        <div class="barra-pesquisar">
            <input type="text" class="input-pesquisar" placeholder="Pesquisar">
            <button class="btn-filtrar pesquisa">Pesquisa</button>
            <button class="btn-filtrar ensino">Ensino</button>
            <button class="btn-filtrar extensao">Extensão</button>
            
            <select class="categorias-filtrar">
                <option >Categorias</option>
                <option value="Ciências Exatas">Exatas</option>
                <option value="Ciências Humanas">Humanas</option>
                <option value="Linguagens">Linguagens</option>
                <option value="Matemática">Matemática</option>
                <option value="Administração">Administração</option>
                <option value="Informática">Informática</option>
                <option value="Vestuário/Moda">Vestuário/Moda</option>
            </select>    
        </div>



        <div class="projects-grid">
            <div class="project-card pesquisa">
                <img src="../telaPrincipal/img/peixario.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label azul">Projeto Peixário</div>
            </div>

            <div class="project-card extensao">
                <img src="../telaPrincipal/img/oficinaLinguistica.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label verde">Oficinas de Linguística Para Educadores</div>
            </div>

            <div class="project-card ensino">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Projeto bla bla bla bla</div>
            </div>


            <div class="project-card ensino">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Projeto bla bla bla bla</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>


            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Lorem ipsurrrrrrrrr</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Proje</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>


            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>


            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>


            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>

            <div class="project-card">
                <img src="../telaPrincipal/img/campus-image.jpg" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Projeto bla bla bla bla</div>
            </div>
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
    <script src="./painelCoordenador.js"></script>
</body>
</html>