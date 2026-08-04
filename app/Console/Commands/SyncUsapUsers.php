<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncUsapUsers extends Command
{
    protected $signature = 'users:sync-usap';

    protected $description = 'Crea o actualiza los usuarios @usap.edu, pidiendo la contraseña de cada uno por consola';

    /** @var array<int, array{name: string, email: string, role: string}> */
    protected array $users = [
        ['name' => 'PAOLA JACKELINE ANTUNEZ DUARTE', 'email' => '1220723@usap.edu', 'role' => 'agent'],
        ['name' => 'Ramon Montoya', 'email' => 'Ramon.montoya@usap.edu', 'role' => 'user'],
        ['name' => 'José Desarrollo Apps', 'email' => '3230017@usap.edu', 'role' => 'admin'],
        ['name' => 'Abisaid Moran', 'email' => '1210795@usap.edu', 'role' => 'admin'],
        ['name' => 'Karolina Castro', 'email' => '1180739@usap.edu', 'role' => 'admin'],
    ];

    public function handle(): int
    {
        foreach ($this->users as $data) {
            $password = $this->secret("Contraseña para {$data['email']} ({$data['name']}, {$data['role']}):");

            if (! $password) {
                $this->warn("Omitido {$data['email']}: contraseña vacía.");

                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'password' => $password,
                    'email_verified_at' => now(),
                ]
            );

            $this->info("OK: {$user->email} ({$data['role']})");
        }

        return self::SUCCESS;
    }
}
