<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishScore extends Model
{
    protected $fillable = [
        'fish_id',
        'judge_id',
        'minus_kepala',
        'minus_badan',
        'minus_dorsal',
        'minus_anal',
        'minus_ekor',
        'minus_dasi',
        'minus_kerapihan',
        'minus_warna',
        'minus_lain_lain',
        'total_minus',
        'total_score',
        'admin_note',
        'is_corrected',
        'kedokan_notes',
        'mental_notes',
        'proporsi_notes',
        'kepala_notes',
        'badan_notes',
        'dorsal_notes',
        'anal_notes',
        'ekor_notes',
        'dasi_notes',
        'warna_notes',
        'kerapihan_notes',
        'final_rank'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total_minus = (int) $model->minus_kepala +
                (int) $model->minus_badan +
                (int) $model->minus_dorsal +
                (int) $model->minus_anal +
                (int) $model->minus_ekor +
                (int) $model->minus_dasi +
                (int) $model->minus_kerapihan +
                (int) $model->minus_warna +
                (int) $model->minus_lain_lain;

            $model->total_score = 100 - $model->total_minus;
        });
    }

    public function fish(): BelongsTo
    {
        return $this->belongsTo(Fish::class);
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }
}
