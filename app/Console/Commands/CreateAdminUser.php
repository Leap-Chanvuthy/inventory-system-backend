<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin-user {name} {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for the application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Check if the user already exists
        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists.');
            return;
        }

        // Create the admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password), // Using bcrypt hashing algorithm
            'role' => 'ADMIN', // Set the role to admin
        ]);

        // Display a success message
        $this->info('Admin user created successfully.');
    }

    // Use this command to create admin user
    // php artisan create:admin-user "John Doe" "admin@example.com" "securepassword"


}
