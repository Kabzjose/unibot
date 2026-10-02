<?php
namespace App\Http\Controllers;
use App\Models\{ChatLog, KnowledgeEntry};
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller {
    public function ask(Request $r, ChatService $svc) {
        $data = $r->validate(['question' => 'required|string|max:500']);
        $m = $svc->match($data['question']);
        $entry = $m['entry'];
        $fallback = "Sorry, I couldn't find that in our official university information. Please contact the Student Affairs office, or try one of these:";
        $answer = $entry ? $entry->answer : $fallback;
        ChatLog::create(['question' => $data['question'], 'normalized' => mb_substr(implode(' ', $svc->tokens($data['question'])), 0, 190),
            'response' => $answer, 'entry_id' => $entry?->id, 'score' => $m['score'], 'answered' => (bool) $entry]);
        $entry?->increment('hits');
        $suggest = $entry ? [] : KnowledgeEntry::where('is_approved', true)->orderByDesc('hits')->limit(3)->pluck('question');
        return response()->json(['answer' => $answer, 'answered' => (bool) $entry, 'suggestions' => $suggest]);
    }
}
