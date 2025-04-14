<?php
namespace App\Models;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $table = 'auth';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_id', 'otp', 'otp_expiry', 'force_password_change'];
}
