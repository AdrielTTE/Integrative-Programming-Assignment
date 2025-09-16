<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Factories\StandardAssignmentViewFactory; // Import the factory
use Illuminate\Http\Request;

class PackageAssignmentController extends Controller
{
    public function __construct()
    {
        // The service is no longer needed here, as the factory handles data fetching.
    }

    public function index(Request $request)
    {
        // 1. Instantiate the appropriate concrete factory.
        //    In a more complex app, you might have logic here to decide
        //    which factory to use (e.g., Standard vs. Express).
        $factory = new StandardAssignmentViewFactory();
        
        // 2. Use the factory to create and render the view.
        //    The controller doesn't know the specifics of how the view is created.
        return $factory->renderView();
    }
}