<?php
namespace App\Services;
use App\Models\{KnowledgeEntry, Synonym};
use Illuminate\Support\Facades\{Cache, DB};

/** Pure-code matching: tokenise -> expand synonyms -> MySQL FULLTEXT/LIKE candidates -> programmed relevance score. */
class ChatService {
    const STOP = ['the','a','an','is','are','what','how','do','does','i','to','for','of','and','in','on','can','my','me','about','please','tell','there','you','we','it','be','get','need','want'];
    const MIN_SCORE = 5.0;

    public function tokens(string $q): array {
        $q = preg_replace('/[^a-z0-9\s]/', ' ', strtolower($q));
        return array_values(array_unique(array_diff(preg_split('/\s+/', trim($q)), self::STOP, [''])));
    }

    protected function groups(): array {
        return Cache::remember('synonym_groups', 3600, fn() => Synonym::all()->map(
            fn($s) => array_values(array_unique(array_filter(array_map('trim', array_merge([strtolower($s->word)], explode(',', strtolower($s->alternatives)))))))
        )->all());
    }

    public function expand(array $tokens): array {
        $out = $tokens;
        foreach ($this->groups() as $g) if (array_intersect($tokens, $g)) $out = array_merge($out, $g);
        return array_values(array_unique($out));
    }

    /** @return array{entry: ?KnowledgeEntry, score: float} */
    public function match(string $question): array {
        $tokens = $this->tokens($question);
        if (!$tokens) return ['entry' => null, 'score' => 0];
        $key = 'match:' . Cache::get('kb_v', 0) . ':' . md5(implode(' ', $tokens));
        $res = Cache::remember($key, 600, fn() => $this->compute($question, $tokens));
        return ['entry' => $res['id'] ? KnowledgeEntry::find($res['id']) : null, 'score' => $res['score']];
    }

    protected function compute(string $question, array $tokens): array {
        $exp = $this->expand($tokens);
        $base = KnowledgeEntry::where('is_approved', true);
        $terms = array_values(array_unique(array_filter(preg_split('/\s+/', implode(' ', $exp)), fn($t) => preg_match('/^[a-z0-9]+$/', $t))));
        $pg = DB::connection()->getDriverName() === 'pgsql';
        $cands = collect();
        if ($terms) {
            $cands = ($pg
                ? (clone $base)->whereRaw("to_tsvector('simple', title || ' ' || question || ' ' || coalesce(keywords, '')) @@ to_tsquery('simple', ?)",
                    [implode(' | ', array_map(fn($t) => $t . ':*', $terms))])
                : (clone $base)->whereRaw('MATCH(title,question,keywords) AGAINST (? IN BOOLEAN MODE)',
                    [implode(' ', array_map(fn($t) => $t . '*', $terms))])
            )->limit(25)->get();
        }
        if ($cands->isEmpty()) {
            $op = $pg ? 'ilike' : 'like';
            $cands = $base->where(function ($w) use ($exp, $op) {
                foreach ($exp as $t) $w->orWhere('keywords', $op, "%$t%")->orWhere('question', $op, "%$t%")->orWhere('title', $op, "%$t%");
            })->limit(25)->get();
        }
        $best = null; $bestScore = 0; $lq = strtolower($question);
        foreach ($cands as $e) {
            $q = strtolower($e->question . ' ' . $e->title); $k = strtolower($e->keywords); $a = strtolower($e->answer);
            $s = 0; $hits = 0;
            foreach ($tokens as $t) {                       // keyword matching (full weight)
                $w = (str_contains($q, $t) ? 3 : 0) + (str_contains($k, $t) ? 4 : 0) + (str_contains($a, $t) ? 1 : 0);
                if ($w) $hits++; $s += $w;
            }
            foreach (array_diff($exp, $tokens) as $t)       // synonym matches (half weight)
                if (str_contains($q, $t) || str_contains($k, $t)) { $s += 1.5; $hits++; }
            similar_text(implode(' ', $tokens), strtolower($e->question), $pct);
            $s += $pct / 100 * 10;                          // phrase / sentence similarity
            if (str_contains($lq, strtolower($e->question))) $s += 10;
            $s += min($e->hits, 50) / 50;                   // popularity tie-breaker
            if ($hits && $s > $bestScore) { $best = $e; $bestScore = $s; }
        }
        return $bestScore >= self::MIN_SCORE ? ['id' => $best->id, 'score' => round($bestScore, 2)] : ['id' => null, 'score' => round($bestScore, 2)];
    }
}
