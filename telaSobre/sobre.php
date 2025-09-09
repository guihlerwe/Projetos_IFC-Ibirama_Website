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
    <link rel="stylesheet" href="sobre.css">
    <title>Sobre o Campus Ibirama e este site</title>
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


            <div class="navegador">
                <div class="projetos-nav">Projetos</div>
                <div class="monitoria-nav">Monitoria</div>
                <div class="sobre-nav">Sobre</div>
                <div class="login-nav"> <?php include '../telaPrincipal/menuUsuario.php'; ?> </div>
            </div>
        </header>
        
        <div class="content">
            <div class="section">
                <div>
                        <img  id="capa" src="../telaPrincipal/img/campus-image.jpg" alt="Imagem do campus">
                    </div>
                <h2 class="section-title">Campus IFC Ibirama</h2>
                <div class="section-layout">
                    <div>
                        <p class="section-text">
                            O Campus Ibirama inaugurado em 2010, contém os cursos de Tecnologia da Informação, Administração e Vestuário para os Discentes. No cenário atual Brasileiro esses cursos são cada vez mais requeridos e importantes para a geração de inovações tecnológicas, transformações empresariais, e avanços internacional na moda.

                            Hoje o campus é formado por mais de dezenas de profissionais, que garantem uma educação de excelência, com métodos de aprendizados contemporâneos e engajadores aos alunos tanto nas matérias dos Cursos quanto nas do Ensino Médio.
                        </p>
                        <a href="https://ibirama.ifc.edu.br/" class="site-link">
                            <img class="site-link-icon" src = "../telaPrincipal/img/ifc-logo-colorida.png" viewBox="0 0 24 24" fill="currentColor"> 
                        </img>
                            Site oficial do IFC Ibirama
                        </a>
                    </div>
                    <div class="section-image">
                        <img src="../telaPrincipal/img/fachada-ifc-ibirama-1024x576.jpg" alt="Imagem do campus">
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-layout">
                    <div class="section-image">
                        <img src="../telaPrincipal/img/campus-image.jpg" alt="Estudantes" id="Criadores">
                    </div>

                    <div>
                        <h2 class="section-title">Este site e o projeto de TCC</h2>
                        <p class="section-text">
                        Este site foi criado de uma necessidade que Gabriella e Guilherme encontraram ao entrar no IFC – Campus Ibirama, o conhecimento de projetos ativos que os interessassem e os horários  das monitorias do conteúdos que precisavam. Já que a divulgação dos mesmos não era tão efetiva, gerando uma falta de alunos e um grande tempo gasto dos bolsistas, tendo que passar de sala em sala para a divulgação dos projetos de ensino e extensão.

                        Portanto, com o objetivo de divulgar e dar igualdade ao acesso das informações dos projetos ativos de ensino, pesquisa, extensão – e das monitorias, este site reúne todos os projetos deixando visível se há inscrições abertas para participação e vagas para bolsista ou voluntários ao alcance de clique, e reúne as monitorias e seus horários, email dos monitores . Além de disponibilizar as descrições, as datas, as categorias de áreas de interesse em que os projetos se encaixam, os meios de contato com os realizadores do projeto, os coordenadores, os bolsistas e o link para o site do projeto (se o projeto ter). Contudo este TCC integrou diversos conhecimentos aprendidos no curso de Tecnologia de Informação, provando a capacidade de solução de problemas de Gabriella e Guilherme.                        </p>

                        <div>

                            <a href="https://www.linkedin.com/in/gabriella-sandner-0a5737363" class="team-member-link">
                                <svg class="linkedin-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"></path>
                                </svg>
                                Gabriella Schmilla Sandner
                            </a>

                            <a href="https://www.linkedin.com/in/guihlerwe/" class="team-member-link">
                                <svg class="linkedin-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"></path>
                                </svg>
                                Guilherme Raimundo
                            </a>

                        </div>
                    </div>
                </div>
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
    <script src="./sobre.js"></script>

</body>
</html>