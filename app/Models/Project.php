<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['title', 'description', 'created_by'];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function tasks() {
        return $this->hasMany(Task::class);
    }
}
