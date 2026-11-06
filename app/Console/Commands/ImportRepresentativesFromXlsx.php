<?php

namespace App\Console\Commands;

use App\Models\Representative;
use App\Models\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportRepresentativesFromXlsx extends Command
{
    protected $signature = 'representatives:import
                            {file=Representantes_TMAC_por_Estado.xlsx : XLSX na raiz do projeto}
                            {--sheet=Por Representante : Nome da aba a ler}
                            {--update : Atualiza telefone/e-mail/observação de quem já existe}
                            {--dry-run : Simula sem gravar}';

    protected $description = 'Importa representantes e os estados que atendem (XLSX)';

    /** Linhas que não são representante de verdade */
    private const IGNORAR = ['SEM REPRESENTANTE', 'A DEFINIR', 'NAO INFORMADO'];

    public function handle(): int
    {
        $path = base_path($this->argument('file'));

        if (! is_file($path)) {
            $this->error("Arquivo não encontrado: {$path}");

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');

        $this->info('Lendo planilha…');
        $rows = $this->readXlsx($path, (string) $this->option('sheet'));

        if (empty($rows)) {
            $this->error('Não foi possível ler a aba "'.$this->option('sheet').'".');

            return self::FAILURE;
        }

        // Mapa UF => state_id
        $estados = State::pluck('id', 'uf')->all();

        $this->line(count($rows).' linhas na planilha · '.count($estados).' UFs no banco');
        $this->newLine();

        $criados = $atualizados = $jaExistiam = $ignorados = 0;
        $vinculos = 0;
        $ufsDesconhecidas = [];
        $porEstado = [];          // uf => [nomes]
        $novos = [];

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $bar->advance();

                $nome = $this->limpa($row['Nome'] ?? '');

                if ($nome === '' || $this->devoIgnorar($nome)) {
                    $ignorados++;

                    continue;
                }

                $telefone = $this->limpa($row['Telefone'] ?? '');
                $email    = mb_strtolower($this->limpa($row['E-mail'] ?? ''));
                $obs      = $this->limpa($row['Observação'] ?? '');
                $ufsRaw   = $this->limpa($row['Estados atendidos (UF)'] ?? '');

                // Procura por e-mail (mais confiável); cai para o nome
                $rep = null;
                if ($email !== '') {
                    $rep = Representative::where('email', $email)->first();
                }
                $rep ??= Representative::whereRaw('UPPER(name) = ?', [mb_strtoupper($nome)])->first();

                $dados = [
                    'name'      => $nome,
                    'phone'     => $telefone ?: null,
                    'whatsapp'  => $this->ehCelular($telefone) ? $telefone : null,
                    'email'     => $email ?: null,
                    'bio'       => $obs ?: null,
                    'is_active' => true,
                ];

                if ($rep) {
                    if ($this->option('update')) {
                        $rep->fill($dados)->save();
                        $atualizados++;
                    } else {
                        $jaExistiam++;
                    }
                } else {
                    $rep = Representative::create($dados);
                    $criados++;
                    $novos[] = $nome;
                }

                // ── Estados atendidos ──
                $ufs = [];
                foreach (preg_split('/[,;\/]+/', $ufsRaw, -1, PREG_SPLIT_NO_EMPTY) as $pedaco) {
                    $uf = mb_strtoupper(trim($pedaco));
                    if ($uf === '') {
                        continue;
                    }
                    if (! isset($estados[$uf])) {
                        $ufsDesconhecidas[$uf] = true;

                        continue;
                    }
                    $ufs[$uf] = $estados[$uf];
                    $porEstado[$uf][$rep->id] = $nome;
                }

                if ($ufs) {
                    // syncWithoutDetaching preserva vínculos feitos à mão no admin
                    $rep->states()->syncWithoutDetaching(array_values($ufs));
                    $vinculos += count($ufs);
                }
            }

            $bar->finish();
            $this->newLine(2);

            // ── is_primary: define automaticamente só onde não há ambiguidade ──
            $primarios = 0;
            $ambiguos  = [];

            foreach ($porEstado as $uf => $nomesPorId) {
                if (count($nomesPorId) === 1) {
                    DB::table('representative_state')
                        ->where('state_id', $estados[$uf])
                        ->where('representative_id', array_key_first($nomesPorId))
                        ->update(['is_primary' => true]);
                    $primarios++;
                } else {
                    $ambiguos[$uf] = count($nomesPorId);
                }
            }

            if ($dry) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->newLine(2);
            $this->error('Falhou: '.$e->getMessage());

            return self::FAILURE;
        }

        // ═══ Relatório ═══
        $this->info('═══ Resultado ═══');
        $this->line('  Representantes cadastrados     : '.$criados);
        if ($this->option('update')) {
            $this->line('  Representantes atualizados     : '.$atualizados);
        } else {
            $this->line('  Já existiam (sem alteração)    : '.$jaExistiam);
        }
        $this->line('  Linhas ignoradas               : '.$ignorados);
        $this->line('  Vínculos representante↔estado  : '.$vinculos);
        $this->line('  UFs com sugestão definida      : '.$primarios);

        if ($novos) {
            $this->newLine();
            $this->line('Representantes cadastrados:');
            foreach (array_slice($novos, 0, 30) as $n) {
                $this->line('  • '.$n);
            }
            if (count($novos) > 30) {
                $this->line('  … e mais '.(count($novos) - 30));
            }
        }

        if ($ufsDesconhecidas) {
            $this->newLine();
            $this->warn('UFs da planilha que não existem na tabela states: '
                .implode(', ', array_keys($ufsDesconhecidas)));
        }

        if ($ambiguos) {
            $this->newLine();
            $this->warn('Estados com mais de um representante — o painel não vai sugerir');
            $this->warn('ninguém nesses casos; quem encaminha escolhe na hora:');
            foreach ($ambiguos as $uf => $qtd) {
                $this->line("  • {$uf} — {$qtd} representantes");
            }
        }

        $semCobertura = array_diff(array_keys($estados), array_keys($porEstado));
        if ($semCobertura) {
            $this->newLine();
            $this->warn('UFs sem nenhum representante: '.implode(', ', $semCobertura));
            $this->line('  (o painel não sugere ninguém — o comercial escolhe ao encaminhar)');
        }

        if ($dry) {
            $this->newLine();
            $this->warn('DRY RUN — nada foi gravado.');
        }

        return self::SUCCESS;
    }

    private function limpa(string $v): string
    {
        return trim(preg_replace('/\s{2,}/', ' ', str_replace("\u{A0}", ' ', $v)));
    }

    private function devoIgnorar(string $nome): bool
    {
        $up = $this->semAcento(mb_strtoupper($nome));

        // Só pontuação/travessão ("— — —", "-", "n/a")
        if (preg_match('/^[\p{P}\p{Z}]*$/u', $nome) || mb_strlen(trim($nome, " -—–·/")) < 3) {
            return true;
        }

        foreach (self::IGNORAR as $termo) {
            if (str_contains($up, $termo)) {
                return true;
            }
        }

        return false;
    }

    /** (11) 99999-9999 → celular; (83) 3232-3282 → fixo */
    private function ehCelular(string $telefone): bool
    {
        $d = preg_replace('/\D/', '', $telefone);

        return strlen($d) === 11 && $d[2] === '9';
    }

    private function semAcento(string $s): string
    {
        return strtr($s, [
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'É' => 'E', 'Ê' => 'E',
            'Í' => 'I', 'Ó' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ú' => 'U', 'Ç' => 'C',
        ]);
    }

    /** Leitor XLSX nativo (ZipArchive + SimpleXML), resolvendo a aba pelo nome */
    private function readXlsx(string $path, string $abaDesejada): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        // sharedStrings (pode não existir quando a planilha usa inline strings)
        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                $shared[] = isset($si->t)
                    ? (string) $si->t
                    : implode('', array_map(fn ($r) => (string) $r->t, iterator_to_array($si->r ?? [])));
            }
        }

        // Descobre qual arquivo de worksheet corresponde à aba pedida
        $alvo = null;
        $wb   = simplexml_load_string((string) $zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string((string) $zip->getFromName('xl/_rels/workbook.xml.rels'));

        if ($wb && $rels) {
            $mapaRels = [];
            foreach ($rels->Relationship as $r) {
                $mapaRels[(string) $r['Id']] = ltrim((string) $r['Target'], '/');
            }

            $i = 0;
            foreach ($wb->sheets->sheet as $sheet) {
                $i++;
                $nome = (string) $sheet['name'];
                $rid  = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];

                if (mb_strtolower(trim($nome)) === mb_strtolower(trim($abaDesejada))) {
                    $target = $mapaRels[$rid] ?? "worksheets/sheet{$i}.xml";
                    $alvo = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
                    break;
                }
            }
        }

        $alvo ??= 'xl/worksheets/sheet1.xml';

        $sheetXml = $zip->getFromName($alvo);
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sx = simplexml_load_string($sheetXml);
        $matrix = [];

        foreach ($sx->sheetData->row as $row) {
            $line = [];
            foreach ($row->c as $c) {
                preg_match('/([A-Z]+)/', (string) $c['r'], $m);
                $col = $this->colToIndex($m[1] ?? 'A');

                $t = (string) $c['t'];
                if ($t === 's') {
                    $v = $shared[(int) $c->v] ?? '';
                } elseif (isset($c->is->t)) {
                    $v = (string) $c->is->t;
                } else {
                    $v = (string) $c->v;
                }

                $line[$col] = $v;
            }
            $matrix[] = $line;
        }

        if (empty($matrix)) {
            return [];
        }

        $headers = array_map(fn ($h) => trim((string) $h), $matrix[0]);
        $out = [];

        foreach (array_slice($matrix, 1) as $line) {
            $a = [];
            foreach ($headers as $i => $h) {
                if ($h !== '') {
                    $a[$h] = $line[$i] ?? '';
                }
            }
            if (array_filter($a)) {
                $out[] = $a;
            }
        }

        return $out;
    }

    private function colToIndex(string $col): int
    {
        $n = 0;
        foreach (str_split($col) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return $n - 1;
    }
}
