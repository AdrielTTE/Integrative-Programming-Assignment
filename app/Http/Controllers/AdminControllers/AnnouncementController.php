<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminServices\AnnouncementService;

class AnnouncementController extends Controller
{
    protected $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    public function create()
    {
        return view('AdminViews.createAnnouncement'); // blade file for announcement input
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $this->announcementService->broadcast($request->input('message'));

        return redirect()->back()->with('success', 'Announcement sent to all customers.');
    }
}
