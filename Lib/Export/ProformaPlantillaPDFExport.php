<?php
/*
 * This file is part of Impresos
 * Copyright (C) 2024  yevea
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Plugins\Impresos\Lib\Export;

use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Lib\Export\PDFExport;
use FacturaScripts\Dinamic\Model\Impuesto;

/**
 * Custom PDF export for PresupuestoCliente that integrates totals
 * (discount, net, VAT, total) as rows inside the line items table,
 * instead of showing them in the page footer.
 */
class ProformaPlantillaPDFExport extends PDFExport
{
    public function addBusinessDocPage($model): bool
    {
        if ($model->modelClassName() !== 'PresupuestoCliente') {
            return parent::addBusinessDocPage($model);
        }

        if (null === $this->format) {
            $this->format = $this->getDocumentFormat($model);
        }

        if ($this->pdf === null) {
            $this->newPage();
        } else {
            $this->pdf->ezNewPage();
            $this->insertedHeader = false;
        }

        $this->insertHeader($model->idempresa);
        $this->insertBusinessDocHeader($model);
        $this->insertBusinessDocBodyWithTotals($model);
        $this->insertBusinessDocFooterCustom($model);

        return false;
    }

    /**
     * Inserts lines with integrated totals at the end of the table.
     */
    protected function insertBusinessDocBodyWithTotals(BusinessDocument $model): void
    {
        $headers = [];
        $tableOptions = [
            'cols' => [],
            'shadeCol' => [0.95, 0.95, 0.95],
            'shadeHeadingCol' => [0.95, 0.95, 0.95],
            'width' => $this->tableWidth
        ];

        foreach ($this->getLineHeaders() as $key => $value) {
            $headers[$key] = $value['title'];
            if (in_array($value['type'], ['number', 'percentage'], true)) {
                $tableOptions['cols'][$key] = ['justification' => 'right'];
            }
        }

        $tableData = [];
        foreach ($model->getLines() as $line) {
            $data = [];
            foreach ($this->getLineHeaders() as $key => $value) {
                if (property_exists($line, 'mostrar_precio')
                    && $line->mostrar_precio === false
                    && in_array($key, ['pvpunitario', 'dtopor', 'dtopor2', 'pvptotal', 'iva', 'recargo', 'irpf'], true)) {
                    continue;
                }

                if ($key === 'referencia') {
                    $data[$key] = empty($line->{$key})
                        ? Tools::fixHtml($line->descripcion)
                        : Tools::fixHtml($line->{$key} . ' - ' . $line->descripcion);
                } elseif ($key === 'cantidad' && property_exists($line, 'mostrar_cantidad')) {
                    $data[$key] = $line->mostrar_cantidad ? $line->{$key} : '';
                } elseif ($value['type'] === 'percentage') {
                    $data[$key] = Tools::number($line->{$key}) . '%';
                } elseif ($value['type'] === 'number') {
                    $data[$key] = Tools::number($line->{$key});
                } else {
                    $data[$key] = $line->{$key};
                }
            }
            $tableData[] = $data;
        }

        // Add separator row
        $emptyRow = [];
        foreach (array_keys($headers) as $key) {
            $emptyRow[$key] = '';
        }
        $tableData[] = $emptyRow;

        // Add discount row if applicable
        $dtopor1 = $model->dtopor1 ?? 0;
        $dtopor2 = $model->dtopor2 ?? 0;
        if ($dtopor1 != 0 || $dtopor2 != 0) {
            $dtoText = '';
            if ($dtopor1 != 0) {
                $dtoText .= Tools::number($dtopor1) . '%';
            }
            if ($dtopor2 != 0) {
                $dtoText .= ($dtoText ? ' + ' : '') . Tools::number($dtopor2) . '%';
            }
            $dtoRow = $emptyRow;
            $dtoRow['referencia'] = $this->i18n->trans('global-dto') . ': ' . $dtoText;
            $tableData[] = $dtoRow;
        }

        // Add net row
        $netRow = $emptyRow;
        $netRow['referencia'] = $this->i18n->trans('net');
        $netRow['pvptotal'] = Tools::number($model->neto);
        $tableData[] = $netRow;

        // Add tax rows
        $eud = $model->getEUDiscount();
        $taxSubtotals = [];
        foreach ($model->getLines() as $line) {
            if (empty($line->codimpuesto) || empty($line->pvptotal)) {
                continue;
            }
            if (property_exists($line, 'suplido') && $line->suplido) {
                continue;
            }

            $key = $line->codimpuesto . '_' . $line->iva . '_' . $line->recargo;
            if (!isset($taxSubtotals[$key])) {
                $taxSubtotals[$key] = [
                    'codimpuesto' => $line->codimpuesto,
                    'iva' => $line->iva,
                    'recargo' => $line->recargo,
                    'totaliva' => 0,
                    'totalrecargo' => 0,
                ];
            }
            $taxSubtotals[$key]['totaliva'] += $line->pvptotal * $eud * $line->iva / 100;
            $taxSubtotals[$key]['totalrecargo'] += $line->pvptotal * $eud * $line->recargo / 100;
        }

        foreach ($taxSubtotals as $tax) {
            $impuesto = new Impuesto();
            $taxTitle = $impuesto->load($tax['codimpuesto'])
                ? $impuesto->descripcion
                : $this->i18n->trans('tax') . ' ' . $tax['iva'] . '%';

            $taxRow = $emptyRow;
            $taxRow['referencia'] = $taxTitle;
            $taxAmount = Tools::number($tax['totaliva']);
            if ($tax['totalrecargo'] != 0) {
                $taxAmount .= ' (' . $this->i18n->trans('re') . ' ' . $tax['recargo'] . '%: '
                    . Tools::number($tax['totalrecargo']) . ')';
            }
            $taxRow['pvptotal'] = $taxAmount;
            $tableData[] = $taxRow;
        }

        // Add IRPF row if applicable
        if ($model->totalirpf != 0) {
            $irpfRow = $emptyRow;
            $irpfRow['referencia'] = $this->i18n->trans('irpf') . ' ' . Tools::number($model->irpf) . '%';
            $irpfRow['pvptotal'] = Tools::number(0 - $model->totalirpf);
            $tableData[] = $irpfRow;
        }

        // Add total row
        $totalRow = $emptyRow;
        $totalRow['referencia'] = $this->i18n->trans('total');
        $totalRow['pvptotal'] = Tools::number($model->total);
        $tableData[] = $totalRow;

        $this->removeEmptyCols($tableData, $headers, Tools::number(0));
        $this->pdf->ezTable($tableData, $headers, '', $tableOptions);
    }

    /**
     * Custom footer: shows observations and payment method, but skips the
     * standard totals table since totals are already in the lines table.
     */
    protected function insertBusinessDocFooterCustom(BusinessDocument $model): void
    {
        if (!empty($model->observaciones)) {
            $this->newPage();
            $this->pdf->ezText(
                $this->i18n->trans('observations') . "\n",
                self::FONT_SIZE
            );
            $this->newLine();
            $this->pdf->ezText(Tools::fixHtml($model->observaciones) . "\n", self::FONT_SIZE);
        }

        if (property_exists($model, 'finoferta') && !empty($model->finoferta)) {
            $this->pdf->ezText(
                "\n" . $this->i18n->trans('expiration') . ': ' . $model->finoferta,
                self::FONT_SIZE
            );
        }

        if (isset($model->codcliente)) {
            $this->insertInvoicePayMethod($model);
        }

        if (!empty($this->format->texto)) {
            $this->pdf->ezText("\n" . Tools::fixHtml($this->format->texto), self::FONT_SIZE);
        }
    }
}
