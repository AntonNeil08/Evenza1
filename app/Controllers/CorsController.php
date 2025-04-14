<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Response;
use CodeIgniter\Log\Logger;

class CorsController extends BaseController
{
    public function handleOptions()
    {
        log_message('info', 'CORS Preflight (OPTIONS) Request Received');

        return $this->response
            ->setStatusCode(204) // No content response
            ->setHeader('Access-Control-Allow-Origin', '*') // Debugging: Allow all
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, api-key, latitude, longitude')
            ->setHeader('Access-Control-Allow-Credentials', 'true');
    }
}
