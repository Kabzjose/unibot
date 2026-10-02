<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class KnowledgeEntry extends Model {
    protected $guarded = [];
    protected $casts = ['is_approved' => 'boolean'];
    public function category() { return $this->belongsTo(Category::class); }
}
