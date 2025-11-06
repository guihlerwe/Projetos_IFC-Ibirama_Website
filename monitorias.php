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

            <div class="project-card" data-categoria="tecnico">
                <img src="../assets/photos/monitoria/icones/adm.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Administração</div>
            </div>


            <div class="project-card" data-categoria="tecnico">
                <img src="../assets/photos/monitoria/icones/info.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Informática</div>
            </div>


            <div class="project-card" data-categoria="tecnico">
                <img src="../assets/photos/monitoria/icones/vest.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Vestuário</div>
            </div>

            <div class="project-card" data-categoria="superior">
                <img src="../assets/photos/monitoria/icones/comercial.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Gestão Comercial</div>
            </div>

            <div class="project-card" data-categoria="superior">
                <img src="../assets/photos/monitoria/icones/moda.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label">Moda</div>
            </div>


            <div class="project-card" data-categoria="geral">
                <img src="../assets/photos/monitoria/icones/humanas.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Ciências Humanas</div>
            </div>


            <div class="project-card" data-categoria="geral">
                <img src="../assets/photos/monitoria/icones/natureza.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Ciências da Natureza</div>
            </div>


            <div class="project-card" data-categoria="geral">
                <img src="../assets/photos/monitoria/icones/linguagens.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Linguagens e suas Tecnologias</div>
            </div>


            <div class="project-card" data-categoria="geral">
                <img src="../assets/photos/monitoria/icones/matemática.png" alt="Campus Ibirama" class="project-image">
                <div class="project-label vermelho">Matemática e suas Tecnologias</div>
            </div>
     
        </div>
    </div>

    
    <footer>
    <div class="linha">
        <div class="footer-container">
            <div class="Recursos">
                <h2>Recursos</h2>
                <ul>
                    <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                    <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                    <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                    <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                    <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                    <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
                </ul>
            </div>
            <div class="Comunidade">
                <h2>Comunidade</h2>
                <ul>
                    <li><a href="http://acessoainformacao.ifc.edu.br/">Acesso à Informação</a></li>
                    <li><a href="https://ifc.edu.br/comite-de-crise/">Calendários Acadêmicos</a></li>
                    <li><a href="https://cepsh.ifc.edu.br/">CEPSH</a></li>
                    <li><a href="https://consuper.ifc.edu.br/">Conselho Superior</a></li>
                    <li><a href="https://sig.ifc.edu.br/public/jsp/portal.jsf">Portal Público</a></li>
                    <li><a href="https://editais.ifc.edu.br/">Editais IFC</a></li>
                    <li><a href="http://www.camboriu.ifc.edu.br/pos-graduacao/treinador-e-instrutor-de-caes-guia/">Projetos Cães-guia</a></li>
                    <li><a href="https://trabalheconosco.ifc.edu.br/">Trabalhe no IFC</a></li>
                </ul>
            </div>
            <div class="Servidor">
                <h2>Servidor</h2>
                <ul>
                    <li><a href="https://ifc.edu.br/desenvolvimento-do-servidor/">Desenvolvimento do Servidor</a></li>
                    <li><a href="https://manualdoservidor.ifc.edu.br/">Manual do Servidor</a></li>
                    <li><a href="https://www.siapenet.gov.br/Portal/Servico/Apresentacao.asp">Portal SIAPENET</a></li>
                    <li><a href="http://suporte.ifc.edu.br/">Suporte TI</a></li>
                    <li><a href="https://sig.ifc.edu.br/sigrh/public/home.jsf">Sistema Integrado de Gestão (SIG)</a></li>
                    <li><a href="https://mail.google.com/mail/u/0/#inbox">Webmail</a></li>
                </ul>
            </div>
            <div class="Sites-Relacionados">
                <h2>Sites Relacionados</h2>
                <ul>
                    <li><a href="https://www.gov.br/pt-br">Brasil - GOV</a></li>
                    <li><a href="https://www.gov.br/capes/pt-br">CAPES - Chamadas Públicas</a></li>
                    <li><a href="https://www-periodicos-capes-gov-br.ez317.periodicos.capes.gov.br/index.php?">Capes - Portal de Periódicos</a></li>
                    <li><a href="https://www.gov.br/cnpq/pt-br">CNPq - Chamadas Públicas</a></li>
                    <li><a href="http://informativo.ifc.edu.br/">Informativo IFC</a></li>
                    <li><a href="https://www.gov.br/mec/pt-br">MEC - Ministério da Educação</a></li>
                    <li><a href="https://www.transparencia.gov.br/">Transparência Pública</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="Sobre">
        <h2>Sobre este site</h2>
        <span id="License"><i>Licença M.I.T.2025</i></span>
    </div>
    <div class="acesso-info">
        <a href="https://www.gov.br/acessoainformacao/pt-br">
            <img src="../assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
        </a>
    </div>
</footer>
    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/monitorias.js"></script>
</body>
</html>