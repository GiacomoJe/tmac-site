<?php

namespace App\Services\SalesTable;

use App\Models\SalesCatalogItem;
use App\Models\SalesOrder;
use App\Models\SalesPriceList;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Gera os dois arquivos .xlsx que a Área do Cliente oferece — equivalentes aos
 * botões "BAIXAR TABELA" e "SALVAR PEDIDO" do app antigo:
 *
 *  - exportTabela(): a tabela em branco da tabela/estado escolhido, com fórmulas
 *    de VALOR FINAL / TOTAL prontas para o cliente preencher no Excel.
 *  - exportPedido(): o pedido já montado (itens com quantidade > 0) que o
 *    cliente enviou pela tela — mesmo conteúdo que fica salvo em sales_orders.
 */
class SalesTableExporter
{
    public function exportTabela(SalesPriceList $priceList, string $tabela): StreamedResponse
    {
        $itens = SalesCatalogItem::query()
            ->where('sales_price_list_id', $priceList->id)
            ->forTabela($tabela)
            ->orderBy('descricao')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tabela '.mb_substr($tabela, 0, 20));

        $sheet->setCellValue('A1', 'TABELA DE VENDAS - TMAC');
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A2', 'Tabela: '.$tabela);
        $sheet->setCellValue('E2', 'Gerada em: '.now()->format('d/m/Y H:i'));
        $sheet->setCellValue('A3', 'Itens de PROMOÇÃO e PROMOÇÃO EXTRA têm preço final e não aceitam desconto.');
        $sheet->mergeCells('A3:N3');
        $sheet->setCellValue('A4', 'Itens SEM ESTOQUE ou FORA DE LINHA não entram no total do pedido.');
        $sheet->mergeCells('A4:N4');

        $headerRow = 6;
        $headers = ['COD', 'DESCRIÇÃO', 'MARCA', 'GRUPO', 'VALOR TABELA', 'CX. MASTER', 'SUB-EMB.',
            'TAG', 'STATUS', 'DESCONTO', 'QNT', 'VALOR FINAL', 'TOTAL', 'ENTRA NO TOTAL?'];
        foreach ($headers as $i => $label) {
            $sheet->setCellValueByColumnAndRow($i + 1, $headerRow, $label);
        }

        $r = $headerRow + 1;
        foreach ($itens as $item) {
            $sheet->setCellValue("A{$r}", $item->cod);
            $sheet->setCellValue("B{$r}", $item->descricao);
            $sheet->setCellValue("C{$r}", $item->marca);
            $sheet->setCellValue("D{$r}", $item->grupo);
            $sheet->setCellValue("E{$r}", (float) $item->preco);
            $sheet->setCellValue("F{$r}", $item->caixa_master);
            $sheet->setCellValue("G{$r}", $item->sub_embalagem);
            $sheet->setCellValue("H{$r}", $item->promo ?: $item->tag);
            $sheet->setCellValue("I{$r}", $item->status);
            $sheet->setCellValue("J{$r}", 0);
            $sheet->setCellValue("K{$r}", 0);

            if ($item->promo) {
                $sheet->setCellValue("L{$r}", "=E{$r}");
            } else {
                $sheet->setCellValue("L{$r}", "=E{$r}*(1-J{$r}/100)");
            }
            $sheet->setCellValue("M{$r}", "=L{$r}*K{$r}");
            $sheet->setCellValue("N{$r}", $item->bloqueado ? 0 : 1);

            $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('R$ #,##0.00');
            $sheet->getStyle("L{$r}")->getNumberFormat()->setFormatCode('R$ #,##0.00');
            $sheet->getStyle("M{$r}")->getNumberFormat()->setFormatCode('R$ #,##0.00');
            $sheet->getStyle("J{$r}")->getNumberFormat()->setFormatCode('0"%"');

            $r++;
        }
        $ultima = $r - 1;

        $totalRow = $ultima + 2;
        $sheet->setCellValue("K{$totalRow}", 'TOTAL DO PEDIDO');
        if ($ultima >= $headerRow + 1) {
            $primeira = $headerRow + 1;
            $sheet->setCellValue("M{$totalRow}", "=SUMIF(N{$primeira}:N{$ultima},1,M{$primeira}:M{$ultima})");
        }
        $sheet->getStyle("M{$totalRow}")->getNumberFormat()->setFormatCode('R$ #,##0.00');

        foreach (['A' => 13, 'B' => 60, 'C' => 12, 'D' => 16, 'E' => 14, 'F' => 12, 'G' => 11,
            'H' => 17, 'I' => 15, 'J' => 11, 'K' => 9, 'L' => 14, 'M' => 15, 'N' => 16] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        if ($ultima >= $headerRow + 1) {
            $sheet->setAutoFilter("A{$headerRow}:N{$ultima}");
        }

        $filename = 'Tabela TMAC '.$this->slug($tabela).' - '.now()->format('Y-m-d').'.xlsx';

        return $this->stream($spreadsheet, $filename);
    }

    public function exportPedido(SalesOrder $order): StreamedResponse
    {
        $order->loadMissing('items');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pedido');

        $sheet->setCellValue('A1', 'PEDIDO DE VENDA - TMAC');
        $sheet->mergeCells('A1:E1');

        $rows = [
            ['CNPJ:', $order->cnpj, '', 'CNPJ TRANSP.:', $order->cnpj_transportadora],
            ['RAZÃO SOCIAL:', $order->razao_social, '', 'TRANSPORTADORA:', $order->transportadora],
            ['RESPONSÁVEL:', $order->responsavel, '', 'VENDEDOR:', $order->vendedor],
            ['TEL./ CEL.:', $order->telefone, '', 'OBSERVAÇÕES:', $order->observacoes],
            ['E-MAIL:', $order->email, '', 'PRAZO PGT:', $order->prazo_pagamento],
            ['TABELA:', $order->tabela, '', 'DATA:', $order->created_at->format('d/m/Y H:i')],
        ];
        $r = 3;
        foreach ($rows as $row) {
            $sheet->fromArray($row, null, "A{$r}");
            $r++;
        }
        $r++; // linha em branco

        $headerRow = $r;
        $sheet->fromArray(['COD', 'DESCRIÇÃO', 'QUANTIDADE', 'VALOR FINAL', 'TOTAL'], null, "A{$headerRow}");
        $r++;

        foreach ($order->items as $item) {
            $sheet->setCellValue("A{$r}", $item->cod);
            $sheet->setCellValue("B{$r}", $item->descricao);
            $sheet->setCellValue("C{$r}", (int) $item->quantidade);
            $sheet->setCellValue("D{$r}", (float) $item->valor_final);
            $sheet->setCellValue("E{$r}", (float) $item->total);
            $sheet->getStyle("D{$r}")->getNumberFormat()->setFormatCode('R$ #,##0.00');
            $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('R$ #,##0.00');
            $r++;
        }
        $r++;
        $sheet->setCellValue("D{$r}", 'TOTAL DO PEDIDO');
        $sheet->setCellValue("E{$r}", (float) $order->total);
        $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode('R$ #,##0.00');

        foreach (['A' => 14, 'B' => 82, 'C' => 12, 'D' => 14, 'E' => 14] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $filename = 'Pedido '.$this->slug($order->razao_social ?: 'sem cliente').' - '.now()->format('Y-m-d Hi\h').'.xlsx';

        return $this->stream($spreadsheet, $filename);
    }

    private function stream(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function slug(string $value): string
    {
        $value = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $value);

        return trim($value);
    }
}
