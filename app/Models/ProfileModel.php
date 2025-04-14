<?php
namespace App\Models;

use CodeIgniter\Model;
class ProfileModel extends Model
{
    protected $table = 'profile';
    protected $primaryKey = 'user_id';
    protected $allowedFields = [
        'user_id', 'first_name', 'middle_name', 'last_name', 'suffix', 
        'department', 'program', 'year_level', 'section', 'is_regular'
    ];
}