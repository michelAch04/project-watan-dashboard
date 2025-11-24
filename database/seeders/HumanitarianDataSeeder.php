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
                'username' => 'michel.achkouti',
                'mobile' => '96170048170',
                'email' => 'michel.achkouti@projectwatan.org',
                'password' => Hash::make('@MA0404ach'),
                'manager_id' => null, // Reports to self
            ]
        );

        $testUser2 = User::updateOrCreate(
            ['mobile' => '96103655326'],
            [
                'username' => 'elias.barbour',
                'mobile' => '96103655326',
                'email' => 'elias.barbour@projectwatan.org',
                'password' => Hash::make('Sanita@2025'),
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
                'first_name' => 'test',
                'father_name' => 'hor',
                'last_name' => 'zone 1',
                'mother_full_name' => 'mother test',
                'city_id' => 1,
                'register_number' => '1',
                'phone' => '70000001'
            ],
            [
                'first_name' => 'test',
                'father_name' => 'hor',
                'last_name' => 'zone 2',
                'mother_full_name' => 'mother test',
                'city_id' => 3,
                'register_number' => '2',
                'phone' => '70000002'
            ],
            [
                'first_name' => 'test',
                'father_name' => 'hor',
                'last_name' => 'zone 3',
                'mother_full_name' => 'mother test',
                'city_id' => 5,
                'register_number' => '3',
                'phone' => '70000003'
            ],
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
            ],
            [
                'first_name' => 'فادي',
                'father_name' => 'نبيل',
                'last_name' => 'حنا',
                'mother_full_name' => 'ريتا طانيوس',
                'city_id' => 1,
                'register_number' => '156',
                'phone' => '71111223'
            ],
            [
                'first_name' => 'سوزان',
                'father_name' => 'طوني',
                'last_name' => 'أبو خاطر',
                'mother_full_name' => 'جورجيت منصور',
                'city_id' => 2,
                'register_number' => '167',
                'phone' => '71222334'
            ],
            [
                'first_name' => 'طارق',
                'father_name' => 'عماد',
                'last_name' => 'صالح',
                'mother_full_name' => 'هالة محمود',
                'city_id' => 3,
                'register_number' => '178',
                'phone' => '71333445'
            ],
            [
                'first_name' => 'ندى',
                'father_name' => 'وسيم',
                'last_name' => 'الخوري',
                'mother_full_name' => 'كارول سعد',
                'city_id' => 4,
                'register_number' => '189',
                'phone' => '71444556'
            ],
            [
                'first_name' => 'وليد',
                'father_name' => 'باسم',
                'last_name' => 'عبود',
                'mother_full_name' => 'سميرة فياض',
                'city_id' => 5,
                'register_number' => '190',
                'phone' => '71555667'
            ],
            [
                'first_name' => 'ريما',
                'father_name' => 'إيلي',
                'last_name' => 'شدياق',
                'mother_full_name' => 'ليال جبران',
                'city_id' => 6,
                'register_number' => '201',
                'phone' => '71666778'
            ],
            [
                'first_name' => 'سليم',
                'father_name' => 'نديم',
                'last_name' => 'حرب',
                'mother_full_name' => 'رانيا صعب',
                'city_id' => 1,
                'register_number' => '212',
                'phone' => '71777889'
            ],
            [
                'first_name' => 'ياسمين',
                'father_name' => 'مارون',
                'last_name' => 'طراد',
                'mother_full_name' => 'ميرنا عازار',
                'city_id' => 2,
                'register_number' => '223',
                'phone' => '71888990'
            ],
            [
                'first_name' => 'جاد',
                'father_name' => 'رياض',
                'last_name' => 'نجار',
                'mother_full_name' => 'نانسي حداد',
                'city_id' => 3,
                'register_number' => '234',
                'phone' => '71999001'
            ],
            [
                'first_name' => 'كلود',
                'father_name' => 'سامي',
                'last_name' => 'معلوف',
                'mother_full_name' => 'فيروز خليل',
                'city_id' => 4,
                'register_number' => '245',
                'phone' => '76000112'
            ],
            [
                'first_name' => 'روي',
                'father_name' => 'جورج',
                'last_name' => 'بولس',
                'mother_full_name' => 'سيلفيا روكز',
                'city_id' => 5,
                'register_number' => '256',
                'phone' => '76111223'
            ],
            [
                'first_name' => 'دانا',
                'father_name' => 'هاني',
                'last_name' => 'سمعان',
                'mother_full_name' => 'جويل بطرس',
                'city_id' => 6,
                'register_number' => '267',
                'phone' => '76222334'
            ],
            [
                'first_name' => 'عمر',
                'father_name' => 'رشيد',
                'last_name' => 'قاسم',
                'mother_full_name' => 'مايا حلو',
                'city_id' => 1,
                'register_number' => '278',
                'phone' => '76333445'
            ],
            [
                'first_name' => 'لارا',
                'father_name' => 'زاهر',
                'last_name' => 'فرح',
                'mother_full_name' => 'دينا نصر',
                'city_id' => 2,
                'register_number' => '289',
                'phone' => '76444556'
            ],
            [
                'first_name' => 'أنطوني',
                'father_name' => 'بشار',
                'last_name' => 'عيسى',
                'mother_full_name' => 'تانيا حنين',
                'city_id' => 3,
                'register_number' => '290',
                'phone' => '76555667'
            ],
            [
                'first_name' => 'كارلا',
                'father_name' => 'جو',
                'last_name' => 'نعمة',
                'mother_full_name' => 'ساندرا يونس',
                'city_id' => 4,
                'register_number' => '301',
                'phone' => '76666778'
            ],
            [
                'first_name' => 'نادر',
                'father_name' => 'شربل',
                'last_name' => 'متى',
                'mother_full_name' => 'غادة بشارة',
                'city_id' => 5,
                'register_number' => '312',
                'phone' => '76777889'
            ],
            [
                'first_name' => 'ريتا',
                'father_name' => 'بطرس',
                'last_name' => 'حايك',
                'mother_full_name' => 'كلوديا سلامة',
                'city_id' => 6,
                'register_number' => '323',
                'phone' => '76888990'
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
