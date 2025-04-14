<?php
namespace App\Models;

use CodeIgniter\Model;
class SessionModel extends Model
{
    protected $table = 'session';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_id', 'jwt_token', 'usertype'];
}