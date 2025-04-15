<?php

namespace App\Controllers;
use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * GET /users/search?term=xxx
     * Returns all user_name values that match the search term
     */
    public function search()
    {
        $search = $this->request->getGet('term');
        $results = $this->userModel->getUsernames($search);

        return $this->respond([
            'success' => true,
            'data' => $results
        ]);
    }
}
