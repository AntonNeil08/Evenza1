<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'festival_id',
        'event_name',
        'event_logo',
        'start_date',
        'end_date',
        'country',
        'state',
        'city',
        'latitude',
        'longitude',
        'event_description',
        'event_participant_limit',
        'event_highlights',
    ];

    public function createEvent(array $data)
    {
        if (isset($data['event_highlights']) && is_array($data['event_highlights'])) {
            $data['event_highlights'] = json_encode($data['event_highlights']);
        }

        return $this->insert($data, true);
    }

}
