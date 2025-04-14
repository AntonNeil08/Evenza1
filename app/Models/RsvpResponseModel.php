<?php
namespace App\Models;

use CodeIgniter\Model;

class RsvpResponseModel extends Model
{
    protected $table = 'rsvp_response';
    protected $allowedFields = 
    [
        'event_id', 
        'user_name', 
        'name',
        'email',
        'participants', 
        'comments'
    ];
}