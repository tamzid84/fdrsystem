<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'doc_no',
        'template_id',
        'data',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'qr_code'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {

            // Auto Document Number
            if (!$model->doc_no) {
                $model->doc_no = 'DOC-' . now()->format('Y') . '-' . rand(100000, 999999);
            }

            // Auto Created By
            if (auth()->check() && !$model->created_by) {
                $model->created_by = auth()->id();
            }

            // 🔥 IMPORTANT: Fix JSON double encoding issue
            if (is_string($model->data)) {
                $decoded = json_decode($model->data, true);
                $model->data = is_array($decoded) ? $decoded : [];
            }
        });

        static::retrieved(function ($model) {
            // Ensure always array when reading
            if (is_string($model->data)) {
                $decoded = json_decode($model->data, true);
                $model->data = is_array($decoded) ? $decoded : [];
            }
        });
    }
}