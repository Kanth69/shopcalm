<?php

namespace App\Services;

use App\Models\Pincode;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

class DeliveryService
{
    /**
     * Check if a 6-digit PIN code is serviceable and return delivery details
     */
    public function checkServiceability(?string $pincode): array
    {
        $pincode = trim((string)$pincode);

        // Validation: 6 digits numeric
        if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            return [
                'success' => false,
                'is_serviceable' => false,
                'message' => 'Please enter a valid 6-digit Indian PIN code.',
                'pincode' => $pincode,
            ];
        }

        // Query database
        $record = Pincode::where('pincode', $pincode)->first();

        if (!$record || !$record->is_serviceable) {
            return [
                'success' => true,
                'is_serviceable' => false,
                'message' => "Sorry, delivery is currently unavailable to pincode {$pincode}.",
                'pincode' => $pincode,
            ];
        }

        $deliveryDays = $record->delivery_days ?? 3;
        $deliveryDate = $record->getEstimatedDeliveryDate();
        $deliveryText = $record->getEstimatedDeliveryText();

        $freeShippingMin = (float) Setting::get('free_shipping_min', 499);

        $isCodFeeEnabled = Setting::get('cod_fee_enabled', '1');
        $isCodFeeActive = ($isCodFeeEnabled === '1' || $isCodFeeEnabled === 'true' || $isCodFeeEnabled === true || $isCodFeeEnabled === 1);

        $codFee = 0.00;
        if ($isCodFeeActive && $record->is_cod_available) {
            $codFee = ($record->cod_fee !== null)
                ? (float) $record->cod_fee
                : (float) Setting::get('cod_flat_fee', 40.00);
        }

        return [
            'success' => true,
            'is_serviceable' => true,
            'pincode' => $record->pincode,
            'city' => $record->city,
            'state' => $record->state,
            'location_text' => "{$record->city}, {$record->pincode}",
            'delivery_days' => $deliveryDays,
            'estimated_delivery' => $deliveryText,
            'estimated_date_iso' => $deliveryDate->toISOString(),
            'is_cod_available' => (bool)$record->is_cod_available,
            'delivery_charge' => (float)$record->delivery_charge,
            'cod_fee' => $codFee,
            'free_shipping_min' => $freeShippingMin,
            'message' => "Delivery available to {$record->city} by {$deliveryText}!",
        ];
    }

    /**
     * Store active delivery location in session & cookie
     */
    public function setSessionLocation(array $data): void
    {
        Session::put('delivery_location', $data);
        Session::put('delivery_pincode', $data['pincode'] ?? null);

        // Also queue a 30-day cookie for persistence
        Cookie::queue('delivery_pincode', $data['pincode'] ?? '', 43200);
        if (!empty($data['location_text'])) {
            Cookie::queue('delivery_location_text', $data['location_text'], 43200);
        }
    }

    /**
     * Retrieve active delivery location from session or fallback cookie/user default
     */
    public function getSessionLocation(): ?array
    {
        if (Session::has('delivery_location')) {
            return Session::get('delivery_location');
        }

        $pincode = Session::get('delivery_pincode') ?? request()->cookie('delivery_pincode');

        if ($pincode) {
            $check = $this->checkServiceability($pincode);
            if ($check['is_serviceable']) {
                Session::put('delivery_location', $check);
                return $check;
            }
        }

        // If customer is logged in, check their primary address zip
        if (auth('customer')->check()) {
            $user = auth('customer')->user();
            $address = $user->addresses()->latest()->first();
            if ($address && $address->zip) {
                $check = $this->checkServiceability($address->zip);
                if ($check['is_serviceable']) {
                    Session::put('delivery_location', $check);
                    return $check;
                }
            }
        }

        return null;
    }
}
