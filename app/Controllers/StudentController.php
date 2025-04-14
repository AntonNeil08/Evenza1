<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ProfileModel;
use App\Models\DepartmentModel;
use App\Models\ProgramModel;
use App\Models\YearLevelModel;
use App\Models\SectionModel;
use CodeIgniter\RESTful\ResourceController;

class StudentController extends ResourceController
{
    protected $userModel;
    protected $profileModel;
    protected $departmentModel;
    protected $programModel;
    protected $yearLevelModel;
    protected $sectionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->profileModel = new ProfileModel();
        $this->departmentModel = new DepartmentModel();
        $this->programModel = new ProgramModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->sectionModel = new SectionModel();
    }

    /**
     * Fetch list of students (active & deactivated) with department, program, year level, section, and is_regular status
     */
    public function studentsList()
    {
        $students = $this->userModel
            ->select('user.id, user.email, user.user_type, 
                      profile.first_name, profile.middle_name, profile.last_name, profile.suffix, profile.is_regular,
                      department.id as department_id, department.department_name,
                      program.id as program_id, program.program_name,
                      year_level.id as year_level_id, year_level.year_level,
                      section.id as section_id, section.section_name')
            ->join('profile', 'profile.user_id = user.id')
            ->join('department', 'department.id = profile.department', 'left')
            ->join('program', 'program.id = profile.program', 'left')
            ->join('year_level', 'year_level.id = profile.year_level', 'left')
            ->join('section', 'section.id = profile.section', 'left')
            ->whereIn('user.user_type', [5, -5])
            ->findAll();

        return $this->respond(['success' => true, 'data' => $students]);
    }

    /**
     * Update student details (All fields optional, except is_regular and user_type)
     */
    public function updateStudent($id)
    {
        $data = $this->request->getRawInput();

        $student = $this->userModel->find($id);
        if (!$student || !in_array($student['user_type'], [5, -5])) {
            return $this->fail(['message' => 'Student not found.']);
        }

        $profile = $this->profileModel->where('user_id', $id)->first();
        if (!$profile) {
            return $this->fail(['message' => 'Student profile not found.']);
        }

        // Validate relationships if provided
        if (!empty($data['department_id']) && !$this->departmentModel->find($data['department_id'])) {
            return $this->fail(['message' => 'Invalid department ID.']);
        }
        if (!empty($data['program_id']) && !$this->programModel->find($data['program_id'])) {
            return $this->fail(['message' => 'Invalid program ID.']);
        }
        if (!empty($data['year_level_id']) && !$this->yearLevelModel->find($data['year_level_id'])) {
            return $this->fail(['message' => 'Invalid year level ID.']);
        }
        if (!empty($data['section_id']) && !$this->sectionModel->find($data['section_id'])) {
            return $this->fail(['message' => 'Invalid section ID.']);
        }

        unset($data['is_regular'], $data['user_type']); // Ensure `is_regular` and `user_type` are NOT updated

        $this->userModel->transStart();

        if (!empty($data['email'])) {
            $this->userModel->update($id, ['email' => $data['email']]);
        }

        $updateProfile = array_filter([
            'first_name'  => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name'   => $data['last_name'] ?? null,
            'suffix'      => $data['suffix'] ?? null,
            'department'  => $data['department_id'] ?? null,
            'program'     => $data['program_id'] ?? null,
            'year_level'  => $data['year_level_id'] ?? null,
            'section'     => $data['section_id'] ?? null,
        ]);

        if (!empty($updateProfile)) {
            $this->profileModel->update($id, $updateProfile);
        }

        $this->userModel->transComplete();

        if ($this->userModel->transStatus() === false) {
            return $this->fail(['message' => 'Failed to update student.']);
        }

        return $this->respond(['success' => true, 'message' => 'Student updated successfully.']);
    }

    public function getStudentsBySection($sectionId)
    {
        // Fetch students by section
        $students = $this->userModel
            ->select('user.id, user.email, user.user_type, 
                    profile.first_name, profile.middle_name, profile.last_name, profile.suffix, profile.is_regular,
                    department.id as department_id, department.department_name,
                    program.id as program_id, program.program_name,
                    year_level.id as year_level_id, year_level.year_level,
                    section.id as section_id, section.section_name')
            ->join('profile', 'profile.user_id = user.id')
            ->join('department', 'department.id = profile.department', 'left')
            ->join('program', 'program.id = profile.program', 'left')
            ->join('year_level', 'year_level.id = profile.year_level', 'left')
            ->join('section', 'section.id = profile.section', 'left')
            ->where('profile.section', $sectionId)
            ->where('user.user_type', 5) // Ensure only students are fetched
            ->where('user.user_type >', 0) // Only fetch active students
            ->findAll();

        if (!$students) {
            return $this->fail(['message' => 'No students found in this section.']);
        }

        return $this->respond(['success' => true, 'data' => $students]);
    }

}

