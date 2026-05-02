<?php

namespace App\Services\Document;

use App\Models\Document;
use PhpOffice\PhpWord\PhpWord;

class DocumentExportService
{
    public function word(Document $doc)
    {
        $engine = app(EnterpriseDocumentV4::class);

        // 🔥 Get rendered content from your existing engine
        $content = $engine->render($doc);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        /* ================= HEADER ================= */
        $section->addText("সিলেট পল্লী বিদ্যুৎ সমিতি-২");
        $section->addText("দরবস্ত, জৈন্তাপুর, সিলেট।");
        $section->addText("তারিখঃ " . date('d/m/Y') . " ইং");

        $section->addTextBreak(1);

        /* ================= MAIN CONTENT ================= */
        $section->addText($content);

        $section->addTextBreak(1);

        /* ================= SIGNATURE TABLE (NO BORDER) ================= */
        $table = $section->addTable([
            'borderSize' => 0, // ❗ NO BORDER
        ]);

        $rows = [
            ['মোহাম্মদ হাবিবুল্লাহ ভূঞা', 'সহকারী হিসাব রক্ষক', 'সিলেট পল্লী বিদ্যুৎ সমিতি-২'],
            ['কামরুন নাহার', 'হিসাব রক্ষক', 'সিলেট পল্লী বিদ্যুৎ সমিতি-২'],
            ['তুহিন রহমান', 'এজিএম (অর্থ-হিসাব)', 'সিলেট পল্লী বিদ্যুৎ সমিতি-২'],
        ];

        foreach ($rows as $row) {
            $table->addRow();

            foreach ($row as $cell) {
                $table->addCell(3000)->addText($cell);
            }
        }

        /* ================= FILE SAVE ================= */
        $file = storage_path("app/public/{$doc->doc_no}.docx");

        $phpWord->save($file, 'Word2007');

        return response()->download($file)->deleteFileAfterSend(true);
    }
    public static function mutateFormDataBeforeCreate(array $data): array
{
    $data['created_by'] = auth()->id();

    return $data;
}
}