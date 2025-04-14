<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProfileModel;
use App\Models\DepartmentModel;
use App\Models\ProgramModel;
use CodeIgniter\RESTful\ResourceController;

class CoordinatorController extends ResourceController
{
    protected $userModel;
    protected $profileModel;
    protected $departmentModel;
    protected $programModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->profileModel = new ProfileModel();
        $this->departmentModel = new DepartmentModel();
        $this->programModel = new ProgramModel();
    }

    /**
     * Fetch list of coordinators (active & deactivated) with department and program details
     */
    public function coordinatorsList()
    {
        $coordinators = $this->userModel
            ->select('user.id, user.email, user.user_type, 
                      profile.first_name, profile.middle_name, profile.last_name, profile.suffix, 
                      department.id as department_id, department.department_name, 
                      program.id as program_id, program.program_name')
            ->join('profile', 'profile.user_id = user.id')
            ->join('department', 'department.id = profile.department', 'left')
            ->join('program', 'program.id = profile.program', 'left')
            ->whereIn('user.user_type', [3, -3])
            ->findAll();

        return $this->respond(['success' => true, 'data' => $coordinators]);
    }

    /**
     * Update coordinator details (All fields optional)
     */
    public function updateCoordinator($id)
    {
        $data = $this->request->getRawInput();

        $coordinator = $this->userModel->find($id);
        if (!$coordinator || !in_array($coordinator['user_type'], [3, -3])) {
            return $this->fail(['message' => 'Coordinator not found.']);
        }

        $profile = $this->profileModel->where('user_id', $id)->first();
        if (!$profile) {
            return $this->fail(['message' => 'Coordinator profile not found.']);
        }

        // Validate department ID if provided
        if (!empty($data['department_id']) && !$this->departmentModel->find($data['department_id'])) {
            return $this->fail(['message' => 'Invalid department ID.']);
        }

        // Validate program ID if provided
        if (!empty($data['program_id']) && !$this->programModel->find($data['program_id'])) {
            return $this->fail(['message' => 'Invalid program ID.']);
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
        if (!empty($data['program_id'])) {
            $updateProfile['program'] = $data['program_id'];
        }

        if (!empty($updateProfile)) {
            $this->profileModel->update($id, $updateProfile);
        }

        $this->userModel->transComplete();

        if ($this->userModel->transStatus() === false) {
            return $this->fail(['message' => 'Failed to update coordinator.']);
        }

        return $this->respond(['success' => true, 'message' => 'Coordinator updated successfully.']);
    }
}
