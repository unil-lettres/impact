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
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Style\Tab;

class ExportBoxService
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
     * @throws Exception
     */
    public function export(): ?string
    {
        $box = $this->box;
        $data = $this->card->$box;

        $exported = match ($box) {
            CardBox::Box2 => $this->transcription($data),
            default => false,
        };

        if (! $exported) {
            return null;
        }

        return $this->file;
    }

    /**
     * Export the transcription content
     *
     * @throws Exception
     */
    private function transcription(array $data): bool
    {
        return match ($this->format) {
            ExportFormat::Docx => $this->transcriptionToDocx($data),
            default => false,
        };
    }

    /**
     * Create a Word document with the transcription content
     *
     * @throws Exception
     */
    private function transcriptionToDocx(array $data): bool
    {
        $phpWord = $this->initPhpWord();

        $fontStyleName = 'impactFontStyle';
        $phpWord->addFontStyle(
            $fontStyleName,
            [
                'name' => 'Courier New',
                'size' => 10,
                'color' => '000000',
                'bold' => false,
            ]
        );

        $paragraphStyleName = 'impactParagraphStyle';
        $phpWord->addParagraphStyle(
            $paragraphStyleName,
            [
                'spaceAfter' => 0,
                'spaceBefore' => 0,
                // Set a negative withdrawal, needed to align the speaker
                // https://phpoffice.github.io/PHPWord/usage/styles/paragraph.html
                'indentation' => [
                    'left' => 1426,
                    'hanging' => 1426,
                ],
                // Set a custom tab stop, needed to align the speaker
                // https://phpoffice.github.io/PHPWord/usage/styles/paragraph.html
                'tabs' => [
                    new Tab('left', 713),
                ],
            ]
        );

        $section = $phpWord->addSection([
            // Set a custom left margin (approximately 1.18cm) to
            // allow a maximum of 65 characters for the speech.
            'marginLeft' => 1236,
        ]);

        foreach ($data['icor'] as $row) {
            $number = $row['number'] ? strval($row['number']) : '';
            $speaker = $row['speaker'] ?: '';
            $speech = $row['speech'] ?: '';

            $section->addText($number."\t".$speaker."\t".$speech, $fontStyleName, $paragraphStyleName);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($this->file);

        return true;
    }

    /**
     * Initialize an instance of PHPWord
     */
    private function initPhpWord(): PhpWord
    {
        $phpWord = new PhpWord();

        $locale = match (Helpers::currentLocal()) {
            'fr' => Language::FR_FR,
            default => Language::EN_US,
        };

        $phpWord->getSettings()->setThemeFontLang(
            new Language(
                $locale
            )
        );
        $phpWord->getDocInfo()->setCreator('Impact');
        $phpWord->getDocInfo()->setTitle($this->card->title);
        $phpWord->getDocInfo()->setDescription(trans('general.created_with_impact'));

        // Escape special characters to avoid XML parsing errors
        Settings::setOutputEscapingEnabled(true);

        return $phpWord;
    }
}
