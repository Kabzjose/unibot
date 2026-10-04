<?php
namespace Database\Seeders;

use App\Models\{Category, KnowledgeEntry, Synonym};
use App\Services\ChatService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Loads the MUT question list as DRAFT entries (hidden from students).
 * Fill in each real answer in the admin dashboard, then tick "Approved".
 * Safe to run more than once: existing questions are skipped.
 */
class QuestionSeeder extends Seeder {
    // category => extra keywords added to every question in that category
    private array $cats = [
        'Admissions & Applications' => 'admission,admitted,apply,application,intake,kuccps,joining,mut,murang a',
        'Courses & Programmes'      => 'course,courses,programme,programmes,degree,diploma,certificate,masters,postgraduate',
        'Fees & Finance'            => 'fees,fee,pay,payment,balance,mpesa,paybill,helb,scholarship,bursary',
        'Accommodation & Hostels'   => 'hostel,hostels,accommodation,housing,room,residence',
        'Registration & Academic Portal' => 'portal,register,registration,units,password,timetable,results,transcript',
        'Examinations'              => 'exam,exams,examination,timetable,supplementary,special,remark,retake,fail',
    ];

    private string $data = <<<'TXT'
Admissions & Applications|How do I apply to Murang'a University?
Admissions & Applications|What are the requirements for joining MUT?
Admissions & Applications|When does the next intake start?
Admissions & Applications|Is MUT currently accepting applications?
Admissions & Applications|How much is the application fee?
Admissions & Applications|Can I apply online?
Admissions & Applications|How do I check my admission status?
Admissions & Applications|I have been admitted but I haven't received my admission letter. What should I do?
Admissions & Applications|Can I change the course I was admitted for?
Admissions & Applications|Can I defer my admission?
Admissions & Applications|Does MUT accept KUCCPS students?
Admissions & Applications|How do I apply through KUCCPS?
Admissions & Applications|Can I join MUT as a self-sponsored student?
Admissions & Applications|What are the requirements for international students?
Admissions & Applications|Can I transfer from another university to MUT?
Admissions & Applications|What documents do I need when reporting?
Admissions & Applications|Where do I report when I arrive at the university?
Admissions & Applications|I lost my admission letter. How can I get another one?
Courses & Programmes|What courses does MUT offer?
Courses & Programmes|Does MUT offer Software Engineering?
Courses & Programmes|What are the requirements for Software Engineering?
Courses & Programmes|How long does a Bachelor's degree take?
Courses & Programmes|Does MUT offer diploma courses?
Courses & Programmes|Does MUT offer certificate courses?
Courses & Programmes|What engineering courses are available?
Courses & Programmes|What computer science courses are available?
Courses & Programmes|What business courses does MUT offer?
Courses & Programmes|Can I change my course after joining?
Courses & Programmes|Which courses are offered at the main campus?
Courses & Programmes|Does MUT offer evening classes?
Courses & Programmes|Does MUT offer part-time programmes?
Courses & Programmes|Are there online programmes?
Courses & Programmes|What postgraduate programmes does MUT offer?
Courses & Programmes|Can I do a master's degree at MUT after completing my bachelor's?
Courses & Programmes|What are the entry requirements for a master's programme?
Fees & Finance|How much are the fees per semester?
Fees & Finance|How do I pay my school fees?
Fees & Finance|What is MUT's paybill number?
Fees & Finance|Can I pay fees through M-Pesa?
Fees & Finance|Can I pay fees in installments?
Fees & Finance|How do I check my fee balance?
Fees & Finance|Why has my fee balance not updated after payment?
Fees & Finance|I paid my fees but the system still shows a balance. What should I do?
Fees & Finance|Where can I get my fee statement?
Fees & Finance|What happens if I haven't cleared my fees?
Fees & Finance|Can I register for units before paying all my fees?
Fees & Finance|Does MUT offer scholarships?
Fees & Finance|How do I apply for a scholarship?
Fees & Finance|Does HELB cover students at MUT?
Fees & Finance|What happens if I cannot afford my fees this semester?
Accommodation & Hostels|Does MUT have student hostels?
Accommodation & Hostels|How much does accommodation cost?
Accommodation & Hostels|How do I apply for a hostel?
Accommodation & Hostels|Are first-year students given priority for hostels?
Accommodation & Hostels|Are there hostels near MUT?
Accommodation & Hostels|How far are the private hostels from the university?
Accommodation & Hostels|What facilities are available in the hostels?
Accommodation & Hostels|Can I choose my roommate?
Accommodation & Hostels|When should I apply for accommodation?
Accommodation & Hostels|What happens if I don't get a university hostel?
Registration & Academic Portal|How do I log into the student portal?
Registration & Academic Portal|I forgot my student portal password. What should I do?
Registration & Academic Portal|How do I register for units?
Registration & Academic Portal|How do I check my registered units?
Registration & Academic Portal|How do I check my exam timetable?
Registration & Academic Portal|Where can I find my class timetable?
Registration & Academic Portal|How do I check my results?
Registration & Academic Portal|My results are missing from the portal. What should I do?
Registration & Academic Portal|How do I download my transcript?
Registration & Academic Portal|How do I print my fee statement?
Registration & Academic Portal|Why can't I register for a unit?
Registration & Academic Portal|The student portal is not working. What should I do?
Registration & Academic Portal|How do I update my personal information?
Registration & Academic Portal|How do I know whether I have completed my unit registration?
Examinations|When are the exams?
Examinations|Where can I find the exam timetable?
Examinations|What happens if I miss an exam?
Examinations|Can I apply for a special exam?
Examinations|What happens if I fail a unit?
Examinations|How do supplementary exams work?
Examinations|How do I apply for a remark?
TXT;

    private array $synonyms = [
        'admission'   => 'admitted,admit,enrolment,enrollment,apply,application,join,joining',
        'fees'        => 'fee,tuition,payment,pay,charges,cost,school fees',
        'hostel'      => 'hostels,accommodation,housing,residence,hall',
        'course'      => 'courses,programme,programmes,program,programs,degree',
        'exam'        => 'exams,examination,examinations,cat,test',
        'results'     => 'result,marks,grades,grade,transcript',
        'scholarship' => 'bursary,sponsorship,helb,loan',
        'portal'      => 'student portal,system,website',
    ];

    public function run(ChatService $svc): void {
        $ids = collect($this->cats)->mapWithKeys(fn($kw, $name) => [$name => Category::firstOrCreate(['name' => $name])->id]);
        $added = 0;
        foreach (explode("\n", trim($this->data)) as $line) {
            [$cat, $q] = array_map('trim', explode('|', $line, 2));
            if (KnowledgeEntry::where('question', $q)->exists()) continue;
            $words = array_filter($svc->tokens($q), fn($w) => strlen($w) > 2);
            KnowledgeEntry::create([
                'category_id' => $ids[$cat], 'title' => $q, 'question' => $q,
                'answer'      => 'Answer not yet provided. Please contact the relevant MUT office.',
                'keywords'    => implode(',', array_unique(array_merge($words, explode(',', $this->cats[$cat])))),
                'is_approved' => false,
            ]);
            $added++;
        }
        foreach ($this->synonyms as $word => $alts) Synonym::firstOrCreate(['word' => $word], ['alternatives' => $alts]);
        Cache::forever('kb_v', microtime(true)); Cache::forget('synonym_groups');
        $this->command?->info("Added $added draft entries.");
    }
}
