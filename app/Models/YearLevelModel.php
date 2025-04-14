<?php
namespace App\Models;

use CodeIgniter\Model;
class YearLevelModel extends Model
{
    protected $table = 'year_level';
    protected $primaryKey = 'id';
    protected $allowedFields = ['program_id', 'year_level', 'is_deleted'];
}