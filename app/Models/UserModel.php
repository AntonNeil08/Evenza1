<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $allowedFields = [
        'user_name', 
        'password',
        'full_name',
        'email', 
        'country', 
        'city'
    ];
    
    protected $validationRules = [
        'user_name' => 'required|is_unique[user.user_name]',
        'full_name' => 'required',
        'password'  => 'required',
        'email'     => 'required|valid_email|is_unique[user.email]',
        'country'   => 'required',
        'city'      => 'required'
    ];
    
    protected $validationMessages = [
        'user_name' => [
            'required'  => 'Username is required.',
            'is_unique' => 'Username already exists.'
        ],
        'email' => [
            'required'    => 'Email is required.',
            'valid_email' => 'Invalid email format.',
            'is_unique'   => 'Email already exists.'
        ]
    ];
    /**
     * Inserts a new user
     */
    public function createUser($data)
    {
        $this->errors = [];
        return $this->insert($data);
    }
    /**
     * Fetch a user by username
     */
    public function getUserByUsername($userName)
    {
        return $this->where('user_name', $userName)->first();
    }
}
