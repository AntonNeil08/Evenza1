<?php
namespace App\Models;

use CodeIgniter\Model;
class DepartmentModel extends Model
{
    protected $table = 'department';
    protected $primaryKey = 'id';
    protected $allowedFields = ['department_name', 'is_deleted'];
}
?>