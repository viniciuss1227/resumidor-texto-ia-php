# Resumidor de Texto com IA – PHP + MySQL

Aplicação web em PHP que consome uma API externa de Inteligência Artificial para gerar resumos automáticos de textos em português, com histórico das últimas interações salvo em banco de dados MySQL. A interface é simples e focada em usabilidade, permitindo que o usuário cole qualquer texto, envie para a IA e visualize o resumo gerado na mesma página.

##  O Diferencial Técnico

Diferente de um formulário PHP básico, este projeto demonstra:

- **Integração com API de IA**: Consumo de endpoint REST de sumarização de texto via cURL em PHP, enviando o texto original em JSON e recebendo o resumo de volta também em JSON.
- **Persistência em MySQL**: Armazenamento de texto original, resumo e data/hora em uma tabela `historico`, permitindo exibir um histórico recente de resumos na própria interface.
- **Boas práticas de segurança**: Uso de *prepared statements* (`prepare` + `bind_param`) para evitar SQL Injection e extração do token da API para um arquivo de configuração não versionado (`config.php`).
- **UI com foco em leitura**: Estilização em CSS com paleta profissional em tons de azul, áreas destacadas para o resumo principal e para o histórico, e efeitos sutis de foco/hover.

##  Tecnologias Utilizadas

- **Backend**
  - **PHP 8+** rodando em servidor Apache (XAMPP).
  - **cURL** em PHP para chamadas HTTP à API de IA.
  - **MySQL** para persistência dos textos e resumos.
- **Frontend**
  - **HTML5** para estrutura semântica da página.
  - **CSS3** (arquivo `style.css`) com foco em legibilidade, contraste e responsividade básica.
- **Ferramentas**
  - **MySQL Workbench** para modelagem e criação do banco/tabela.
  - **APYHub** como provedor da API de sumarização de texto.

##  Como Funciona a Lógica

1. O usuário cola um texto no formulário e envia.
2. O PHP recebe o texto via `$_POST["texto"]` e monta uma requisição HTTP para a API de IA:
   - Método **POST**.
   - Corpo em JSON com o campo `"text"` e `"output_language": "pt_BR"`.
3. A requisição é enviada via cURL e a resposta JSON é decodificada com `json_decode`, extraindo o campo de resumo.
4. O texto original e o resumo são gravados na tabela `historico` usando prepared statement.
5. Na mesma página, o usuário vê:
   - O **resumo atual** em destaque.
   - Um **histórico recente** das últimas interações, com texto original, resumo e data/hora.

##  Estrutura do Banco de Dados

O projeto utiliza um banco de dados MySQL chamado `resume` com uma tabela `historico`.

O script SQL está disponível no arquivo `database.sql` e tem a seguinte estrutura:

```sql
CREATE DATABASE resume;

USE resume;

CREATE TABLE IF NOT EXISTS historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto LONGTEXT NOT NULL,
    resumo LONGTEXT NOT NULL,
    datahora DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

##  Configuração de Credenciais (APYHub)

Por segurança, o token da API **não é versionado** no GitHub.

1. Acesse [https://apyhub.com](https://apyhub.com), crie uma conta e gere sua própria API key de sumarização de texto.
2. Duplique o arquivo `config.example.php` e renomeie para `config.php`.
3. Edite o `config.php` e preencha com seu token real:

```php
<?php
$APY_TOKEN = 'SUA_CHAVE_REAL_AQUI';
```

> Importante: o arquivo `config.php` está listado no `.gitignore` e **não** deve ser commitado. Cada pessoa que clonar o repositório deve criar o seu próprio `config.php` com a sua chave.

##  Como Rodar Localmente

### Pré-requisitos

- XAMPP (ou Apache + PHP + MySQL instalados).
- MySQL (local) em execução.
- Opcional: MySQL Workbench para importar o script do banco.

### 1. Clonar o repositório

```bash
git clone https://github.com/viniciuss1227/resumidor-texto-ia-php.git
cd resumidor-texto-ia-php
```


### 2. Configurar o banco de dados

1. Inicie o MySQL pelo XAMPP.
2. Abra o **MySQL Workbench** (ou phpMyAdmin) e conecte na instância local.
3. Importe/execute o arquivo `database.sql` para criar:
   - O banco `resume`.
   - A tabela `historico`.

### 3. Configurar o token da API

1. Gere sua API key no ApyHub.
2. Crie o arquivo `config.php` a partir do `config.example.php`:
   ```php
   <?php
   $APY_TOKEN = 'SUA_CHAVE_REAL_AQUI';
   ```
3. Garanta que `config.php` **não** foi adicionado ao Git (o `.gitignore` já cuida disso por padrão).

### 4. Rodar o projeto no XAMPP

1. Copie a pasta do projeto para o diretório `htdocs` do XAMPP (ex.: `C:\xampp\htdocs\resumidor-texto-ia-php`).
2. Inicie **Apache** e **MySQL** no painel do XAMPP.
3. Acesse no navegador:

```text
http://localhost/resumidor-texto-ia-php/index.php
```

Cole um texto, clique para gerar o resumo e confira também o histórico de interações.

##  Exemplos de Uso

- Colar um parágrafo longo de um artigo acadêmico e obter um resumo mais curto e direto.
- Resumir instruções extensas para gerar uma versão “TL;DR” mais fácil de revisar.

##  Contexto Acadêmico e Papel no Projeto

Este projeto foi desenvolvido como trabalho de faculdade em uma disciplina de integração de IA com PHP, cujo objetivo era construir uma aplicação web que consumisse uma API de Inteligência Artificial externa e, opcionalmente, persistisse dados em MySQL.

Embora a atividade previsse grupos de 5 a 7 pessoas, **assumi a implementação completa do código (PHP, integração com API, MySQL e frontend) e também a apresentação em sala**, explicando o fluxo da requisição, o uso do cURL, o processamento da resposta da IA e a gravação dos dados no banco. Isso me permitiu exercitar tanto a parte técnica quanto a comunicação do raciocínio por trás das decisões de implementação.