<?php

//database/seeders/ForumSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ForumSetting;
use App\Models\ForumMessage;
use Illuminate\Support\Facades\Hash;

class ForumSeeder extends Seeder
{
    public function run()
    {
        // Buat forum settings default
        ForumSetting::updateOrCreate(
            ['id' => 1],
            [
                'is_open' => true,
                'max_users' => 100,
                'allow_attachments' => true,
                'allow_polls' => true
            ]
        );

        // Buat user moderator jika belum ada
        $moderator = User::updateOrCreate(
            ['email' => 'moderator@gmail.com'],
            [
                'name' => 'Moderator Forum',
                'role_id' => 3, // Role moderator
                'password' => Hash::make('password'),
                'email_verified_at' => now()
            ]
        );

        // Update user admin yang sudah ada
        User::where('email', 'admin@gmail.com')->update([
            'role_id' => 1 // Pastikan admin punya role_id = 1
        ]);

        // Buat beberapa pesan contoh
        $admin = User::where('role_id', 1)->first();
        $user = User::where('role_id', 2)->first();

        if ($admin) {
            ForumMessage::create([
                'user_id' => $admin->id,
                'message' => 'Selamat datang di forum diskusi PT RKA! Silakan bertanya dan berbagi informasi seputar GIS dan Remote Sensing.',
                'message_type' => 'text',
                'metadata' => ['is_welcome' => true]
            ]);
        }

        if ($user) {
            ForumMessage::create([
                'user_id' => $user->id,
                'message' => 'Terima kasih! Saya ingin bertanya tentang pelatihan GIS yang akan datang.',
                'message_type' => 'text'
            ]);
        }

        if ($moderator) {
            ForumMessage::create([
                'user_id' => $moderator->id,
                'message' => 'Untuk informasi pelatihan terbaru, silakan cek halaman blog kami secara berkala.',
                'message_type' => 'text'
            ]);
        }

        echo "Forum seeder completed successfully!\n";
        echo "Login credentials:\n";
        echo "Admin: admin@gmail.com / password\n";
        echo "Moderator: moderator@gmail.com / password\n";
        echo "User: user@gmail.com / password\n";
    }
}