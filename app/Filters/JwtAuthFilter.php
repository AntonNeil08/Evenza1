<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\SessionModel;

class JwtAuthFilter implements FilterInterface
{
    protected $jwtSecret = 'ICE_CANDY_FOR_SALE_HERE_10_PESOS_EACH_LAMI_NA_BARATO_PA'; // Set your JWT secret key

    public function before(RequestInterface $request, $arguments = null)
    {
        // Ensure $request is an instance of IncomingRequest to use getCookie()
        $token = ($request instanceof IncomingRequest) ? $request->getCookie('auth_token') : null;

        if (!$token) {
            return service('response')->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        try {
            // Decode JWT
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            // Fetch session to verify if the token belongs to an active session
            $sessionModel = new SessionModel();
            $session = $sessionModel
                ->where('user_id', $decoded->user_id)
                ->where('user_type', $decoded->user_type)
                ->where('jwt_token', $token)
                ->first();

            if (!$session) {
                return service('response')->setJSON(['success' => false, 'message' => 'Invalid session'])->setStatusCode(401);
            }

            // Enforce Role-Based Access Control
            if (!empty($arguments)) {
                $allowedRoles = array_map('intval', $arguments); // Convert to integers

                if (!in_array($decoded->user_type, $allowedRoles)) {
                    return service('response')->setJSON(['success' => false, 'message' => 'Forbidden'])->setStatusCode(403);
                }
            }

        } catch (\Exception $e) {
            return service('response')->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No modifications needed after request
    }
}
