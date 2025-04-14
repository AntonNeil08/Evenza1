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
            'user_name'  => $this->request->getPost('user_name'),
            'full_name'  => $this->request->getPost('full_name'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'email'      => $this->request->getPost('email'),
            'country'    => $this->request->getPost('country'),
            'city'       => $this->request->getPost('city')
        ];
    
        $this->userModel->errors = []; 
    
        $success = $this->userModel->createUser($data);
    
        if ($success) {
            return $this->respondCreated([
                'status'    => 'success',
                'message'   => 'User created successfully',
                'user_name' => $data['user_name']
            ]);
        }
    
        log_message('error', 'Validation failed: ' . json_encode($this->userModel->errors()));
        return $this->failValidationErrors($this->userModel->errors());
    }
    public function sign_in()
    {
        $userName = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');
    
        $user = $this->userModel->getUserByUsername($userName);
    
        if ($user && password_verify($password, $user['password'])) {
            return $this->respond(['status' => 'success', 'message' => 'Login successful']);
        } else {
            return $this->failNotFound('Invalid username or password');
        }
    }
}