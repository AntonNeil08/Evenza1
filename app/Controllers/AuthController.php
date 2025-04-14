<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\SessionModel;
use App\Models\UserModel;
use App\Models\ProfileModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends ResourceController
{
    protected $profileModel;
    protected $authModel;
    protected $userModel;
    protected $sessionModel;
    protected $jwtSecret = "ICE_CANDY_FOR_SALE_HERE_10_PESOS_EACH_LAMI_NA_BARATO_PA"; // Replace with your actual secret

    public function __construct()
    {
        helper('email');
        $this->profileModel = new ProfileModel();
        $this->authModel = new AuthModel();
        $this->userModel = new UserModel();
        $this->sessionModel = new SessionModel();
    }

    /**
     * Handle user login
     */
    public function login()
    {
        $userId = $this->request->getPost('user_id');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('id', $userId)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->fail(['message' => 'Invalid credentials']);
        }

        $token = $this->_generate_jwt($user['id'], $user['user_type']);

        $this->sessionModel->save([
            'user_id' => $user['id'],
            'jwt_token' => $token,
            'usertype' => $user['user_type']
        ]);

        return $this->respond(['success' => true, 'data' => ['token' => $token]]);
    }

    /**
     * Generate and store OTP
     */
    public function generateOTP()
    {
        $userId = $this->request->getPost('user_id');
        if (!$userId) {
            return $this->fail(['message' => 'User ID is required.']);
        }

        // Fetch user and profile details
        $user = $this->userModel->where('id', $userId)->first();
        $profile = $this->profileModel->where('user_id', $userId)->first();

        if (!$user || !$profile) {
            return $this->fail(['message' => 'User not found.']);
        }

        // Generate OTP and set expiry
        $otp = random_int(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $this->authModel->transStart();

        // Check if user already has an OTP
        $existingOtp = $this->authModel->where('user_id', $userId)->first();

        if ($existingOtp) {
            // Update existing OTP record
            $this->authModel->set([
                'otp' => $otp,
                'otp_expiry' => $expiry
            ])->where('user_id', $userId)->update();
        } else {
            // Insert new OTP record
            $this->authModel->insert([
                'user_id' => $userId,
                'otp' => $otp,
                'otp_expiry' => $expiry
            ]);
        }

        $this->authModel->transComplete();

        if ($this->authModel->transStatus() === false) {
            return $this->fail(['message' => 'Failed to generate OTP.']);
        }

        // Construct full name
        $fullName = trim("{$profile['first_name']}" . 
            (!empty($profile['middle_name']) ? " {$profile['middle_name']}" : '') . 
            " {$profile['last_name']}" . 
            (!empty($profile['suffix']) ? " {$profile['suffix']}" : '')
        );

        // Send OTP email
        if (!sendOTPEmail($user['email'], $fullName, $otp)) {
            return $this->fail(['message' => 'OTP generated, but failed to send OTP email.']);
        }

        return $this->respond(['success' => true, 'message' => 'OTP sent successfully.']);
    }
    /**
     * Resend OTP if still valid, otherwise generate a new one
     */
    public function resendOTP()
    {
        $userId = $this->request->getPost('user_id');
        if (!$userId) {
            return $this->fail(['message' => 'User ID is required.']);
        }

        // Fetch user and profile details
        $user = $this->userModel->where('id', $userId)->first();
        $profile = $this->profileModel->where('user_id', $userId)->first();

        if (!$user || !$profile) {
            return $this->fail(['message' => 'User not found.']);
        }

        $auth = $this->authModel->where('user_id', $userId)->first();

        if ($auth && strtotime($auth['otp_expiry']) > time()) {
            // Construct full name
            $fullName = trim("{$profile['first_name']}" . 
                (!empty($profile['middle_name']) ? " {$profile['middle_name']}" : '') . 
                " {$profile['last_name']}" . 
                (!empty($profile['suffix']) ? " {$profile['suffix']}" : '')
            );

            // Resend the same OTP
            if (!sendOTPEmail($user['email'], $fullName, $auth['otp'])) {
                return $this->fail(['message' => 'Failed to send OTP email.']);
            }

            return $this->respond(['success' => true, 'message' => 'OTP sent successfully.']);
        }

        return $this->generateOTP();
    }

    /**
     * Verify OTP
     */
    public function verifyOTP()
    {
        $userId = $this->request->getPost('user_id');
        $otp = $this->request->getPost('otp');

        $auth = $this->authModel->where('user_id', $userId)->first();

        if (!$auth || $auth['otp'] != $otp || strtotime($auth['otp_expiry']) < time()) {
            return $this->fail(['message' => 'Invalid or expired OTP']);
        }

        $this->authModel->save(['user_id' => $userId, 'otp' => null, 'otp_expiry' => null]);

        return $this->respond(['success' => true, 'data' => []]);
    }

    /**
     * Validate JWT token
     */
    public function validateJWT()
    {
        $userId = $this->request->getPost('user_id');
        $userType = $this->request->getPost('user_type');
        $token = $this->request->getCookie('auth_token'); // Retrieve JWT from the HTTP-only cookie

        if (!$token) {
            return $this->fail(['message' => 'Token is required']);
        }

        // Step 1: Validate if the token belongs to the user and user type in the session
        $session = $this->sessionModel
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->first();

        if (!$session || $session['jwt_token'] !== $token) {
            return $this->fail(['message' => 'Invalid session']);
        }

        try {
            // Step 2: Decode the JWT and verify the contents
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            if ($decoded->user_id !== $userId || $decoded->user_type !== $userType) {
                return $this->fail(['message' => 'Token does not match user']);
            }

            return $this->respond(['success' => true, 'message' => 'Token is valid.']);
        } catch (\Exception $e) {
            return $this->fail(['message' => 'Unauthorized']);
        }
    }
    /**
     * Generate JWT Token
     */
    protected function _generate_jwt($userId, $userType)
    {
        $expirationTime = time() + (9 * 3600); // 9 hours

        $payload = [
            'user_id'   => $userId,
            'user_type' => $userType,
            'exp'       => $expirationTime // JWT expires in 9 hours
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

        // Set JWT as an HTTP-Only Secure Cookie
        $response = service('response');
        $response->setCookie(
            'auth_token',  // Cookie name
            $jwt,          // JWT value
            $expirationTime, // Expiration timestamp (9 hours)
            [
                'path'     => '/',
                'domain'   => '', // Set your domain if needed
                'secure'   => isset($_SERVER['HTTPS']), // Secure if HTTPS is enabled
                'httponly' => true, // Prevents JavaScript access
                'samesite' => 'Strict' // Prevents CSRF
            ]
        );

        return $jwt; // Optional: Return JWT for debugging (frontend will use cookies automatically)
    }

}
