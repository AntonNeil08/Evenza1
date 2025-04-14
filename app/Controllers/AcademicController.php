<?php

namespace App\Controllers;

use App\Models\DepartmentModel;
use App\Models\ProgramModel;
use App\Models\YearLevelModel;
use App\Models\SectionModel;
use App\Models\UserModel;
use App\Models\SubjectModel;
use CodeIgniter\RESTful\ResourceController;

class AcademicController extends ResourceController
{
    protected $subjectModel;
    protected $userModel;
    protected $departmentModel;
    protected $programModel;
    protected $yearLevelModel;
    protected $sectionModel;

    public function __construct()
    {
        $this->subjectModel = new SubjectModel();
        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
        $this->programModel = new ProgramModel();
        $this->yearLevelModel = new YearLevelModel();
        $this->sectionModel = new SectionModel();
    }

    // ✅ Fetch all departments
    public function departmentsList()
    {
        return $this->respond(['success' => true, 'data' => $this->departmentModel->where('is_deleted', 0)->findAll()]);
    }

    // ✅ Create a department
    public function createDepartment()
    {
        $data = $this->request->getPost();
        if (empty($data['department_name'])) {
            return $this->fail(['message' => 'Department name is required.']);
        }

        $this->departmentModel->insert(['department_name' => $data['department_name']]);
        return $this->respond(['success' => true, 'message' => 'Department created successfully.']);
    }

    // ✅ Fetch all programs
    public function programsList()
    {
        return $this->respond(['success' => true, 'data' => $this->programModel->where('is_deleted', 0)->findAll()]);
    }

    // ✅ Create a program
    public function createProgram()
    {
        $data = $this->request->getPost();
        if (empty($data['program_name']) || empty($data['department_id'])) {
            return $this->fail(['message' => 'Program name and department ID are required.']);
        }

        $this->programModel->insert([
            'program_name' => $data['program_name'],
            'department_id' => $data['department_id']
        ]);

        return $this->respond(['success' => true, 'message' => 'Program created successfully.']);
    }

    // ✅ Fetch all year levels
    public function yearLevelsList()
    {
        return $this->respond(['success' => true, 'data' => $this->yearLevelModel->where('is_deleted', 0)->findAll()]);
    }

    // ✅ Create a year level
    public function createYearLevel()
    {
        $data = $this->request->getPost();
        if (empty($data['year_level']) || empty($data['program_id'])) {
            return $this->fail(['message' => 'Year level and program ID are required.']);
        }

        $this->yearLevelModel->insert([
            'year_level' => $data['year_level'],
            'program_id' => $data['program_id']
        ]);

        return $this->respond(['success' => true, 'message' => 'Year level created successfully.']);
    }

    // ✅ Fetch all sections
    public function sectionsList()
    {
        return $this->respond(['success' => true, 'data' => $this->sectionModel->where('is_deleted', 0)->findAll()]);
    }

    // ✅ Create a section
    public function createSection()
    {
        $data = $this->request->getPost();
        if (empty($data['section_name']) || empty($data['year_id'])) {
            return $this->fail(['message' => 'Section name and year ID are required.']);
        }

        $this->sectionModel->insert([
            'section_name' => $data['section_name'],
            'year_id' => $data['year_id']
        ]);

        return $this->respond(['success' => true, 'message' => 'Section created successfully.']);
    }

