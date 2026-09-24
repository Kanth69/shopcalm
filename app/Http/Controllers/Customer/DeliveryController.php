<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    protected DeliveryService $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * AJAX endpoint to check serviceability of a PIN code
     */
    public function checkPincode(Request $request): JsonResponse
    {
        $pincode = $request->input('pincode');
        $saveLocation = $request->boolean('save_location', false);

        $result = $this->deliveryService->checkServiceability($pincode);

        if ($result['is_serviceable'] && $saveLocation) {
            $this->deliveryService->setSessionLocation($result);
        }

        return response()->json($result);
    }

    /**
     * Save selected location to session from header modal or address pick
     */
    public function saveLocation(Request $request): JsonResponse
    {
        $pincode = $request->input('pincode');
        $result = $this->deliveryService->checkServiceability($pincode);

        if ($result['is_serviceable']) {
            $this->deliveryService->setSessionLocation($result);
            return response()->json([
                'success' => true,
                'location' => $result,
                'message' => "Delivering to {$result['location_text']}",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? "Pincode {$pincode} is not serviceable.",
        ], 422);
    }

    /**
     * Get saved customer addresses with serviceability checks for location modal
     */
    public function getSavedAddresses(): JsonResponse
    {
        if (!auth('customer')->check()) {
            return response()->json(['addresses' => []]);
        }

        $addresses = auth('customer')->user()->addresses()->latest()->get()->map(function ($addr) {
            $check = $this->deliveryService->checkServiceability($addr->zip);
            return [
                'id' => $addr->id,
                'name' => $addr->name,
                'address' => $addr->address,
                'city' => $addr->city,
                'state' => $addr->state,
                'zip' => $addr->zip,
                'is_serviceable' => $check['is_serviceable'] ?? false,
                'estimated_delivery' => $check['estimated_delivery'] ?? null,
            ];
        });

        return response()->json(['addresses' => $addresses]);
    }
}
