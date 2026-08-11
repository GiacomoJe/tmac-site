<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportCategoryImages extends Command
{
    protected $signature = 'categories:import-images {--force : Rebaixa mesmo se já existir arquivo}';

    protected $description = 'Baixa as imagens das categorias do site antigo e vincula no banco';

    /** slug da categoria => URL da imagem original */
    private const IMAGES = [
        'acessorios'                 => 'https://tmacimport.com.br/wp-content/uploads/2024/05/acessorios.png',
        'cabos-de-comando'           => 'https://tmacimport.com.br/wp-content/uploads/2024/05/cabos-de-comando.png',
        'carenagem'                  => 'https://tmacimport.com.br/wp-content/uploads/2024/09/TM651-2048x766.png',
        'chassi'                     => 'https://tmacimport.com.br/wp-content/uploads/2024/05/chassi.png',
        'eletrica'                   => 'https://tmacimport.com.br/wp-content/uploads/2024/05/eletrica.png',
        'ferramentas-e-equipamentos' => 'https://tmacimport.com.br/wp-content/uploads/2024/05/ferramentas.png',
        'fixacao'                    => 'https://tmacimport.com.br/wp-content/uploads/2024/05/fixacao.png',
        'freio'                      => 'https://tmacimport.com.br/wp-content/uploads/2024/05/freio.png',
        'injecao'                    => 'https://tmacimport.com.br/wp-content/uploads/2024/05/injecao.png',
        'motor'                      => 'https://tmacimport.com.br/wp-content/uploads/2024/05/motor.png',
        'roda'                       => 'https://tmacimport.com.br/wp-content/uploads/2024/05/AT103-1.png',
        'suspensao'                  => 'https://tmacimport.com.br/wp-content/uploads/2024/05/suspensao.png',
        'transmissao'                => 'https://tmacimport.com.br/wp-content/uploads/2024/05/transmissao.png',
    ];

    public function handle(): int
    {
        @ini_set('memory_limit', '256M');

        $force   = $this->option('force');
        $ok      = 0;
        $fail    = 0;
        $missing = [];

        $this->newLine();
        $this->info('Baixando imagens das categorias…');
        $this->newLine();

        foreach (self::IMAGES as $slug => $url) {
            $category = Category::where('slug', $slug)->first();

            if (! $category) {
                $missing[] = $slug;
                $this->line("  <fg=red>✗</> {$slug} — categoria não encontrada no banco");
                continue;
            }

            $ext      = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
            $relative = "categories/{$slug}.{$ext}";
            $absolute = Storage::disk('public')->path($relative);

            // Garante diretório
            $dir = dirname($absolute);
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            // Já existe?
            if (! $force && is_file($absolute) && filesize($absolute) > 512) {
                if ($category->image_path !== $relative) {
                    $category->update(['image_path' => $relative]);
                }
                $this->line("  <fg=yellow>↷</> {$category->name} — já existia, vinculada");
                $ok++;
                continue;
            }

            if ($this->download($url, $absolute)) {
                $category->update(['image_path' => $relative]);
                $size = number_format(filesize($absolute) / 1024, 0);
                $this->line("  <fg=green>✓</> {$category->name} — {$size} KB");
                $ok++;
            } else {
                $this->line("  <fg=red>✗</> {$category->name} — falha no download");
                $fail++;
            }
        }

        // ── Relatório ───────────────────────────────────────────
        $this->newLine();
        $this->info('═══ Resultado ═══');
        $this->line("  Sucesso: <fg=green>{$ok}</>");
        if ($fail > 0)  $this->line("  Falhas:  <fg=red>{$fail}</>");

        if (! empty($missing)) {
            $this->newLine();
            $this->warn('Categorias não encontradas no banco:');
            foreach ($missing as $m) $this->line("  • {$m}");
            $this->line('  Rode: php artisan db:seed --class=CategoriesSeeder');
        }

        // Categorias sem imagem
        $semImagem = Category::whereNull('image_path')->orWhere('image_path', '')->pluck('name');
        if ($semImagem->isNotEmpty()) {
            $this->newLine();
            $this->warn('Categorias ainda sem imagem ('.$semImagem->count().'):');
            foreach ($semImagem as $n) $this->line("  • {$n}");
        }

        $this->newLine();
        return self::SUCCESS;
    }

    /** Download em stream — memória constante */
    private function download(string $url, string $destination): bool
    {
        $tmp = $destination.'.part';

        try {
            $in = @fopen($url, 'rb', false, stream_context_create([
                'http' => [
                    'timeout'         => 30,
                    'follow_location' => 1,
                    'max_redirects'   => 3,
                    'user_agent'      => 'Mozilla/5.0 (compatible; TMAC-Importer/1.0)',
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]));

            if (! $in) return false;

            $out = @fopen($tmp, 'wb');
            if (! $out) { fclose($in); return false; }

            $bytes = 0;
            while (! feof($in)) {
                $chunk = fread($in, 262144);
                if ($chunk === false) break;
                fwrite($out, $chunk);
                $bytes += strlen($chunk);
                unset($chunk);
            }

            fclose($in);
            fclose($out);

            if ($bytes < 512) {
                @unlink($tmp);
                return false;
            }

            @rename($tmp, $destination);
            return true;
        } catch (\Throwable $e) {
            @unlink($tmp);
            return false;
        }
    }
}
