<?php

namespace App\Services;

use App\Card;
use App\Enums\CardBox;
use App\Enums\ExportFormat;
use App\Enums\StoragePath;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Style\Table;

class ExportCardBox
{
    private Card $card;

    private string $box;

    private string $format;

    private string $filename;

    private string $file;

    public function __construct(Card $card, string $box, string $format)
    {
        $this->card = $card;
        $this->box = $box;
        $this->format = $format;
        $this->filename = $card->id.'.'.$format;
        $this->file = Storage::disk('public')
            ->path(StoragePath::ExportTemp.'/'.$this->filename);

        Storage::disk('public')
            ->makeDirectory(StoragePath::ExportTemp);
    }

    /**
     * Export the content of a card box
     *
     * @return string|null
     * @throws Exception
     */
    public function export()
    {
        $box = $this->box;
        $data = $this->card->$box;
        $exported = false;

        switch ($box) {
            case CardBox::Box2:
                $exported = $this->transcription($data);
                break;
        }

        if (! $exported) {
            return null;
        }

        return $this->file;
    }

    /**
     * Export the transcription content
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    private function transcription(array $data)
    {
        switch ($this->format) {
            case ExportFormat::Docx:
                return $this->transcriptionToDocx($data);
            default:
                return false;
        }
    }

    /**
     * Create a Word document with the transcription content
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    private function transcriptionToDocx(array $data)
    {
        $phpWord = $this->initPhpWord();

        $fontStyleName = 'transcription';
        $phpWord->addFontStyle(
            $fontStyleName,
            [
                'name' => 'Courier',
                'size' => 10,
                'color' => '000000',
                'bold' => false,
            ]
        );

        $section = $phpWord->addSection();

        $tableStyle = [
            'borderSize' => 0,
            'borderColor' => 'ffffff',
            'valign' => 'top',
            'alignment' => JcTable::START,
            'layout' => Table::LAYOUT_FIXED,
        ];

        $table = $section->addTable($tableStyle);
        foreach ($data['data'] as $row) {
            $table->addRow();

            $number = $row['number'] ? strval($row['number']) : '';
            $table->addCell(400)->addText($number, $fontStyleName);

            $speaker = $row['speaker'] ? $row['speaker'] : '';
            $table->addCell(500)->addText($speaker, $fontStyleName);

            $speech = $row['speech'] ? $row['speech'] : '';
            $cell = $table->addCell(8000);
            $lines = explode('<br />', $speech);
            foreach ($lines as $line) {
                $cell->addText($line, $fontStyleName);
            }
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($this->file);

        return true;
    }

    /**
     * Initialize an instance of PHPWord
     *
     * @return PhpWord
     */
    private function initPhpWord()
    {
        $phpWord = new PhpWord();

        switch (Helpers::currentLocal()) {
            case 'fr':
                $locale = Language::FR_FR;
                break;
            case 'en':
            default:
                $locale = Language::EN_US;
        }

        $phpWord->getSettings()->setThemeFontLang(
            new Language(
                $locale
            )
        );
        $phpWord->getDocInfo()->setCreator('Impact');
        $phpWord->getDocInfo()->setTitle($this->card->title);
        $phpWord->getDocInfo()->setDescription(trans('general.created_with_impact'));

        return $phpWord;
    }
}
