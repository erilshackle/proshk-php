<?php

// This file defines the schema for the entities in the application
// It includes the table names, primary keys, and validation rules for each entity
// The schema is used to ensure that the data in the database is consistent and valid
// It is important to keep this file updated as the application evolves
/** @uses \EntityModel  -> EntityModel::use()*/


return [
    'User' => [
        'table' => 'users',
        'primary_key' => 'id',
        'rules' => [
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'email' => 'required|email|unique:users',
        ]
    ],
    'Profile' => [
        'table' => 'profiles',
        'primary_key' => 'id',
        'rules' => [
            'name' => 'required',
            'user_id' => 'required|exists:users,id'
        ]
    ]
    // Adicione mais entidades conforme necessário
];
