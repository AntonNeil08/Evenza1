<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
class AuthController extends ResourceController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function sign_up()
    {
        $data = [
            'user_name' => $this->request->getPost('user_name'),
            'password'  => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'country'   => $this->request->getPost('country'),
            'state'     => $this->request->getPost('state'),
            'city'      => $this->request->getPost('city')
        ];
    
        $result = $this->userModel->createUser($data);
    
        if (!$result['success']) {
            return $this->respond([
                'success' => false,
                'message' => 'Validation failed.',
                'data'    => $result['errors']
            ], 400);
        }
    
        return $this->respond([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => []
        ], 201);
    }

    public function sign_in()
    {
        $userName = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');
    
        $user = $this->userModel->getUserByUsername($userName);
    
        if ($user && password_verify($password, $user['password'])) {
            return $this->respond([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'email' => $user['email']
                ]
            ]);
        }
    
        return $this->failNotFound('Invalid username or password');
    }
}