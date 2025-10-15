<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lunar\Models\Customer;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create {first_name} {last_name} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a user and the customer model.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $first_name = $this->argument('first_name');
        $last_name = $this->argument('last_name');
        $email = $this->argument('email');

        $user = User::create([
            'name' => "$first_name $last_name",
            'email' => $email,
            'password' => Hash::make(Str::random(12)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer = Customer::create([
            'title' => '',
            'first_name' => $first_name,
            'last_name' => $last_name,
        ]);

        // Asocia el customer al usuario
        $customer->users()->attach($user);

        $plain_text_token = $user->createToken($email, $this->setUserPermissions())->plainTextToken;

        $this->info("User created for {$email}. Token: {$plain_text_token}");
    }

    private function setUserPermissions(): array
    {
        return [
            'cart:create',
            'cart:get',
            'cart:checkout',
            'cart:clear',
            'cart:confirm',
            'cart:lines',

            'customer:list',
            'customer:create',
            'customer:get',
            'customer:delete',
            'customer:group:create',

            'order:get',
            'order:transaction:create',

            'pickup:list',

            'product:list',
            'product:create',
            'product:brand:list',
            'product:category:list',
            'product:get',

            'product:search:*',
        ];
    }
}
