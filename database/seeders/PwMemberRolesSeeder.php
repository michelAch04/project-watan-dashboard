<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PwMemberRole;

class PwMemberRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'helped',
                'name_ar' => 'تم مساعدته',
                'description' => 'Member who received help through a request',
                'description_ar' => 'عضو تلقى المساعدة من خلال طلب'
            ],
            [
                'name' => 'volunteer',
                'name_ar' => 'متطوع',
                'description' => 'Active volunteer member',
                'description_ar' => 'عضو متطوع نشط'
            ],
            [
                'name' => 'organizer',
                'name_ar' => 'منظم',
                'description' => 'Event and activity organizer',
                'description_ar' => 'منظم الفعاليات والأنشطة'
            ],
            [
                'name' => 'coordinator',
                'name_ar' => 'منسق',
                'description' => 'Regional coordinator',
                'description_ar' => 'منسق إقليمي'
            ],
            [
                'name' => 'supporter',
                'name_ar' => 'داعم',
                'description' => 'General supporter of the organization',
                'description_ar' => 'داعم عام للمنظمة'
            ]
        ];

        foreach ($roles as $role) {
            PwMemberRole::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('✅ PW Member Roles seeded successfully!');
    }
}
