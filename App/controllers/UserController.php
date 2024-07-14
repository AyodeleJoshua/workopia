<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;

class UserController
{
    protected $db;

    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    /**
     * Show login page
     * 
     * @return void
     */
    public function login()
    {
        loadView('users/login');
    }

    /**
     * Show the register page
     * 
     * return void
     */
    public function create()
    {
        loadView('users/create');
    }

    /**
     * Store user in database
     * 
     * @return void
     */
    public function store()
    {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $password = $_POST['password'];
        $passwordConfirmation = $_POST['password_confirmation'];

        $errors = [];

        // Validation
        if (!Validation::email($email)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        if (!Validation::string($name, 2, 50)) {
            $errors['name'] = 'Name must be between 2 and 50 characters';
        }

        if (!Validation::string($password, 6, 50)) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if (!Validation::match($password, $passwordConfirmation)) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            loadView('users/create', ['user' => ['name' => $name, 'email' => $email, 'city' => $city, 'state' => $state, 'password' => $password, 'password_confirmation' => $passwordConfirmation], 'errors' => $errors]);
            exit;
        }

        // Check if email exists
        $user = $this->db->query("SELECT * FROM users WHERE email = :email", ['email' => $email])->fetch();

        if ($user) {
            $errors['email'] = 'That email already exists';
            loadView('users/create', ['user' => ['name' => $name, 'email' => $email, 'city' => $city, 'state' => $state, 'password' => $password, 'password_confirmation' => $passwordConfirmation], 'errors' => $errors]);
            exit;
        }

        // Create user account
        $params = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'city' => $city,
            'state' => $state
        ];

        $this->db->query("INSERT INTO users (name, email, password, city, state) VALUES (:name, :email, :password, :city, :state)", $params);

        redirect('/listings/login');

    }
}
