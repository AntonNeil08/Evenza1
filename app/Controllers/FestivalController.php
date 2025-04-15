<?php

namespace App\Controllers;
use App\Models\FestivalModel;
use App\Models\OrganizerModel;
use CodeIgniter\RESTful\ResourceController;

class FestivalController extends ResourceController
{
    protected $festivalModel;
    protected $organizerModel;

    public function __construct()
    {
        $this->festivalModel = new FestivalModel();
        $this->organizerModel = new OrganizerModel();
    }

    public function create()
    {
        $request = $this->request;

        // Extract + sanitize
        $festivalName = $request->getPost('festival_name');
        $date = $request->getPost('date');
        $year = date('Y', strtotime($date));
        
        // Fix: allow uppercase letters before converting to lowercase
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $festivalName));
        
        // Upload logo
        $logo = $request->getFile('festival_logo');
        $logoPath = '';
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $ext = $logo->getClientExtension();
            $newLogoName = "{$safeName}_logo_{$year}.{$ext}";
            $logo->move(ROOTPATH . 'public/uploads/logos', $newLogoName);
            $logoPath = "uploads/logos/{$newLogoName}";
        }
        
        // Upload highlights
        $highlightPaths = [];
        $highlightFiles = $request->getFiles()['festival_highlights'] ?? [];
        foreach ($highlightFiles as $index => $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $ext = $file->getClientExtension();
                $newName = "{$safeName}_highlight_{$year}_{$index}.{$ext}";
                $file->move(ROOTPATH . 'public/uploads/highlights', $newName);
                $highlightPaths[] = "uploads/highlights/{$newName}";
            }
        }

        // Insert festival
        $festivalData = [
            'festival_name'        => $festivalName,
            'festival_logo'        => $logoPath,
            'date'                 => $date,
            'country'              => $request->getPost('country'),
            'state'                => $request->getPost('state'),
            'city'                 => $request->getPost('city'),
            'festival_description' => $request->getPost('festival_description'),
            'festival_highlights'  => $highlightPaths,
        ];

        $festivalId = $this->festivalModel->insertFestival($festivalData);
        if (!$festivalId) {
            return $this->failServerError('Failed to create festival');
        }

        // Insert organizers
        $organizers = $request->getPost('organizers'); 
        if (!is_array($organizers)) $organizers = [];
        
        $this->organizerModel->addOrganizers($festivalId, $organizers);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Festival and organizers created successfully',
            'festival_id' => $festivalId
        ]);
    }
    public function activeFestivals()
    {
        $festivals = $this->festivalModel->getActiveFestivals();
    
        return $this->respond([
            'success' => true,
            'data' => $festivals
        ]);
    }
}
