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
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/monitorias.css">
    <title>Monitorias</title>
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
                Monitores do Campus Ibirama
            </div>
            <div class="navegador">
                <div class="projetos-nav">Projetos</div>
                <div class="monitoria-nav">Monitoria</div>
                <div class="login-nav"id"login-nav"><?php include 'menuUsuario.php'; ?></div>
            </div>
        </header>

        <div class="barra-pesquisar">
            <input type="text" class="input-pesquisar" placeholder="Pesquisar">
            <button class="btn-filtrar tecnico">Área Técnica Integrada</button>
            <button class="btn-filtrar geral">Ensino Médio</button>
            <button class="btn-filtrar superior">Superior</button>
        </div>



        <div class="projects-grid">

            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/adm.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Administração</div>
            </div>


            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/info.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Informática</div>
            </div>


            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/vest.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Vestuário</div>
            </div>

            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/comercial.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Gestão Comercial</div>
            </div>

            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/moda.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Moda</div>
            </div>


            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/humanas.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Ciências Humanas</div>
            </div>


            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/natureza.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Ciências da Natureza</div>
            </div>


            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/linguagens.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Linguagens e suas Tecnologias</div>
            </div>


            <div class="project-card">
                <img src="../assets/photos/monitoria/icones/matemática.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Matemática e suas Tecnologias</div>
            </div>
     
        </div>
    </div>

    <footer>
        <div class="container footer-container">
            <div class="Aluno">
                <h2>Recursos</h2>
                <ul id="menu-aluno">
                    <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                    <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                    <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                    <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                    <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                    <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
                </ul>
            </div>

            <div class="Sobre">
                <h2>Sobre este site</h2>
                <p> 
                    O Campus Ibirama, inaugurado em 2010, com dezenas de profissionais, proporciona uma educação de 
                    qualidade e oferece cursos de Tecnologia da Informação, Administração e Vestuário, 
                    que são importantes para inovações e negócios.  

                    <b id="gab">Gabriella</b> e <b id="gui">Guilherme</b> criaram um site para facilitar o acesso a informações sobre projetos e monitorias,
                    que antes eram pouco divulgados. O site reúne dados sobre inscrições, horários de monitorias e contatos 
                    dos responsáveis pelos projetos, mostrando a aplicação de conhecimentos do curso de Tecnologia da Informação.
                </p>
                <span id="License"><i>Licença M.I.T.2025</i></span>
            </div>
            
        </div>
    </footer>
    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/monitorias.js"></script>
</body>
</html>