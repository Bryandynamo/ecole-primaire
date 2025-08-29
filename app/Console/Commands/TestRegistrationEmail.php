<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewUserRegistered;
use App\Models\User;
use App\Models\Enseignant;
use Illuminate\Support\Str;

class TestRegistrationEmail extends Command
{
    protected $signature = 'email:test-registration {--with-enseignant}';
    protected $description = 'Send a test NewUserRegistered notification to the admin email';

    public function handle()
    {
        $adminEmail = config('app.admin_notify_email');
        if (empty($adminEmail)) {
            $this->error('config(app.admin_notify_email) is empty. Set ADMIN_NOTIFY_EMAIL in .env');
            return Command::FAILURE;
        }

        // Use a persisted user if available, else create a temporary test user
        $user = User::first();
        $createdTempUser = false;
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test.user.' . Str::random(6) . '@example.com',
                'password' => bcrypt(Str::random(16)),
            ]);
            $createdTempUser = true;
        }

        // For testing, we don't require related foreign keys; pass null or a lightweight instance
        $enseignant = null;

        try {
            Notification::route('mail', $adminEmail)
                ->notifyNow(new NewUserRegistered($user, $enseignant));
            $this->info('Test email sent to ' . $adminEmail);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
