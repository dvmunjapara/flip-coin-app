<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = ['invitee_id','bet','flip','result'];

    public function inviter() {

        return $this->belongsTo(User::class,'user_id');
    }
    public function invitee() {

        return $this->belongsTo(User::class);
    }
}
