<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\OrganizerModel;
use CodeIgniter\RESTful\ResourceController;

class EventController extends ResourceController
{
    protected $eventModel;
    protected $organizerModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->organizerModel = new OrganizerModel();
    }

    public function create()
    {
        $request = $this->request;

        $eventName = $request->getPost('event_name');
        $year = date('Y', strtotime($request->getPost('start_date')));
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $eventName));

        // Upload event logo
        $logo = $request->getFile('event_logo');
        $logoPath = '';
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $ext = $logo->getClientExtension();
            $newLogoName = "{$safeName}_logo_{$year}.{$ext}";
            $logo->move(ROOTPATH . 'public/uploads/logos', $newLogoName);
            $logoPath = "uploads/logos/{$newLogoName}";
        }

        // Upload event highlights
        $highlightPaths = [];
        $highlightFiles = $request->getFiles()['event_highlights'] ?? [];
        foreach ($highlightFiles as $index => $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $ext = $file->getClientExtension();
                $newName = "{$safeName}_highlight_{$year}_{$index}.{$ext}";
                $file->move(ROOTPATH . 'public/uploads/highlights', $newName);
                $highlightPaths[] = "uploads/highlights/{$newName}";
            }
        }

        // Prepare event data
        $eventData = [
            'festival_id'             => $request->getPost('festival_id'),
            'event_name'              => $eventName,
            'event_logo'              => $logoPath,
            'start_date'              => $request->getPost('start_date'),
            'end_date'                => $request->getPost('end_date'),
            'country'                 => $request->getPost('country'),
            'state'                   => $request->getPost('state'),
            'city'                    => $request->getPost('city'),
            'latitude'                => $request->getPost('latitude'),
            'longitude'               => $request->getPost('longitude'),
            'event_description'       => $request->getPost('event_description'),
            'event_participant_limit' => $request->getPost('event_participant_limit'),
            'event_highlights'        => $highlightPaths,
        ];

        $eventId = $this->eventModel->createEvent($eventData);
        if (!$eventId) {
            return $this->failServerError('Failed to create event.');
        }

        // Insert organizers
        $organizers = $request->getPost('organizers');
        if (!is_array($organizers)) $organizers = [];

        $this->organizerModel->addEventOrganizers($eventId, $organizers);

        return $this->respondCreated([
            'success'   => true,
            'message'   => 'Event and organizers created successfully',
            'event_id'  => $eventId
        ]);
    }
}
