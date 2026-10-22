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

        // Query database strictly — only pincodes present in DB and marked serviceable are allowed
        $record = Pincode::where('pincode', $pincode)->first();

        if (!$record) {
            return [
                'success' => true,
                'is_serviceable' => false,
                'message' => "Sorry, delivery is currently unavailable to pincode {$pincode}.",
                'pincode' => $pincode,
            ];
        }

        if (!$record->is_serviceable) {
            return [
                'success' => true,
                'is_serviceable' => false,
                'city' => $record->city,
                'state' => $record->state,
                'message' => "Sorry, delivery is currently unavailable to pincode {$pincode} ({$record->city}).",
                'pincode' => $pincode,
            ];
        }

        $freeShippingMin = (float) Setting::get('free_shipping_min', 499);
        $deliveryDays = (int) ($record->delivery_days ?? 3);
        $deliveryDate = $record->getEstimatedDeliveryDate();
        $deliveryText = $record->getEstimatedDeliveryText();
        $city = $record->city;
        $state = $record->state;
        $locationText = "{$record->city}, {$record->pincode}";

        // Master COD Switch check (Level 1) & Pincode availability check (Level 2)
        $isGlobalCodEnabled = (Setting::get('enable_cod', '1') === '1');
        $isCodAvailable = $isGlobalCodEnabled && (bool) $record->is_cod_available;

        // Delivery Charge Hierarchy:
        // Priority 1: Specific Pincode Delivery Charge (if configured > 0 in pincodes table)
        // Priority 2: Store Default Delivery Fee (from store settings, e.g. ₹40.00)
        $pincodeCharge = ($record->delivery_charge !== null) ? (float) $record->delivery_charge : 0.00;
        $defaultShippingFee = (float) Setting::get('default_shipping_fee', 40.00);
        $deliveryCharge = ($pincodeCharge > 0) ? $pincodeCharge : $defaultShippingFee;

        $codFee = 0.00;
        if ($isCodAvailable) {
            // Priority 1: Specific Pincode COD Fee (if configured in pincodes table)
            // Priority 2: Store Default COD Flat Fee (from store settings, e.g. ₹40)
            $codFee = ($record->cod_fee !== null)
                ? (float) $record->cod_fee
                : (float) Setting::get('cod_flat_fee', 40.00);
        }

        return [
            'success' => true,
            'is_serviceable' => true,
            'pincode' => $record->pincode,
            'city' => $city,
            'state' => $state,
            'location_text' => $locationText,
            'delivery_days' => $deliveryDays,
            'estimated_delivery' => $deliveryText,
            'estimated_date_iso' => $deliveryDate->toISOString(),
            'is_global_cod_enabled' => $isGlobalCodEnabled,
            'is_cod_available' => $isCodAvailable,
            'delivery_charge' => $deliveryCharge,
            'cod_fee' => $codFee,
            'free_shipping_min' => $freeShippingMin,
            'message' => "Delivery available to {$locationText} by {$deliveryText}!",
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
     * Retrieve active delivery location from session or fallback cookie (always verified against DB)
     */
    public function getSessionLocation(): ?array
    {
        $sessionLoc = Session::get('delivery_location');
        $pincode = ($sessionLoc['pincode'] ?? null)
            ?? Session::get('delivery_pincode')
            ?? request()->cookie('delivery_pincode');

        if ($pincode) {
            $check = $this->checkServiceability($pincode);
            if ($check['is_serviceable']) {
                Session::put('delivery_location', $check);
                return $check;
            }
            Session::forget(['delivery_location', 'delivery_pincode']);
        }

        return null;
    }
}
