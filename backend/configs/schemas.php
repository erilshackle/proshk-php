<?php

// Define o tipo de banco de dados (mysql, sqlite, pgsql, etc.)
Schema::setType(env('DB_CONNECTION', 'sqlite'));

// Criação da tabela 'users'
$users = Schema::create('users', function (Schema $table) {
    $table->column_primary('id', 'integer')  // Definindo a chave primária (id)
        ->column('username', 'string')
        ->column('password', 'string')
        ->column('email', 'string')
        ->timestamps()
        ->softDeletes()
        ->unique('email');  // Garantindo que o e-mail seja único
});

// Criação da tabela 'roles'
$roles = Schema::create('roles', function (Schema $table) {
    $table->column_primary('id', 'integer')  // Definindo a chave primária
        ->column('name', 'string')
        ->column('description', 'string', false, null)
        ->unique('name');  // Garantindo que o nome da função seja único
});

// Criação da tabela 'user_roles' para o relacionamento entre usuários e funções
$user_roles = Schema::create('user_roles', function (Schema $table) use ($roles, $users) {
    $table->column_foreign('user_id', $users, 'id')  // Definindo a chave estrangeira para o usuário
        ->column_foreign('role_id', $roles, 'id')  // Definindo a chave estrangeira para o papel
        ->primaryKey(['user_id', 'role_id'])  // Definindo uma chave primária composta
        ->unique(['user_id', 'role_id']);  // Garantindo a unicidade do par user_id + role_id
});

// Criação da tabela 'profiles'
$profiles = Schema::create('profiles', function (Schema $table) use ($users) {
    $table->column_foreign('user_id', $users)  // Definindo a chave estrangeira para o usuário
        ->column('name', 'string')
        ->column('avatar', 'string')
        ->column('birthdate', 'date', false, null)
        ->timestamps()
        ->primaryKey('user_id');  // Definindo a chave primária como 'user_id'
});



// Retorna um array com os esquemas definidos
return array_filter(get_defined_vars(), fn($table) => $table instanceof Schema);
