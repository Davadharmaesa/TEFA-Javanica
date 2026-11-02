<?php

namespace App\Exports\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;

trait AddsExcelTable
{
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $worksheet = $event->sheet->getDelegate();
                $lastRow = $worksheet->getHighestRow();

                if ($lastRow < 2) {
                    return;
                }

                $table = new Table(
                    'A1:' . $worksheet->getHighestColumn() . $lastRow,
                    $this->tableName()
                );
                $table->setStyle(
                    new TableStyle(TableStyle::TABLE_STYLE_NONE)
                );

                $worksheet->addTable($table);

                $lastColumn = $worksheet->getHighestColumn();
                $worksheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF16803C'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                ]);
                $worksheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    abstract protected function tableName(): string;
}