<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComment extends Model
{
    use HasFactory;

    protected $table = 'product_comments';
    protected $fillable = ['epno_part_id', 'user_comment', 'comment', 'user_answer', 'answer', 'status'];

    public function epnoPart()
    {
        return $this->belongsTo(EpnoPart::class);
    }
    public function userComment()
    {
        return $this->belongsTo(User::class, 'user_comment');
    }
    public function userAnswer()
    {
        return $this->belongsTo(User::class, 'user_answer');
    }
}
