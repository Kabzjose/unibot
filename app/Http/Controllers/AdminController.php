<?php
namespace App\Http\Controllers;
use App\Models\{Category, ChatLog, Document, KnowledgeEntry, Synonym};
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Storage};

class AdminController extends Controller {
    private function bust() { Cache::forever('kb_v', microtime(true)); Cache::forget('synonym_groups'); }

    // ---- Knowledge entries / FAQs ----
    public function entries(Request $r) {
        $q = KnowledgeEntry::with('category')->latest();
        if ($s = $r->search) $q->where(fn($x) => $x->where('question', 'like', "%$s%")->orWhere('keywords', 'like', "%$s%")->orWhere('answer', 'like', "%$s%"));
        if ($r->category_id) $q->where('category_id', $r->category_id);
        if ($r->filled('approved')) $q->where('is_approved', $r->approved);
        return $q->paginate(15);
    }
    private function rules() {
        return ['title' => 'required|max:255', 'question' => 'required|string', 'answer' => 'required|string', 'keywords' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id', 'is_approved' => 'boolean', 'log_id' => 'nullable|exists:chat_logs,id'];
    }
    private function save(Request $r, KnowledgeEntry $e) {
        $d = $r->validate($this->rules());
        $e->fill(collect($d)->except('log_id')->all())->save();
        if (!empty($d['log_id'])) { // convert unanswered question -> resolved
            ChatLog::where('normalized', ChatLog::find($d['log_id'])->normalized)->update(['resolved' => true]);
        }
        $this->bust(); return $e;
    }
    public function storeEntry(Request $r) { return $this->save($r, new KnowledgeEntry); }
    public function updateEntry(Request $r, KnowledgeEntry $entry) { return $this->save($r, $entry); }
    public function destroyEntry(KnowledgeEntry $entry) { $entry->delete(); $this->bust(); return ['ok' => true]; }

    // ---- Documents (PDF upload + text extraction into draft entries) ----
    public function documents() { return Document::latest()->get(); }
    public function uploadDocument(Request $r, ChatService $svc) {
        $r->validate(['title' => 'required|max:255', 'file' => 'required|file|mimes:pdf|max:20480']);
        $path = $r->file('file')->store('documents');
        $doc = Document::create(['title' => $r->title, 'path' => $path, 'status' => 'processed']);
        try {
            $text = (new \Smalot\PdfParser\Parser)->parseFile(Storage::path($path))->getText();
            $chunks = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', str_replace("\r", '', $text))), fn($c) => mb_strlen($c) > 40));
            if (count($chunks) < 3) $chunks = array_filter(array_map('trim', mb_str_split(trim($text), 800)));
            foreach ($chunks as $c) {
                $c = mb_substr($c, 0, 1500);
                $first = trim(strtok($c, "\n")); $title = mb_substr($first, 0, 80);
                $freq = array_count_values(array_filter($svc->tokens($c), fn($w) => strlen($w) > 3)); arsort($freq);
                KnowledgeEntry::create(['document_id' => $doc->id, 'title' => $title, 'question' => $title, 'answer' => $c,
                    'keywords' => implode(',', array_slice(array_keys($freq), 0, 8)), 'is_approved' => false]); // drafts: admin must review & approve
            }
            $doc->update(['entries_count' => count($chunks)]);
        } catch (\Throwable $e) { $doc->update(['status' => 'failed']); }
        $this->bust(); return $doc;
    }
    public function destroyDocument(Document $document) { Storage::delete($document->path); $document->delete(); return ['ok' => true]; }

    // ---- Categories & synonyms ----
    public function categories() { return Category::orderBy('name')->get(); }
    public function storeCategory(Request $r) { return Category::create($r->validate(['name' => 'required|max:100|unique:categories'])); }
    public function synonyms() { return Synonym::orderBy('word')->get(); }
    public function storeSynonym(Request $r) {
        $s = Synonym::create($r->validate(['word' => 'required|max:100|unique:synonyms', 'alternatives' => 'required'])); $this->bust(); return $s;
    }
    public function destroySynonym(Synonym $synonym) { $synonym->delete(); $this->bust(); return ['ok' => true]; }

    // ---- Questions, history, analytics ----
    public function unanswered() {
        return ChatLog::where('answered', false)->where('resolved', false)
            ->selectRaw('MIN(id) as id, MAX(question) as question, COUNT(*) as c')->groupBy('normalized')->orderByDesc('c')->limit(100)->get();
    }
    public function logs(Request $r) {
        $q = ChatLog::latest();
        if ($r->filled('answered')) $q->where('answered', $r->answered);
        if ($s = $r->search) $q->where('question', 'like', "%$s%");
        return $q->paginate(20);
    }
    public function analytics() {
        return [
            'total' => ChatLog::count(), 'answered' => ChatLog::where('answered', true)->count(),
            'unanswered' => ChatLog::where('answered', false)->where('resolved', false)->count(),
            'top_entries' => KnowledgeEntry::where('hits', '>', 0)->orderByDesc('hits')->limit(8)->get(['title', 'hits']),
            'top_categories' => KnowledgeEntry::join('categories', 'categories.id', '=', 'knowledge_entries.category_id')
                ->selectRaw('categories.name, SUM(hits) as hits')->groupBy('categories.name')->orderByDesc('hits')->limit(8)->get(),
            'frequent' => ChatLog::selectRaw('MAX(question) as question, COUNT(*) as c')->groupBy('normalized')->orderByDesc('c')->limit(10)->get(),
            'daily' => ChatLog::selectRaw('DATE(created_at) as d, SUM(answered) as a, COUNT(*) as t')->where('created_at', '>=', now()->subDays(14))->groupBy('d')->orderBy('d')->get(),
        ];
    }
}
