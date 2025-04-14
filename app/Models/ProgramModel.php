<?php
namespace App\Models;

use CodeIgniter\Model;
class ProgramModel extends Model
{
    protected $table = 'program';
    protected $primaryKey = 'id';
    protected $allowedFields = ['department_id', 'program_name', 'is_deleted'];
}