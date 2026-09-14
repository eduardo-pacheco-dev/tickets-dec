<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('user:create-admin {name? : Nome do administrador} {email? : E-mail do administrador} {--password= : Senha do administrador}')]
#[Description('Create a new administrator user')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->ask('Nome do administrador');
        $email = $this->argument('email') ?? $this->ask('E-mail do administrador');
        $password = $this->option('password') ?? $this->secret('Senha do administrador');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $this->info("Administrador criado com sucesso: {$user->email} (ID: {$user->id})");

        return self::SUCCESS;
    }
}
