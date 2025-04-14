<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuthModel;
use App\Models\ProfileModel;
use CodeIgniter\RESTful\ResourceController;

class AdminController extends ResourceController
{
    protected $userModel;
    protected $authModel;
    protected $profileModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->userModel = new UserModel();
        $this->profileModel = new ProfileModel();
    }

    /**
     * Fetch list of admins (active & deactivated)
     */
    public function adminsList()
    {
        $admins = $this->userModel
            ->select('user.id, user.email, user.user_type, profile.first_name, profile.middle_name, profile.last_name, profile.suffix')
            ->join('profile', 'profile.user_id = user.id')
            ->whereIn('user.user_type', [1, -1])
            ->findAll();

        return $this->respond(['success' => true, 'data' => $admins]);
    }

    /**
     * Update admin details
     */
    public function updateAdmin($id)
    {
        $data = $this->request->getRawInput();

        $admin = $this->userModel->find($id);
        if (!$admin || !in_array($admin['user_type'], [1, -1])) {
            return $this->fail(['message' => 'Admin not found.']);
        }

        $profile = $this->profileModel->where('user_id', $id)->first();
        if (!$profile) {
            return $this->fail(['message' => 'Admin profile not found.']);
        }

        $this->userModel->transStart();

        // Update `user` table (Only update provided values)
        $updateUser = [];
        if (!empty($data['email'])) {
            $updateUser['email'] = $data['email'];
        }
        if (!empty($updateUser)) {
            $this->userModel->update($id, $updateUser);
        }

        // Update `profile` table (Only update provided values)
        $updateProfile = [];
        if (!empty($data['first_name'])) {
            $updateProfile['first_name'] = $data['first_name'];
        }
        if (!empty($data['middle_name'])) {
            $updateProfile['middle_name'] = $data['middle_name'];
        }
        if (!empty($data['last_name'])) {
            $updateProfile['last_name'] = $data['last_name'];
        }
        if (!empty($data['suffix'])) {
            $updateProfile['suffix'] = $data['suffix'];
        }

        if (!empty($updateProfile)) {
            $this->profileModel->update($id, $updateProfile);
        }

        $this->userModel->transComplete();

        if ($this->userModel->transStatus() === false) {
            return $this->fail(['message' => 'Failed to update admin.']);
        }

        return $this->respond(['success' => true, 'message' => 'Admin updated successfully.']);
    }

}
