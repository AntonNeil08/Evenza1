<?php
namespace App\Models;

use CodeIgniter\Model;

class OrganizerModel extends Model
{
    protected $table = 'organizers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['festival_id', 'event_id', 'user_name'];

    public function addFestivalOrganizers(int $festivalId, array $userNames)
    {
        $data = array_map(fn($name) => [
            'festival_id' => $festivalId,
            'user_name' => $name,
        ], $userNames);

        return $this->insertBatch($data);
    }

    public function addEventOrganizers(int $eventId, array $userNames)
    {
        $data = array_map(fn($name) => [
            'event_id' => $eventId,
            'user_name' => $name,
        ], $userNames);

        return $this->insertBatch($data);
    }
}
