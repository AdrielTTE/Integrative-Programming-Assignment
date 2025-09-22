<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminServices\AnnouncementService;
use App\Models\Customer;

class AnnouncementController extends Controller
{
    protected $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    public function create()
{
    $customers = Customer::all(); // or filter active customers only
    return view('AdminViews.createAnnouncement', compact('customers'));
}


    public function send(Request $request)
{
    $request->validate([
        'message' => 'required|string|max:1000',
        'customer_ids' => 'nullable|array',              // allow multiple customers
        'customer_ids.*' => 'exists:customer,customer_id', // ensure each ID exists
    ]);

    $message = $request->input('message');
    $customerIds = $request->input('customer_ids', []); // default empty array

    $this->announcementService->broadcast($message, $customerIds);

    return redirect()->back()->with(
        'success',
        empty($customerIds)
            ? 'Announcement sent to all customers.'
            : 'Announcement sent to selected customers.'
    );
}

}
