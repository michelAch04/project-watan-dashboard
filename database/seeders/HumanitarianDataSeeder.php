<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestType;
use App\Models\RequestStatus;
use App\Models\PwMember;
use App\Models\User;
use App\Models\Voter;
use App\Models\PublicInstitution;
use App\Models\City;
use Illuminate\Support\Facades\Hash;

class HumanitarianDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Request Statuses
        $requestStatuses = [
            [
                'name' => 'draft',
                'name_ar' => 'مسودة',
                'description' => 'Request is in draft state',
                'order' => 1
            ],
            [
                'name' => 'published',
                'name_ar' => 'منشور',
                'description' => 'Request has been published and sent to manager',
                'order' => 2
            ],
            [
                'name' => 'approved',
                'name_ar' => 'موافق عليه',
                'description' => 'Request approved by manager',
                'order' => 3
            ],
            [
                'name' => 'rejected',
                'name_ar' => 'مرفوض',
                'description' => 'Request rejected and sent back to sender',
                'order' => 4
            ],
            [
                'name' => 'final_approval',
                'name_ar' => 'موافقة نهائية',
                'description' => 'Request reached HOR and has final approval',
                'order' => 5
            ],
            [
                'name' => 'ready_for_collection',
                'name_ar' => 'جاهز للتسليم',
                'description' => 'Request is ready to be collected',
                'order' => 6
            ],
            [
                'name' => 'collected',
                'name_ar' => 'تم التسليم',
                'description' => 'Request has been collected',
                'order' => 7
            ]
        ];

        foreach ($requestStatuses as $status) {
            RequestStatus::create($status);
        }

        // Create admin user Michel Achkouti
        $testUser = User::updateOrCreate(
            ['mobile' => '96170048170'],
            [
                'name' => 'Michel Achkouti',
                'mobile' => '96170048170',
                'email' => 'michel.achkouti@projectwatan.org',
                'password' => Hash::make('@MA0404ach'),
                'manager_id' => null, // Reports to self
            ]
        );

        // Assign admin role (we'll need to create this role)
        if (!$testUser->hasRole('admin')) {
            $testUser->assignRole('admin');
        }

        // Create sample voters (we'll need at least one city)
        $voters = [
            [
                'first_name' => 'جورج',
                'father_name' => 'أنطوان',
                'last_name' => 'خوري',
                'mother_full_name' => 'سلمى يوسف',
                'city_id' => 1,
                'register_number' => '9',
                'phone' => '76111222'
            ],
            [
                'first_name' => 'ماري',
                'father_name' => 'جوزيف',
                'last_name' => 'حداد',
                'mother_full_name' => 'ليلى سمير',
                'city_id' => 1,
                'register_number' => '23',
                'phone' => '71222333'
            ],
            [
                'first_name' => 'بيار',
                'father_name' => 'ميشال',
                'last_name' => 'عون',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 2,
                'register_number' => '24',
                'phone' => '70333444'
            ],
            [
                'first_name' => 'سارة',
                'father_name' => 'إبراهيم',
                'last_name' => 'نعيم',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 2,
                'register_number' => '45',
                'phone' => '70999888'
            ],
            [
                'first_name' => 'ليلى',
                'father_name' => 'سمير',
                'last_name' => 'حسن',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 3,
                'register_number' => '67',
                'phone' => '70111222'
            ],
            [
                'first_name' => 'كريم',
                'father_name' => 'جمال',
                'last_name' => 'يوسف',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 3,
                'register_number' => '89',
                'phone' => '70222333'
            ],
            [
                'first_name' => 'نور',
                'father_name' => 'فادي',
                'last_name' => 'سعيد',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 4,
                'register_number' => '90',
                'phone' => '70333445'
            ],
            [
                'first_name' => 'رامي',
                'father_name' => 'زياد',
                'last_name' => 'جميل',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 4,
                'register_number' => '101',
                'phone' => '70444556'
            ],
            [
                'first_name' => 'هدى',
                'father_name' => 'علي',
                'last_name' => 'موسى',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 5,
                'register_number' => '112',
                'phone' => '70555667'
            ],
            [
                'first_name' => 'سامر',
                'father_name' => 'خالد',
                'last_name' => 'شريف',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 5,
                'register_number' => '123',
                'phone' => '70666778'
            ],
            [
                'first_name' => 'جميلة',
                'father_name' => 'سمير',
                'last_name' => 'فارس',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 6,
                'register_number' => '134',
                'phone' => '70777889'
            ],
            [
                'first_name' => 'زياد',
                'father_name' => 'رامي',
                'last_name' => 'كريم',
                'mother_full_name' => 'ناديا جورج',
                'city_id' => 6,
                'register_number' => '145',
                'phone' => '70888990'
            ]
        ];

        foreach ($voters as $voter) {
            Voter::create($voter);
        }

        // Create sample public institutions
        $institutions = [
            [
                'name' => 'بلدية جبيل',
                'description' => 'Municipality of Jbeil',
                'city_id' => 1,
                'contact_person' => 'رئيس البلدية',
                'phone' => '09540540'
            ],
            [
                'name' => 'كنيسة مار جرجس',
                'description' => 'Saint George Church',
                'city_id' => 2,
                'contact_person' => 'الأب بولس',
                'phone' => '09545678'
            ],
            [
                'name' => 'مدرسة القديس يوسف',
                'description' => 'Saint Joseph School',
                'city_id' => 3,
                'contact_person' => 'المدير العام',
                'phone' => '09556789'
            ]
        ];

        foreach ($institutions as $institution) {
            PublicInstitution::create($institution);
        }

        $this->command->info('✅ Humanitarian data seeded successfully!');
    }
}