    // ✅ Soft-delete function for all entities
    public function deleteEntity($type, $id)
    {
        $model = $this->_getModelByType($type);

        if (!$model || !$model->find($id)) {
            return $this->fail(['message' => ucfirst($type) . ' not found.']);
        }

        // Prevent deleting department with active Deans or Faculty
        if ($type === 'department') {
            $activeUsers = $this->userModel
                ->join('profile', 'profile.user_id = user.id')
                ->where('profile.department', $id)
                ->whereIn('user.user_type', [2, 4, 6]) // Dean (2), Faculty (4), Privileged Faculty (6)
                ->where('user.user_type >', 0) // Only check active users
                ->countAllResults();

            if ($activeUsers > 0) {
                return $this->fail(['message' => 'Cannot delete department. Active Deans or Faculty exist.']);
            }
        }

        // Prevent deleting program with active Coordinators
        if ($type === 'program') {
            $activeCoordinators = $this->userModel
                ->join('profile', 'profile.user_id = user.id')
                ->where('profile.program', $id)
                ->where('user.user_type', 3) // Coordinator
                ->where('user.user_type >', 0) // Only check active users
                ->countAllResults();

            if ($activeCoordinators > 0) {
                return $this->fail(['message' => 'Cannot delete program. Active Coordinators exist.']);
            }
        }

        // Prevent deleting year level if it has a section with active students
        if ($type === 'year') {
            $activeStudents = $this->userModel
                ->join('profile', 'profile.user_id = user.id')
                ->join('section', 'section.id = profile.section')
                ->where('section.year_id', $id)
                ->where('user.user_type', 5) // Student
                ->where('user.user_type >', 0) // Only check active users
                ->countAllResults();

            if ($activeStudents > 0) {
                return $this->fail(['message' => 'Cannot delete year level. Active students exist in its sections.']);
            }
        }

        // Prevent deleting section if it has active students
        if ($type === 'section') {
            $activeStudents = $this->userModel
                ->join('profile', 'profile.user_id = user.id')
                ->where('profile.section', $id)
                ->where('user.user_type', 5) // Student
                ->where('user.user_type >', 0) // Only check active users
                ->countAllResults();

            if ($activeStudents > 0) {
                return $this->fail(['message' => 'Cannot delete section. Active students exist.']);
            }
        }

        // Soft-delete the entity
        $model->update($id, ['is_deleted' => 1]);

        return $this->respond(['success' => true, 'message' => ucfirst($type) . ' deleted successfully.']);
    }

    // ✅ Retrieve the correct model based on entity type
    protected function _getModelByType($type)
    {
        return match ($type) {
            'department' => $this->departmentModel,
            'program' => $this->programModel,
            'year' => $this->yearLevelModel,
            'section' => $this->sectionModel,
            default => null,
        };
    } 

    // ✅ Fetch all subjects
    public function subjectsList()
    {
        return $this->respond(['success' => true, 'data' => $this->subjectModel->where('is_deleted', 'N')->findAll()]);
    }

    // ✅ Create a new subject
    public function createSubject()
    {
        $data = $this->request->getPost();
        if (empty($data['code']) || empty($data['subject_name'])) {
            return $this->fail(['message' => 'Subject code and name are required.']);
        }

        // Ensure subject code is unique
        if ($this->subjectModel->find($data['code'])) {
            return $this->fail(['message' => 'Subject code already exists.']);
        }

        $this->subjectModel->insert([
            'code' => $data['code'],
            'subject_name' => $data['subject_name'],
            'is_deleted' => 'N'
        ]);

        return $this->respond(['success' => true, 'message' => 'Subject created successfully.']);
    }

    // ✅ Update a subject (allows updating the subject code)
    public function updateSubject($oldCode)
    {
        $data = $this->request->getRawInput();

        $subject = $this->subjectModel->find($oldCode);
        if (!$subject) {
            return $this->fail(['message' => 'Subject not found.']);
        }

        // Ensure new subject code is unique if changing it
        if (!empty($data['code']) && $data['code'] !== $oldCode && $this->subjectModel->find($data['code'])) {
            return $this->fail(['message' => 'New subject code already exists.']);
        }

        // Update the subject, including the code if provided
        $updateData = [];
        if (!empty($data['code'])) {
            $updateData['code'] = $data['code'];
        }
        if (!empty($data['subject_name'])) {
            $updateData['subject_name'] = $data['subject_name'];
        }

        // If subject code is updated, we must manually update it due to PK change
        if (!empty($data['code']) && $data['code'] !== $oldCode) {
            $this->subjectModel->delete($oldCode);
            $this->subjectModel->insert([
                'code' => $data['code'],
                'subject_name' => $data['subject_name'] ?? $subject['subject_name'],
                'is_deleted' => $subject['is_deleted']
            ]);
        } else {
            $this->subjectModel->update($oldCode, $updateData);
        }

        return $this->respond(['success' => true, 'message' => 'Subject updated successfully.']);
    }

    // ✅ Soft-delete a subject
    public function deleteSubject($code)
    {
        $subject = $this->subjectModel->find($code);
        if (!$subject) {
            return $this->fail(['message' => 'Subject not found.']);
        }

        $this->subjectModel->update($code, ['is_deleted' => 'Y']);

        return $this->respond(['success' => true, 'message' => 'Subject deleted successfully.']);
    }
}
