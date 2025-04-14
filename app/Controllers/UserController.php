<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuthModel;
use App\Models\ProfileModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Email;

class UserController extends ResourceController
{
    protected $userModel;
    protected $profileModel;
    protected $authModel;

    public function __construct()
    {
        helper('email');
        $this->userModel = new UserModel();
        $this->authModel = new AuthModel();
    }

    /**
     * Change User Password (Works for all user types)
     */
    public function changePassword()
    {
        $data = $this->request->getPost();

        if (empty($data['id']) || empty($data['current_password']) || empty($data['new_password'])) {
            return $this->fail(['message' => 'User ID, current password, and new password are required.']);
        }

        $user = $this->userModel->where('id', $data['id'])->first();

        if (!$user) {
            return $this->fail(['message' => 'User not found.']);
        }

        // Verify current password
        if (!password_verify($data['current_password'], $user['password'])) {
            return $this->fail(['message' => 'Current password is incorrect.']);
        }

        // Hash the new password
        $hashedPassword = password_hash($data['new_password'], PASSWORD_BCRYPT);

        // Update the password
        $this->userModel->update($data['id'], ['password' => $hashedPassword]);

        $this->authModel->update($data['id'], ['force_password_change' => 'N']);

        return $this->respond(['success' => true, 'message' => 'Password changed successfully.']);
    }

    /**
     * Reset User Password (Works for all user types and reactivates if needed)
     */
    public function resetPassword()
    {
        $data = $this->request->getPost();

        if (empty($data['id'])) {
            return $this->fail(['message' => 'User ID is required.']);
        }

        $user = $this->userModel->where('id', $data['id'])->first();

        if (!$user) {
            return $this->fail(['message' => 'User not found.']);
        }

        $newPassword = $user['id']; // Reset password to be the same as the ID
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->userModel->transStart();

        // Update password and reactivate if deactivated
        $this->userModel->update($data['id'], [
            'password' => $hashedPassword,
            'user_type' => ($user['user_type'] < 0) ? abs($user['user_type']) : $user['user_type'] // Reactivate if deactivated
        ]);

        // Set force password change
        $this->authModel->update($data['id'], ['force_password_change' => 'Y']);

        $this->userModel->transComplete();

        if ($this->userModel->transStatus() === false) {
            return $this->fail(['message' => 'Failed to reset password.']);
        }

        // Send email notification
        if (!sendPasswordResetEmail($user['email'], $user['id'], $newPassword)) {
            return $this->fail(['message' => 'Password reset successful, but failed to send email.']);
        }

        return $this->respond(['success' => true, 'message' => 'Password reset successfully. User reactivated if deactivated. Email notification sent.']);
    }

    public function createUser()
    {
        $data = $this->request->getPost();
    
        if (empty($data['id']) || empty($data['password']) || empty($data['email']) || 
            empty($data['first_name']) || empty($data['last_name']) || empty($data['user_type'])) {
            return $this->fail(['message' => 'ID, password, email, first name, last name, and user type are required.']);
        }
    
        // Validate user type (Now includes 6 for Privileged Faculty)
        if (!in_array($data['user_type'], [1, 2, 3, 4, 5, 6])) {
            return $this->fail(['message' => 'Invalid user type.']);
        }
    
        // Check if the user already exists
        if ($this->userModel->find($data['id'])) {
            return $this->fail(['message' => 'User ID already exists.']);
        }
    
        // Validate required fields based on user type
        if (in_array($data['user_type'], [2, 3, 4, 5, 6]) && empty($data['department_id'])) {
            return $this->fail(['message' => 'Department is required for this user type.']);
        }
        if (in_array($data['user_type'], [3, 5]) && empty($data['program_id'])) {
            return $this->fail(['message' => 'Program is required for this user type.']);
        }
        if ($data['user_type'] === 5 && (empty($data['year_level_id']) || empty($data['section_id']) || !isset($data['is_regular']))) {
            return $this->fail(['message' => 'Year level, section, and is_regular are required for students.']);
        }
    
        $this->userModel->transStart();
    
        // Insert into `user` table
        $this->userModel->insert([
            'id'        => $data['id'],
            'email'     => $data['email'],
            'password'  => password_hash($data['password'], PASSWORD_BCRYPT),
            'user_type' => $data['user_type'],
        ]);
    
        // Insert into `profile` table
        $profileData = [
            'user_id'     => $data['id'],
            'first_name'  => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name'   => $data['last_name'],
            'suffix'      => $data['suffix'] ?? null,
            'department'  => $data['department_id'] ?? null,
            'program'     => $data['program_id'] ?? null,
            'year_level'  => $data['year_level_id'] ?? null,
            'section'     => $data['section_id'] ?? null,
            'is_regular'  => $data['is_regular'] ?? null,
        ];
        
        $this->profileModel->insert(array_filter($profileData)); // Remove null values
    
        $this->userModel->transComplete();
    
        if ($this->userModel->transStatus() === false) {
            return $this->fail(['message' => 'Failed to create user.']);
        }
    
        // Send registration email with login credentials
        if (!sendUserRegistrationEmail($data['email'], $data['first_name'], $data['user_type'], $data['id'], $data['password'])) {
            return $this->fail(['message' => 'User created, but failed to send registration email.']);
        }
    
        return $this->respond(['success' => true, 'message' => 'User created successfully. Email notification sent.']);
    }

    public function toggleUserStatus($id)
    {
        $user = $this->userModel->find($id);

        if (!$user || !in_array($user['user_type'], [1, -1, 2, -2, 3, -3, 4, -4, 5, -5, 6, -6])) {
            return $this->fail(['message' => 'User not found.']);
        }

        $newStatus = $user['user_type'] * -1;

        $this->userModel->update($id, ['user_type' => $newStatus]);

        return $this->respond([
            'success' => true,
            'message' => ($newStatus < 0) ? 'User deactivated.' : 'User reactivated.'
        ]);
    }

    public function togglePrivilegedFaculty($id)
{
    $user = $this->userModel->find($id);

    if (!$user || !in_array($user['user_type'], [4, 6])) {
        return $this->fail(['message' => 'User is not a Faculty or Privileged Faculty.']);
    }

    // Toggle user type (4 ↔ 6)
    $newStatus = ($user['user_type'] === 4) ? 6 : 4;

    $this->userModel->update($id, ['user_type' => $newStatus]);

    // Send Email Notification
    if (!sendPrivilegedFacultyToggleEmail($user['email'], $user['id'], $newStatus)) {
        return $this->fail(['message' => 'User role changed, but failed to send email.']);
    }

    return $this->respond([
        'success' => true,
        'message' => ($newStatus === 6) ? 'User is now a Privileged Faculty.' : 'User is now a Regular Faculty.'
    ]);
}

    
}
