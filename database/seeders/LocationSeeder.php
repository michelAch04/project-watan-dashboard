<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create lebanese governorates
        $governorates = [
            ['name' => 'Beirut', 'name_ar' => 'بيروت'],
            ['name' => 'Mount Lebanon', 'name_ar' => 'لبنان الجبل'],
            ['name' => 'North Lebanon', 'name_ar' => 'شمال لبنان'],
            ['name' => 'South Lebanon', 'name_ar' => 'جنوب لبنان'],
            ['name' => 'Bekaa', 'name_ar' => 'البقاع'],
            ['name' => 'Nabatieh', 'name_ar' => 'النبطية'],
            ['name' => 'Akkar', 'name_ar' => 'عكار'],
            ['name' => 'Baalbek-Hermel', 'name_ar' => 'بعلبك-الهرمل'],
        ];
        foreach ($governorates as $governorate) {
            \App\Models\Governorate::create($governorate);
        }

        // Create lebanese districts (in mount lebanon only for simplicity)
        $districts = [
            ['name' => 'Matn', 'name_ar' => 'المتن', 'governorate_id' => 2],
            ['name' => 'Keserwan', 'name_ar' => 'كسروان', 'governorate_id' => 2],
            ['name' => 'Aley', 'name_ar' => 'عاليه', 'governorate_id' => 2],
            ['name' => 'Chouf', 'name_ar' => 'الشوف', 'governorate_id' => 2],
            ['name' => 'Jbeil', 'name_ar' => 'جبيل', 'governorate_id' => 2],
        ];
        foreach ($districts as $district) {
            \App\Models\District::create($district);   
        }

        // Create lebanese zones (in jbeil only for simplicity)
        $zones = [
            ['name' => 'Zone 1', 'name_ar' => 'المنطقة 1', 'district_id' => 5],
            ['name' => 'Zone 2', 'name_ar' => 'المنطقة 2', 'district_id' => 5],
            ['name' => 'Zone 3', 'name_ar' => 'المنطقة 3', 'district_id' => 5],
        ];
        foreach ($zones as $zone) {
            \App\Models\Zone::create($zone);
        }

        // Create lebanese cities (in jbeil only for simplicity)
        $cities = [
            ['name' => 'Byblos', 'name_ar' => 'جبيل', 'zone_id' => 1],
            ['name' => 'Amchit', 'name_ar' => 'أمشيت', 'zone_id' => 1],
            ['name' => 'Qartaba', 'name_ar' => 'قرطبا', 'zone_id' => 2],
            ['name' => 'Ehmej', 'name_ar' => 'إهمج', 'zone_id' => 2],
            ['name' => 'Haqel', 'name_ar' => 'حقل', 'zone_id' => 3],
            ['name' => 'Jaj', 'name_ar' => 'جاج', 'zone_id' => 3],
        ];
        foreach ($cities as $city) {
            \App\Models\City::create($city);
        }

        $this->command->info('✅ Locations data seeded successfully!');
    }
}
