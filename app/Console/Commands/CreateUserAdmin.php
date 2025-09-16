<?php

declare(strict_types=1);
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User; // importa o model
use Illuminate\Support\Facades\Hash;

class CreateUserAdmin extends Command
{
    /**
     * O nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'app:create-admin 
                            {email : Email do usuário} 
                            {password : Senha do usuário}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Cria um usuário administrador no sistema';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $password = $this->argument('password');

        if (User::where('email', $email)->exists()) {
            $this->error("❌ Já existe um usuário com o email {$email}");
            return;
        }

        $user = User::create([
            'email' => $email,
            'cpf' => User::factory()->make()->cpf,
            'password' => Hash::make($password),
            'role' => 'admn', // precisa ter essa coluna no banco
        ]);

        $this->info("✅ Usuário admin {$user->name} criado com sucesso!");
    }
}

