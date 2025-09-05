<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPackageMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'leader_id',
        'team_package_id',
        'member_id',
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function package()
    {
        return $this->belongsTo(TeamTrainingPackage::class, 'team_package_id');
    }
}
