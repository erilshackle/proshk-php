# proshk-php (php-procedural by shk)

Estrutura simples e flexível para desenvolvimento de sites em PHP puro, sem frameworks. Ideal para quem quer começar rápido com um projeto procedural e organizado.

## 📆 Estrutura

```md
php-procedural/
├── app/
│   ├── core/
│   ├── models/
│   └── utils/
├── backend/
│   ├── classes/
│   ├── configs/ 
│   ├── functions/
│   ├── autoload.php
|   ├── configs.php
│   └── helpers.php
├── page/               -> existem funcoes que começam com **page_** e pode ajudar a reutilizar os arquivos 
│   ├── errors/
│   ├── layouts/
│   └── templates/
├── public/
│   ├── assets/
│   ├── .htaccess
│   ├── @layout.php
│   └── index.php
│   └── ***
├── storage/
│   ├── cache/
│   ├── logs/
│   └── uploads/
├── vendor/
├── .env
├── backend.php
├── site.ini
├── composer.json
└── .htaccess
```

## ⚙️ Requisitos

- **PHP**: 8.1 ou superior
- **Servidor**: Apache ou servidor embutido do PHP (`php -S localhost:8000 -t public`)
- **Banco de dados**: SQLite, MySQL ou PostgreSQL (configurável)

## 🚀 Começando

1. Clone o repositório:

   ```bash
   git clone https://github.com/erilshackle/php-procedural.git
   cd php-procedural
   ```

2. Instale as dependências (se houver):

   ```bash
   composer install
   ```

3. Configure seu ambiente:

   - Renomeie `.env.example` para `.env` (se existir) ou edite diretamente os arquivos em `backend/configs/*`.

4. Inicie o servidor local:

   ```bash
   php -S localhost:8000 -t public
   ```

## 🛠 Como funciona

- Os arquivos `.php` devem ser criados na pasta `public/`.
- Cada arquivo criado deve incluir `@layout.php` (manual), que deve fazer o include de `backend.php`, responsável por carregar o bootstrap da aplicação.
- Toda a lógica e estrutura backend ficam organizadas nas pastas `app/` e `backend/`.

## 🛠 Utilitários

- Classe de Debug para uso rápido durante o desenvolvimento (ex: `dd($var)` ou `Debug::dump()`).
- Explore `backend/functions/*` para ver as funcoes disponivbilizadas para uso

## 📁 Exemplo de página

Crie um novo arquivo em `public/` chamado `contato.php`:

```php
<?php include "@layout.php"; ?>
@
<h1>contato</h1>
<p>Essa é a página de contato</p>
```

No `@layout.php`, certifique-se que o `backend.php` está sendo incluído corretamente:

```php
<?php 

require_once __DIR__ . '/../backend.php';

page_layouts_extends('main', ['title' => 'Contact Page']); // main.php site in page/layout/main.php...
```

Em `page/layout/main.php` crie seu layout e use `@layout('content')` para fazer o replace do conteudo no lugar que pretende

``` php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@layout('title')</title>
</head>
<body>
    <main class="container">
        @layout('content')
    </main>
</body>
</html>
```



## 📄 Licença

Este projeto está licenciado sob a licença MIT.

