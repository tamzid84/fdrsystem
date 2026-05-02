<?php
namespace App\Services\Document;

use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Support\Str;

class EnterpriseDocumentV4
{
    /* ================= CREATE DOCUMENT ================= */
    public function create(string $templateCode, array $data, int $userId)
    {
        $template = DocumentTemplate::where('code', $templateCode)->firstOrFail();

        return Document::create([
            'doc_no' => 'DOC-' . now()->format('Y') . '-' . Str::upper(Str::random(6)),
            'template_id' => $template->id,
            'data' => $data,
            'status' => 'draft',
            'created_by' => $userId,
        ]);
    }

    /* ================= APPROVE ================= */
    public function approve(Document $doc, int $userId)
    {
        $doc->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $doc;
    }

    /* ================= ISSUE ================= */
    public function issue(Document $doc)
    {
        $doc->update([
            'status' => 'issued',
            'qr_code' => $this->generateQR($doc),
        ]);

        return $doc;
    }

    /* ================= QR ================= */
    private function generateQR(Document $doc)
    {
        return hash('sha256', $doc->doc_no . $doc->id);
    }

    /* ================= RENDER WORD CONTENT ================= */
    public function render(Document $doc)
    {
        $content = $doc->template->content;

        foreach ($doc->data as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }
}