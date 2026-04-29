<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'type',
        'branch_name',
        'account_number',
        'routing_number',
        'phone',
        'address',
        'total_investment',
    ];

    public function fdrs()
    {
        return $this->hasMany(Fdr::class);
    }

    // 🔥 Update investment automatically
    public function adjustInvestment(float $amount, string $type = 'add')
    {
        if ($type === 'add') {
            $this->total_investment += $amount;
        } else {
            $this->total_investment -= $amount;
        }

        $this->save();
    }

    // 🔥 Bank type check helpers
    public function isGovt(): bool
    {
        return $this->type === 'govt';
    }

    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }
}