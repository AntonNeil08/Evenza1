<?php
namespace App\Models;

use CodeIgniter\Model;

class FestivalModel extends Model
{
    protected $table = 'festivals';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'festival_name',
        'festival_logo',
        'date',
        'country',
        'state',
        'city',
        'festival_description',
        'festival_highlights',
    ];

    public function insertFestival(array $data)
    {
        $data['festival_highlights'] = json_encode($data['festival_highlights'] ?? []);
        return $this->insert($data);
    }
    public function getActiveFestivals()
    {
        $today = date('Y-m-d');
    
        $sql = <<<EOT
SELECT *
FROM festivals
WHERE start_date <= :today:
    AND end_date >= :today:
    AND (is_deleted IS NULL OR is_deleted = 0)
EOT;
        return $this->query($sql, ['today' => $today])->getResultArray();
    }
}
