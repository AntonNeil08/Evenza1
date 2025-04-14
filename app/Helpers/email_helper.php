<?php

use Config\Email;
use CodeIgniter\Email\Email as CIEmail;

/**
 * Sends an OTP email to the user.
 *
 * @param string $toEmail Recipient email address
 * @param string $fullName Full name of the recipient
 * @param string $otp One-Time Password
 * @return bool Returns true if email is sent successfully, false otherwise
 */
function sendOTPEmail($toEmail, $fullName, $otp)
{
    $emailConfig = new Email();
    $email = new CIEmail();

    $email->initialize([
        'protocol'   => 'smtp',
        'SMTPHost'   => $emailConfig->SMTPHost,
        'SMTPUser'   => $emailConfig->SMTPUser,
        'SMTPPass'   => $emailConfig->SMTPPass,
        'SMTPPort'   => $emailConfig->SMTPPort,
        'mailType'   => 'html',
    ]);

    $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
    $email->setTo($toEmail);
    $email->setSubject('Your OTP Code');
    $email->setMessage(view('emails/otp', ['full_name' => $fullName, 'otp' => $otp]));

    return $email->send();
}
function sendPasswordResetEmail($toEmail, $userId, $tempPassword)
    {
        $emailConfig = new Email();
        $email = new CIEmail();

        $email->initialize([
            'protocol'   => 'smtp',
            'SMTPHost'   => $emailConfig->SMTPHost,
            'SMTPUser'   => $emailConfig->SMTPUser,
            'SMTPPass'   => $emailConfig->SMTPPass,
            'SMTPPort'   => $emailConfig->SMTPPort,
            'mailType'   => 'html',
        ]);

        $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
        $email->setTo($toEmail);
        $email->setSubject('Password Reset Notification');

        $message = view('emails/password_reset', [
            'user_id' => $userId,
            'temp_password' => $tempPassword
        ]);

        $email->setMessage($message);

        return $email->send();
    }
    function sendUserRegistrationEmail($toEmail, $fullName, $userType, $userId, $password)
    {
        $emailConfig = new Email();
        $email = new CIEmail();

        $email->initialize([
            'protocol'   => 'smtp',
            'SMTPHost'   => $emailConfig->SMTPHost,
            'SMTPUser'   => $emailConfig->SMTPUser,
            'SMTPPass'   => $emailConfig->SMTPPass,
            'SMTPPort'   => $emailConfig->SMTPPort,
            'mailType'   => 'html',
        ]);

        $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
        $email->setTo($toEmail);
        $email->setSubject('Welcome to the Application');

        $roleName = match($userType) {
            1 => 'Administrator',
            2 => 'Dean',
            3 => 'Coordinator',
            4 => 'Faculty Member',
            5 => 'Student',
            6 => 'Privileged Faculty',
            default => 'User'
        };

        $message = view('emails/user_registration', [
            'full_name' => $fullName,
            'role'      => $roleName,
            'user_id'   => $userId,
            'password'  => $password
        ]);

        $email->setMessage($message);

        return $email->send();
    }

    function sendPrivilegedFacultyToggleEmail($toEmail, $userId, $newStatus)
    {
        $emailConfig = new Email();
        $email = new CIEmail();

        $email->initialize([
            'protocol'   => 'smtp',
            'SMTPHost'   => $emailConfig->SMTPHost,
            'SMTPUser'   => $emailConfig->SMTPUser,
            'SMTPPass'   => $emailConfig->SMTPPass,
            'SMTPPort'   => $emailConfig->SMTPPort,
            'mailType'   => 'html',
        ]);

        $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
        $email->setTo($toEmail);

        $statusText = ($newStatus === 6) ? 'Privileged Faculty' : 'Regular Faculty';
        $email->setSubject("Your Role Has Been Updated");

        $message = view('emails/privileged_faculty_toggle', [
            'user_id' => $userId,
            'status'  => $statusText,
        ]);

        $email->setMessage($message);

        return $email->send();
    }
