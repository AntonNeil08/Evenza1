<?php
namespace App\Models;

use CodeIgniter\Model;
class SubjectModel extends Model
{
    protected $table = 'subject';
    protected $primaryKey = 'code';
    protected $allowedFields = ['subject_name', 'is_deleted'];
}