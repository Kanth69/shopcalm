<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pincode;

class PincodeSeeder extends Seeder
{
    public function run(): void
    {
        $pincodes = [
            // Hyderabad & Telangana
            ['pincode' => '500081', 'city' => 'Hyderabad (Hitec City)', 'state' => 'Telangana', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '500032', 'city' => 'Hyderabad (Gachibowli)', 'state' => 'Telangana', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '500001', 'city' => 'Hyderabad (Abids)', 'state' => 'Telangana', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '500072', 'city' => 'Hyderabad (Kukatpally)', 'state' => 'Telangana', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '500034', 'city' => 'Hyderabad (Banjara Hills)', 'state' => 'Telangana', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '506001', 'city' => 'Warangal', 'state' => 'Telangana', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],

            // Bengaluru & Karnataka
            ['pincode' => '560001', 'city' => 'Bengaluru (MG Road)', 'state' => 'Karnataka', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '560034', 'city' => 'Bengaluru (Koramangala)', 'state' => 'Karnataka', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '560066', 'city' => 'Bengaluru (Whitefield)', 'state' => 'Karnataka', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '560100', 'city' => 'Bengaluru (Electronic City)', 'state' => 'Karnataka', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '570001', 'city' => 'Mysuru', 'state' => 'Karnataka', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],

            // Andhra Pradesh
            ['pincode' => '530001', 'city' => 'Visakhapatnam', 'state' => 'Andhra Pradesh', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '520001', 'city' => 'Vijayawada', 'state' => 'Andhra Pradesh', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '517501', 'city' => 'Tirupati', 'state' => 'Andhra Pradesh', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],

            // Mumbai & Maharashtra
            ['pincode' => '400001', 'city' => 'Mumbai (Fort)', 'state' => 'Maharashtra', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '400050', 'city' => 'Mumbai (Bandra)', 'state' => 'Maharashtra', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '400076', 'city' => 'Mumbai (Powai)', 'state' => 'Maharashtra', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '411001', 'city' => 'Pune', 'state' => 'Maharashtra', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],

            // Delhi NCR
            ['pincode' => '110001', 'city' => 'New Delhi (Connaught Place)', 'state' => 'Delhi', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '110016', 'city' => 'New Delhi (Hauz Khas)', 'state' => 'Delhi', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '122001', 'city' => 'Gurugram', 'state' => 'Haryana', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '201301', 'city' => 'Noida', 'state' => 'Uttar Pradesh', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],

            // Chennai & Tamil Nadu
            ['pincode' => '600001', 'city' => 'Chennai (George Town)', 'state' => 'Tamil Nadu', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '600096', 'city' => 'Chennai (OMR / Perungudi)', 'state' => 'Tamil Nadu', 'delivery_days' => 2, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '641001', 'city' => 'Coimbatore', 'state' => 'Tamil Nadu', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],

            // Kolkata & West Bengal
            ['pincode' => '700001', 'city' => 'Kolkata (BBD Bagh)', 'state' => 'West Bengal', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '700091', 'city' => 'Kolkata (Salt Lake)', 'state' => 'West Bengal', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],

            // Ahmedabad, Jaipur & Other Major Hubs
            ['pincode' => '380001', 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '302001', 'city' => 'Jaipur', 'state' => 'Rajasthan', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '682001', 'city' => 'Kochi', 'state' => 'Kerala', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '462001', 'city' => 'Bhopal', 'state' => 'Madhya Pradesh', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '226001', 'city' => 'Lucknow', 'state' => 'Uttar Pradesh', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
            ['pincode' => '160017', 'city' => 'Chandigarh', 'state' => 'Chandigarh', 'delivery_days' => 3, 'is_cod_available' => true, 'delivery_charge' => 0.00, 'is_serviceable' => true],
        ];

        foreach ($pincodes as $data) {
            Pincode::updateOrCreate(['pincode' => $data['pincode']], $data);
        }
    }
}
