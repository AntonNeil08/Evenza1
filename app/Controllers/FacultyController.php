<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProfileModel;
use App\Models\DepartmentModel;
use CodeIgniter\RESTful\ResourceController;

class FacultyController extends ResourceController
{
    protected $userModel;
    protected $profileModel;
    protected $departmentModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->profileModel = new ProfileModel();
        $this->departmentModel = new DepartmentModel();
    }

    /**
     * Fetch list of faculty members (active & deactivated)
     */
    public function facultyList()
    {
        $faculty = $this->userModel
            ->select('user.id, user.email, user.user_type, 
                      profile.first_name, profile.middle_name, profile.last_name, profile.suffix, 
                      department.id as department_id, department.department_name')
            ->join('profile', 'profile.user_id = user.id')
            ->join('department', 'department.id = profile.department', 'left')
            ->whereIn('user.user_type', [4, -4, 6, -6])
            ->findAll();

        return $this->respond(['success' => true, 'data' => $faculty]);
    }

    /**
     * Create a new faculty member
     */

    /**
     * Update faculty details (All fields optional)
     */
    public function updateFaculty($id)
    {
        $data = $this->request->getRawInput();

        $faculty = $this->userModel->find($id);
        if (!$faculty || !in_array($faculty['user_type'], [4, -4])) {
            return $this->fail(['message' => 'Faculty member not found.']);
        }

        $profile = $this->profileModel->where('user_id', $id)->first();
        if (!$profile) {
            return $this->fail(['message' => 'Faculty profile not found.']);
        }

        // Validate department ID if provided
        if (!empty($data['department_id']) && !$this->departmentModel->find($data['department_id'])) {
            return $this->fail(['message' => 'Invalid department ID.']);
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
        if (!empty($data['department_id'])) {
            $updateProfile['department'] = $data['department_id'];
        }

        if (!empty($updateProfile)) {
            $this->profileModel->update($id, $updateProfile);
        }

        $this->userModel->transComplete();

        if ($this->userModel->transStatus() === false) {
            return $this->fail(['message' => 'Failed to update faculty member.']);
        }

        return $this->respond(['success' => true, 'message' => 'Faculty member updated successfully.']);
    }
}
