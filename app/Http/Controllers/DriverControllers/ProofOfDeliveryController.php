<?php

namespace App\Http\Controllers\DriverControllers;

use App\Http\Controllers\Controller;
use App\Services\DriverPackageService;
use App\Factories\Driver\ProofOfDeliveryViewFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProofOfDeliveryController extends Controller
{
    protected DriverPackageService $packageService;

    public function __construct(DriverPackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Show the form for creating a proof of delivery.
     */
    public function create(string $packageId)
    {
        try {
            $package = $this->packageService->getPackageDetails($packageId);
            return view('DriverViews.proof-of-delivery', compact('package'));
        } catch (\Exception $e) {
            return redirect()->route('driver.status.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Store the new proof of delivery with FILE UPLOAD SECURITY.
     */
    public function store(Request $request, string $packageId)
    {
        Log::info("Proof submission started", [
            'package_id' => $packageId,
            'driver_id' => Auth::id(),
            'ip' => $request->ip()
        ]);

        try {
            // SECURITY: Validate file uploads if photo proof is submitted
            $photoPath = null;
            if ($request->hasFile('proof_photo') && $request->input('proof_type') === 'PHOTO') {
                $photoPath = $this->handleSecureFileUpload($request->file('proof_photo'));
            }

            // Basic data collection with file path
            $data = [
                'proof_type' => $request->input('proof_type', 'SIGNATURE'),
                'recipient_signature_name' => $request->input('recipient_signature_name', 'N/A'),
                'notes' => $request->input('notes', null),
                'status' => 'DELIVERED',
                'proof_photo_path' => $photoPath // Add photo path if uploaded
            ];

            Log::info("Data to be processed: ", array_merge($data, ['proof_photo_path' => $photoPath ? 'FILE_UPLOADED' : 'NO_FILE']));

            // Use the service to handle the database transaction
            $this->packageService->updateStatusWithProof($packageId, $data);
            
            Log::info("Service method completed successfully");
            
            return redirect()->route('driver.status.index')
                ->with('success', "Delivery completed successfully!");
                
        } catch (\Exception $e) {
            Log::error("ERROR in store method: " . $e->getMessage());
            
            // Clean up uploaded file if there was an error
            if (isset($photoPath) && $photoPath && Storage::exists($photoPath)) {
                Storage::delete($photoPath);
            }
            
            return redirect()->route('driver.status.index')
                ->with('error', "Error: " . $e->getMessage());
        }
    }

    /**
     * SECURITY METHOD: Handle secure file upload for photo proof
     */
    private function handleSecureFileUpload($file): string
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        $maxSize = 2 * 1024 * 1024; 
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds 2MB limit');
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        $fileMime = $file->getMimeType();
        $fileExtension = strtolower($file->getClientOriginalExtension());

        if (!in_array($fileMime, $allowedMimes) || !in_array($fileExtension, $allowedExtensions)) {
            Log::warning('Suspicious file upload attempt', [
                'driver_id' => Auth::id(),
                'mime_type' => $fileMime,
                'extension' => $fileExtension,
                'original_name' => $file->getClientOriginalName(),
                'ip' => request()->ip()
            ]);
            throw new \Exception('Only JPEG and PNG images are allowed');
        }

        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \Exception('File is not a valid image');
        }

        $fileContent = file_get_contents($file->getPathname());
        $maliciousPatterns = [
            '<?php', '<?=', '<script', 'javascript:', 'eval(', 'exec(', 'system('
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (stripos($fileContent, $pattern) !== false) {
                Log::alert('Malicious content detected in uploaded file', [
                    'driver_id' => Auth::id(),
                    'pattern_found' => $pattern,
                    'file_name' => $file->getClientOriginalName(),
                    'ip' => request()->ip()
                ]);
                throw new \Exception('File contains suspicious content');
            }
        }

        $secureFileName = $this->generateSecureFileName($fileExtension);
        
        $storagePath = 'proof-photos/' . date('Y/m/d');
        $fullPath = $file->storeAs($storagePath, $secureFileName, 'local');

        if (!Storage::exists($fullPath)) {
            throw new \Exception('Failed to store uploaded file');
        }

        Log::info('Secure file upload completed', [
            'driver_id' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $secureFileName,
            'file_size' => $file->getSize(),
            'mime_type' => $fileMime,
            'storage_path' => $fullPath
        ]);

        return $fullPath;
    }

    
    private function generateSecureFileName(string $extension): string
    {
        // Use driver ID + timestamp + random string for unique filename
        $driverId = Auth::id();
        $timestamp = time();
        $randomString = Str::random(10);
        
        return "{$driverId}_{$timestamp}_{$randomString}.{$extension}";
    }

    
    public function getProofPhoto(string $packageId)
    {
        try {
            $driverId = Auth::id();
            $hasAccess = DB::table('delivery')
                ->where('package_id', $packageId)
                ->where('driver_id', $driverId)
                ->exists();

            if (!$hasAccess) {
                Log::warning('Unauthorized photo access attempt', [
                    'package_id' => $packageId,
                    'driver_id' => $driverId,
                    'ip' => request()->ip()
                ]);
                abort(403, 'Unauthorized');
            }

            // Get proof record
            $proof = DB::table('proofofdelivery')
                ->join('delivery', 'proofofdelivery.delivery_id', '=', 'delivery.delivery_id')
                ->where('delivery.package_id', $packageId)
                ->select('proofofdelivery.proof_photo_path')
                ->first();

            if (!$proof || !$proof->proof_photo_path) {
                abort(404, 'Photo not found');
            }

            // SECURITY: Verify file still exists and serve it securely
            if (!Storage::exists($proof->proof_photo_path)) {
                abort(404, 'Photo file not found');
            }

            // Log photo access
            Log::info('Proof photo accessed', [
                'package_id' => $packageId,
                'driver_id' => $driverId,
                'ip' => request()->ip()
            ]);

            // Serve file with proper headers
            return Storage::response($proof->proof_photo_path, null, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('Error serving proof photo', [
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Error loading photo');
        }
    }
}