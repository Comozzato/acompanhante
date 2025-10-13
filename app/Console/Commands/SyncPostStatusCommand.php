<?php

namespace App\Console\Commands;

use App\Behaviors\CpfBehaviors;
use App\Models\User;
use App\Modules\Anunciante\Services\AnuncianteService;
use App\Services\AnuncioApiService;
use Illuminate\Console\Command;

class SyncPostStatusCommand extends Command
{

    public function __construct(private AnuncianteService $service)
    {

        parent::__construct();
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-post-status-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        $users = User::where('role', 'anct')->select('id', 'cpf')->get();

        foreach ($users as $user) {
            if (empty($user->cpf)) {
                continue;
            }
            $this->service->atualizarPublicacoesAnunciantes($user);
        }
    }
}
