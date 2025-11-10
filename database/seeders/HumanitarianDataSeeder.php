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
        // Create Request Types
        $requestTypes = [
            [
                'name' => 'public',
                'name_ar' => 'عام',
                'description' => 'Public institutions requests (churches, municipalities)',
                'format' => null
            ],
            [
                'name' => 'humanitarian',
                'name_ar' => 'إنساني',
                'description' => 'Humanitarian aid (educational, medical, healing, social)',
                'format' => null
            ],
            [
                'name' => 'diapers',
                'name_ar' => 'حفاضات',
                'description' => 'Diaper distribution requests',
                'format' => null
            ],
            [
                'name' => 'others',
                'name_ar' => 'أخرى',
                'description' => 'Other types of requests',
                'format' => null
            ]
        ];

        foreach ($requestTypes as $type) {
            RequestType::create($type);
        }

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

        // Create PW Members
        $pwMembers = [
            ['name' => 'Michel Achkouti', 'phone' => '70048170', 'email' => 'michel@projectwatan.org', 'is_active' => true],
            ['name' => 'Jean Khoury', 'phone' => '71123456', 'email' => 'jean@projectwatan.org', 'is_active' => true],
            ['name' => 'Marie Haddad', 'phone' => '76234567', 'email' => 'marie@projectwatan.org', 'is_active' => true],
            ['name' => 'Antoine Sleiman', 'phone' => '70345678', 'email' => 'antoine@projectwatan.org', 'is_active' => true],
            ['name' => 'Nadia Frem', 'phone' => '71456789', 'email' => 'nadia@projectwatan.org', 'is_active' => true],
        ];

        foreach ($pwMembers as $member) {
            PwMember::create($member);
        }

        // Create test user Michel Achkouti
        $testUser = User::updateOrCreate(
            ['mobile' => '96170048170'],
            [
                'name' => 'Michel Achkouti',
                'email' => 'michel.achkouti@projectwatan.org',
                'password' => Hash::make('@MA0404ach'),
                'manager_id' => null, // Reports to self
            ]
        );

        // Assign HOR role (we'll need to create this role)
        if (!$testUser->hasRole('hor')) {
            $testUser->assignRole('hor');
        }

        // Create sample voters (we'll need at least one city)
        $sampleCity = City::first();
        
        if ($sampleCity) {
            $voters = [
                [
                    'first_name' => 'جورج',
                    'father_name' => 'أنطوان',
                    'last_name' => 'خوري',
                    'city_id' => $sampleCity->id,
                    'ro_number' => '1234567',
                    'phone' => '76111222'
                ],
                [
                    'first_name' => 'ماري',
                    'father_name' => 'جوزيف',
                    'last_name' => 'حداد',
                    'city_id' => $sampleCity->id,
                    'ro_number' => '2345678',
                    'phone' => '71222333'
                ],
                [
                    'first_name' => 'بيار',
                    'father_name' => 'ميشال',
                    'last_name' => 'عون',
                    'city_id' => $sampleCity->id,
                    'ro_number' => '3456789',
                    'phone' => '70333444'
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
                    'city_id' => $sampleCity->id,
                    'contact_person' => 'رئيس البلدية',
                    'phone' => '09540540'
                ],
                [
                    'name' => 'كنيسة مار جرجس',
                    'description' => 'Saint George Church',
                    'city_id' => $sampleCity->id,
                    'contact_person' => 'الأب بولس',
                    'phone' => '09545678'
                ],
                [
                    'name' => 'مدرسة القديس يوسف',
                    'description' => 'Saint Joseph School',
                    'city_id' => $sampleCity->id,
                    'contact_person' => 'المدير العام',
                    'phone' => '09556789'
                ]
            ];

            foreach ($institutions as $institution) {
                PublicInstitution::create($institution);
            }
        }

        $this->command->info('✅ Humanitarian data seeded successfully!');
    }
}