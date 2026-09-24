<?php

namespace App\Console\Commands;

use App\Models\Reseller;
use Illuminate\Console\Command;

class ClearDemoResellers extends Command
{
    protected $signature = 'resellers:clear-demo {--force : Não pede confirmação}';

    protected $description = 'Remove os revendedores fictícios criados para teste do mapa';

    public function handle(): int
    {
        $query = Reseller::where('notes', 'like', '%[DEMO]%');
        $count = $query->count();

        if ($count === 0) {
            $this->info('Nenhum revendedor de teste encontrado.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn("Serão removidos {$count} revendedores marcados como [DEMO]:");
        foreach ($query->get(['name', 'city']) as $r) {
            $this->line("  • {$r->name} — {$r->city}");
        }
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Confirma a remoção?', true)) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        $query->delete();
        $this->info("✔ {$count} revendedores de teste removidos.");

        return self::SUCCESS;
    }
}
