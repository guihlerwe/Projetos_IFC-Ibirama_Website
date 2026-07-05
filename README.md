# Site de Monitoramento de Projetos do IFC Ibirama / IFC Ibirama Projects Monitoring Website

[Português](#português) | [English](#english)

---

## Português

### Sobre

Este projeto é uma plataforma web projetada para centralizar e facilitar o acesso às informações sobre projetos de ensino, pesquisa e extensão, além dos horários de monitoria do IFC (Instituto Federal Catarinense) - Campus Ibirama. O sistema foi desenvolvido como trabalho de conclusão de curso (TCC) para resolver o problema da divulgação pouco efetiva de projetos e oportunidades de monitoria.

### Funcionalidades

- **Gerenciamento de Projetos**: Cadastro, edição e visualização de projetos de ensino, pesquisa e extensão
- **Sistema de Monitoria**: Gerenciamento de horários de monitoria com informações sobre monitores e disponibilidade
- **Autenticação de Usuários**: Sistema de cadastro separado para alunos e coordenadores com verificação de email
- **Controle de Acesso**: Diferentes níveis de permissão (aluno, coordenador, bolsista, voluntário)
- **Filtragem Automática**: Busca e filtragem de projetos por tipo, categoria e palavras-chave
- **Design Responsivo**: Adaptação automática de tema claro/escuro

### Tecnologias Utilizadas

- **Backend**: PHP 8.x com MySQLi
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Email**: [PHPMailer](https://github.com/PHPMailer/PHPMailer) v6.x
- **Arquitetura**: Padrão MVC com separação de responsabilidades

### Pré-requisitos

- PHP 8.0 ou superior (com servidor de desenvolvimento embutido)
- MySQL 5.7+
- MySQL Workbench (recomendado para gerenciamento do banco de dados)
- Conta SMTP válida para envio de emails (Gmail configurado por padrão)

### Instalação

1. **Clone o repositório**
```bash
git clone https://github.com/g-mundo/Projetos_IFC-Ibirama_Website.git
cd Projetos_IFC-Ibirama_Website
```

2. **Configure o banco de dados**

Abra o MySQL Workbench e execute o script de criação do banco:

**Opção 1: Usando MySQL Workbench (Recomendado)**
- Abra o MySQL Workbench e conecte-se ao seu servidor MySQL
- Vá em `File` → `Open SQL Script`
- Navegue até `assets/creating the db/UseThisForTheDB.txt`
- Clique no ícone de raio (⚡) para executar todo o script
- Isso criará o banco `website` com todas as tabelas e relacionamentos

**Nota**: O script principal do banco (`UseThisForTheDB.txt`) cria todas as tabelas necessárias:
- `pessoa` (usuários)
- `projeto` (projetos)
- `monitoria` (monitorias)
- `pessoa_projeto` (relacionamentos usuário-projeto)
- `monitoria_pessoa` (relacionamentos monitoria-usuário)

3. **Configure as credenciais do banco**

Edite os seguintes arquivos e atualize as credenciais de conexão:
- `cad-usuario.php`
- `login.php`
- `cad-projetoBD.php`
- `contaBD.php`
- E outros arquivos PHP que conectam ao banco

```php
$host = 'localhost';
$usuario = 'root';
$senha = 'SUA_SENHA';
$banco = 'website';
```

4. **Configure o email (PHPMailer)**

Edite `cad-usuario.php` e configure suas credenciais SMTP:
```php
$mail->Username = 'seu-email@gmail.com';
$mail->Password = 'sua-senha-de-app';
$mail->setFrom('seu-email@gmail.com', 'IFC Projetos');
```

Atualize o link de verificação para corresponder ao seu servidor local:
```php
$linkConfirmacao = "http://localhost:8080/confirmar_usuario.php?token=$token";
```

**Nota**: Para Gmail, você precisa gerar uma [Senha de App](https://support.google.com/accounts/answer/185833?hl=pt-BR).

5. **Configure as permissões** (apenas Linux/Mac)
```bash
chmod 755 assets/photos/
chmod 755 assets/photos/projetos/
chmod 755 assets/photos/fotos_perfil/
chmod 755 assets/photos/monitoria/
```

6. **Inicie o servidor local**
```bash
php -S localhost:8080
```

Acesse: `http://localhost:8080/principal.php`

**Nota**: Este projeto está configurado para desenvolvimento local e utiliza o MySQL Workbench para gerenciamento do banco de dados.

### Estrutura do Projeto

```
Projetos_IFC-Ibirama_Website/
├── assets/
│   ├── bd/              # Scripts do banco de dados
│   ├── css/             # Folhas de estilo
│   ├── js/              # Arquivos JavaScript
│   ├── photos/          # Imagens e uploads
│   └── font-family/     # Fontes personalizadas
├── PHPMailer/           # Biblioteca PHPMailer (incluída)
├── *.php                # Páginas e controladores PHP
├── LICENSE.TXT          # Licença MIT
└── README.md            # Este arquivo
```

### Recursos de Segurança

- Hash de senhas com `password_hash()`
- Proteção contra SQL injection com prepared statements
- Validação de domínio de email (alunos: @estudantes.ifc.edu.br, coordenadores: @ifc.edu.br)
- Verificação de conta via email
- Exclusão automática de contas não verificadas após 10 minutos
- Autenticação baseada em sessão
- Controle de acesso por tipo de usuário

### Bibliotecas de Terceiros

Este projeto utiliza a seguinte biblioteca:

#### PHPMailer
- **Versão**: 6.x
- **Licença**: LGPL-2.1
- **Repositório**: https://github.com/PHPMailer/PHPMailer
- **Propósito**: Envio de emails de verificação e notificações
- **Créditos**: Marcus Bointon, Jim Jagielski, Andy Prevost e contribuidores

O PHPMailer está incluído no diretório `PHPMailer/` com todos os arquivos e licenças necessários.

### Autores

- **Gabriella Schmilla Sandner** - [LinkedIn](https://www.linkedin.com/in/gabriella-sandner-0a5737363)
- **Guilherme Raimundo** - [LinkedIn](https://www.linkedin.com/in/raimund08guilherme/)

### Coordenador

- **Rodrigo Ramos Nogueira** 

### Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE.TXT](LICENSE.TXT) para detalhes.

### Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou enviar pull requests.
---

## English

### About

This project is a web platform designed to centralize and facilitate access to information about teaching, research, and extension projects, as well as tutoring schedules at IFC (Instituto Federal Catarinense) - Campus Ibirama. The system was developed as a final course project (TCC) to solve the problem of ineffective dissemination of academic projects and tutoring opportunities.

### Features

- **Project Management**: Registration, editing, and visualization of teaching, research, and extension projects
- **Tutoring System**: Management of tutoring schedules with information about monitors and availability
- **User Authentication**: Separate registration system for students and coordinators with email verification
- **Access Control**: Different permission levels (student, coordinator, scholarship holder, volunteer)
- **Automatic Filtering**: Search and filter projects by type, category, and keywords
- **Responsive Design**: Automatic light/dark theme adaptation

### Technologies Used

- **Backend**: PHP 8.x with MySQLi
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Email**: [PHPMailer](https://github.com/PHPMailer/PHPMailer) v6.x
- **Architecture**: MVC pattern with separation of concerns

### Prerequisites

- PHP 8.0 or higher (with built-in development server)
- MySQL 5.7+ or MariaDB 10.3+
- MySQL Workbench (recommended for database management)
- Valid SMTP account for sending emails (Gmail configured by default)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/g-mundo/Projetos_IFC-Ibirama_Website.git
cd Projetos_IFC-Ibirama_Website
```

2. **Configure the database**

Open MySQL Workbench and execute the database creation script:

**Option 1: Using MySQL Workbench (Recommended)**
- Open MySQL Workbench and connect to your MySQL server
- Go to `File` → `Open SQL Script`
- Navigate to `assets/creating the db/UseThisForTheDB.txt`
- Click on the lightning bolt icon (⚡) to execute the entire script
- This will create the `website` database with all tables and relationships

**Note**: The main database script (`UseThisForTheDB.txt`) creates all necessary tables including:
- `pessoa` (users)
- `projeto` (projects)
- `monitoria` (tutoring)
- `pessoa_projeto` (user-project relationships)
- `monitoria_pessoa` (tutoring-user relationships)

3. **Configure database credentials**

Edit the following files and update the connection credentials:
- `cad-usuario.php`
- `login.php`
- `cad-projetoBD.php`
- `contaBD.php`
- And other PHP files that connect to the database

```php
$host = 'localhost';
$usuario = 'root';
$senha = 'YOUR_PASSWORD';
$banco = 'website';
```

4. **Configure email (PHPMailer)**

Edit `cad-usuario.php` and configure your SMTP credentials:
```php
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->setFrom('your-email@gmail.com', 'IFC Projects');
```

Update the verification link to match your local server:
```php
$linkConfirmacao = "http://localhost:8080/confirmar_usuario.php?token=$token";
```

**Note**: For Gmail, you need to generate an [App Password](https://support.google.com/accounts/answer/185833).

5. **Configure permissions** (Linux/Mac only)
```bash
chmod 755 assets/photos/
chmod 755 assets/photos/projetos/
chmod 755 assets/photos/fotos_perfil/
chmod 755 assets/photos/monitoria/
```

6. **Start the local server**
```bash
php -S localhost:8080
```

Access: `http://localhost:8080/principal.php`

**Note**: This project is configured for local development and uses MySQL Workbench for database management.

### Project Structure

```
Projetos_IFC-Ibirama_Website/
├── assets/
│   ├── bd/              # Database scripts
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   ├── photos/          # Images and uploads
│   └── font-family/     # Custom fonts
├── PHPMailer/           # PHPMailer library (included)
├── *.php                # PHP pages and controllers
├── LICENSE.TXT          # MIT License
└── README.md            # This file
```

### Security Features

- Password hashing with `password_hash()`
- SQL injection protection with prepared statements
- Email domain validation (students: @estudantes.ifc.edu.br, coordinators: @ifc.edu.br)
- Account verification via email
- Automatic deletion of unverified accounts after 10 minutes
- Session-based authentication
- Access control by user type

### Third-Party Libraries

This project uses the following library:

#### PHPMailer
- **Version**: 6.x
- **License**: LGPL-2.1
- **Repository**: https://github.com/PHPMailer/PHPMailer
- **Purpose**: Sending verification and notification emails
- **Credits**: Marcus Bointon, Jim Jagielski, Andy Prevost, and contributors

PHPMailer is included in the `PHPMailer/` directory with all necessary files and licenses.

### Authors

- **Gabriella Schmilla Sandner** - [LinkedIn](https://www.linkedin.com/in/gabriella-sandner-0a5737363)
- **Guilherme Raimundo** - [LinkedIn](https://www.linkedin.com/in/raimund08guilherme/)

### Coordinator

- **Rodrigo Ramos Nogueira** 

### 📄 License

This project is licensed under the MIT License - see the [LICENSE.TXT](LICENSE.TXT) file for details.

### Contributing

Contributions are welcome! Feel free to open issues or submit pull requests.


**Copyright © 2025 Guilherme Raimundo & Gabriella Schmilla Sandner**
