<?php
namespace Database\Seeders;

use App\Models\{Category, KnowledgeEntry, Synonym};
use App\Services\ChatService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * MUT questions + answers taken from https://mut.ac.ke (checked 3 Oct 2026).
 * Status per entry:  A = approved (live)   D = answer filled, kept as DRAFT for you to verify   P = no answer found yet (placeholder draft)
 * Safe to re-run: updates existing questions; never overwrites an existing answer with a placeholder.
 */
class MutKnowledgeSeeder extends Seeder {
    private array $cats = [
        'Admissions & Applications'      => 'admission,admitted,apply,application,intake,kuccps,joining,mut,murang a',
        'Courses & Programmes'           => 'course,courses,programme,programmes,degree,diploma,certificate,masters,postgraduate',
        'Fees & Finance'                 => 'fees,fee,pay,payment,balance,mpesa,paybill,helb,hef,scholarship,bursary',
        'Accommodation & Hostels'        => 'hostel,hostels,accommodation,housing,room,residence',
        'Registration & Academic Portal' => 'portal,register,registration,units,password,timetable,results,transcript',
        'Examinations'                   => 'exam,exams,examination,timetable,supplementary,special,remark,retake,fail',
    ];

    private array $synonyms = [
        'admission' => 'admitted,admit,enrolment,enrollment,apply,application,join,joining',
        'fees' => 'fee,tuition,payment,pay,charges,cost,school fees',
        'hostel' => 'hostels,accommodation,housing,residence,hall',
        'course' => 'courses,programme,programmes,program,programs,degree',
        'exam' => 'exams,examination,examinations,cat,test',
        'results' => 'result,marks,grades,grade,transcript',
        'scholarship' => 'bursary,sponsorship,helb,hef,loan',
        'portal' => 'student portal,system,website',
    ];

    private string $data = <<<'TXT'
@@ A|Admissions & Applications|How do I apply to Murang'a University?
You can apply online at https://admissions.mut.ac.ke. Application forms are also available from the Office of the Registrar (Academic and Student Affairs) or can be downloaded at https://mut.ac.ke/application-forms/.
Submit the filled form with copies of your academic and professional certificates, one passport-size photo, a copy of your National ID or birth certificate, and the original application fee deposit slip. Send it to the Registrar (ASA) office, email admissions@mut.ac.ke, or post it to: The Registrar (ASA), Murang'a University of Technology, P.O. Box 75-10200, Murang'a.
For online (ODeL) programmes, apply through https://odel.mut.ac.ke. Questions: 0705 939 269.
@@ A|Admissions & Applications|What are the requirements for joining MUT?
Minimum entry requirements depend on the programme. See the official list of course admission requirements at https://mut.ac.ke/courses-minimum-admission-requirements/ (it has one document for degree programmes and one for TVET programmes). For help choosing a course, call 0705 939 269 or email admissions@mut.ac.ke.
@@ D|Admissions & Applications|When does the next intake start?
MUT admits students in January, May and September. The university website currently lists the September 2026 intake as ongoing. Check https://admissions.mut.ac.ke or call 0705 939 269 for the next intake date.
@@ D|Admissions & Applications|Is MUT currently accepting applications?
Yes. The university website currently lists the September 2026 intake as ongoing. Apply at https://admissions.mut.ac.ke or call 0705 939 269 to confirm the closing date.
@@ D|Admissions & Applications|How much is the application fee?
The non-refundable application fee is Kshs 500 for certificate courses, Kshs 1,000 for diploma courses, Kshs 1,500 for undergraduate courses and Kshs 2,000 for postgraduate programmes.
Pay by cash deposit or bankers cheque into: KCB (Murang'a or any KCB branch) account 1107198356, or Equity Bank (Murang'a or any Equity branch) account 0220273636188. Attach the original deposit slip to your application.
@@ A|Admissions & Applications|Can I apply online?
Yes. Apply online at https://admissions.mut.ac.ke. For online (ODeL) programmes use https://odel.mut.ac.ke. Paper application forms are also available at https://mut.ac.ke/application-forms/.
@@ A|Admissions & Applications|How do I check my admission status?
Log in to the admissions portal at https://admissions.mut.ac.ke. If you have been admitted you can download your admission letter and update your details there. For help call 0705 939 269 or email admissions@mut.ac.ke (Monday to Friday, 8am to 5pm).
@@ A|Admissions & Applications|I have been admitted but I haven't received my admission letter. What should I do?
Admission letters are downloaded from the admissions portal at https://admissions.mut.ac.ke; they are not posted. If your letter is not there, contact the admissions office on 0705 939 269 or admissions@mut.ac.ke (Monday to Friday, 8am to 5pm).
@@ D|Admissions & Applications|Can I change the course I was admitted for?
First-year students can request a course transfer using the Course Transfer/Change form. Find the notice and forms at https://mut.ac.ke/notice-to-first-year-students/. Approval is decided by the university, so contact the Registrar (Academic and Student Affairs) on 0705 939 269 before you apply.
@@ A|Admissions & Applications|Can I defer my admission?
Yes. Apply for deferment or academic leave through the student portal at https://studentportal.mut.ac.ke (Defer/Academic Leave application). If the reason is sickness or bereavement, attach a photocopy of the supporting documents.
@@ A|Admissions & Applications|Does MUT accept KUCCPS students?
Yes. KUCCPS admission letters and the Household Component of Fee letters can be downloaded from the admissions portal at https://admissions.mut.ac.ke.
@@ P|Admissions & Applications|How do I apply through KUCCPS?
@@ D|Admissions & Applications|Can I join MUT as a self-sponsored student?
Yes. MUT has self-sponsored (SSP) programmes, with separate fee structures at https://mut.ac.ke/fees-structure/. Apply through https://admissions.mut.ac.ke or the application procedure at https://mut.ac.ke/how-to-apply/. Call 0705 939 269 to confirm which programmes are open.
@@ P|Admissions & Applications|What are the requirements for international students?
@@ D|Admissions & Applications|Can I transfer from another university to MUT?
MUT has an "Application for Credit Accumulation and Credit Transfer" form: https://mut.ac.ke/wp-content/uploads/2023/01/APPLICATION-FOR-CREDIT-ACCUMULATION-AND-CREDIT-TRANSFER.pdf. Contact the Registrar (Academic and Student Affairs) on 0705 939 269 or admissions@mut.ac.ke for the transfer conditions.
@@ A|Admissions & Applications|What documents do I need when reporting?
Download, print and fill in the admission documents and bring them on registration day: https://mut.ac.ke/wp-content/uploads/2023/08/ADMISSION-DOWNLOADS.pdf. Also read the Joining Instructions: https://mut.ac.ke/wp-content/uploads/2023/08/JOINING-INSTRUCTIONS.pdf.
Also fill in the MUT Student Data Collection Form, and the hostel application form if you need university accommodation. BSc Medical Laboratory Sciences, Environmental Health and Community Health students have extra requirements (https://mut.ac.ke/wp-content/uploads/2026/08/ADMISSION-REQUIREMENTS.pdf). Engineering students must bring instruments and materials (https://mut.ac.ke/wp-content/uploads/2026/08/ENGINEERING-STUD-REQUIREMENTS.pdf).
@@ D|Admissions & Applications|Where do I report when I arrive at the university?
Report to Murang'a University of Technology on the date set for your school. For 2026/2027 Semester I, the dates were 1 September 2026 (Hospitality & Tourism, Agriculture & Environmental Sciences, Computing & IT, Pure Applied & Health Sciences, Engineering & Technology) and 2 September 2026 (Business & Economics, Education, Humanities & Social Sciences). See https://mut.ac.ke/reporting-and-semester-dates/ and the Joining Instructions. Call 0705 939 269 for the registration venue.
@@ A|Admissions & Applications|I lost my admission letter. How can I get another one?
Download it again from the admissions portal at https://admissions.mut.ac.ke. If you cannot find it, contact the admissions office on 0705 939 269 or admissions@mut.ac.ke (Monday to Friday, 8am to 5pm).
@@ A|Courses & Programmes|What courses does MUT offer?
MUT offers certificate, diploma (TVET), bachelor's, master's and PhD programmes across nine schools: Computing & Information Technology; Pure and Applied Sciences; Business & Economics; Agriculture & Environmental Sciences; Engineering & Technology; Hospitality & Tourism Management; Education; Humanities & Social Sciences; Health Sciences.
Full lists: all programmes with duration https://mut.ac.ke/wp-content/uploads/2025/09/ALL-Academic-Programmes-2025-Duration.pdf, undergraduate https://mut.ac.ke/wp-content/uploads/2024/04/Undergraduate-Programmes-1.pdf, postgraduate https://mut.ac.ke/postgraduate-programmes/, TVET https://mut.ac.ke/wp-content/uploads/2024/04/MUT-TVET-programmes.pdf.
@@ D|Courses & Programmes|Does MUT offer Software Engineering?
Computing programmes are offered by the School of Computing and Information Technology (https://scit.mut.ac.ke). Check the full list at https://mut.ac.ke/wp-content/uploads/2024/04/Undergraduate-Programmes-1.pdf to confirm Software Engineering and its intake.
@@ P|Courses & Programmes|What are the requirements for Software Engineering?
@@ D|Courses & Programmes|How long does a Bachelor's degree take?
The duration of each programme is listed in the official document: https://mut.ac.ke/wp-content/uploads/2025/09/ALL-Academic-Programmes-2025-Duration.pdf.
@@ A|Courses & Programmes|Does MUT offer diploma courses?
Yes. MUT offers TVET diploma programmes. See the list at https://mut.ac.ke/wp-content/uploads/2024/04/MUT-TVET-programmes.pdf and the entry requirements at https://mut.ac.ke/wp-content/uploads/2025/01/TVET-Programmes.pdf. The application fee for diploma courses is Kshs 1,000.
@@ A|Courses & Programmes|Does MUT offer certificate courses?
Yes. MUT offers certificate courses; see the TVET programmes list at https://mut.ac.ke/wp-content/uploads/2024/04/MUT-TVET-programmes.pdf. Short courses are also offered by the Schools of Business and Hospitality: https://mut.ac.ke/school-of-business-short-courses/ and https://mut.ac.ke/school-of-hospitality-short-courses/.
@@ D|Courses & Programmes|What engineering courses are available?
Engineering programmes are offered by the School of Engineering and Technology (https://set.mut.ac.ke). See the full list at https://mut.ac.ke/wp-content/uploads/2024/04/Undergraduate-Programmes-1.pdf.
@@ D|Courses & Programmes|What computer science courses are available?
Computing programmes are offered by the School of Computing and Information Technology (https://scit.mut.ac.ke). See the full list at https://mut.ac.ke/wp-content/uploads/2024/04/Undergraduate-Programmes-1.pdf.
@@ D|Courses & Programmes|What business courses does MUT offer?
Business programmes are offered by the School of Business and Economics (https://sbe.mut.ac.ke). Short business courses are listed at https://mut.ac.ke/school-of-business-short-courses/. See the full list at https://mut.ac.ke/wp-content/uploads/2024/04/Undergraduate-Programmes-1.pdf.
@@ D|Courses & Programmes|Can I change my course after joining?
First-year students can request a course transfer using the Course Transfer/Change form: https://mut.ac.ke/notice-to-first-year-students/. Approval is decided by the university; contact the Registrar (Academic and Student Affairs) on 0705 939 269.
@@ P|Courses & Programmes|Which courses are offered at the main campus?
@@ P|Courses & Programmes|Does MUT offer evening classes?
@@ P|Courses & Programmes|Does MUT offer part-time programmes?
@@ A|Courses & Programmes|Are there online programmes?
Yes. MUT offers online programmes through the Directorate of Open, Distance and e-Learning (ODeL). See https://odel.mut.ac.ke/soc.html and apply at https://odel.mut.ac.ke.
@@ A|Courses & Programmes|What postgraduate programmes does MUT offer?
MUT offers Master's and PhD programmes across its schools, and postgraduate diplomas in Education and in Engineering & Technology. See https://mut.ac.ke/postgraduate-programmes/ or contact the Directorate of Postgraduate Studies: https://mut.ac.ke/directorate-of-postgraduate-studies/.
@@ D|Courses & Programmes|Can I do a master's degree at MUT after completing my bachelor's?
MUT offers Master's programmes in all its schools (https://mut.ac.ke/postgraduate-programmes/). Admission depends on meeting the entry requirements; contact the Directorate of Postgraduate Studies: https://mut.ac.ke/directorate-of-postgraduate-studies/.
@@ D|Courses & Programmes|What are the entry requirements for a master's programme?
Entry requirements are set per programme. See https://mut.ac.ke/postgraduate-programmes/ or contact the Directorate of Postgraduate Studies: https://mut.ac.ke/directorate-of-postgraduate-studies/. The postgraduate application fee is Kshs 2,000.
@@ D|Fees & Finance|How much are the fees per semester?
Fees depend on your programme and sponsorship. Download your fee structure at https://mut.ac.ke/fees-structure/ (postgraduate by school, TVET diploma government-sponsored and self-sponsored). KUCCPS students also receive a fee letter with their admission letter at https://admissions.mut.ac.ke.
@@ A|Fees & Finance|How do I pay my school fees?
Pay into one of the MUT bank accounts at Murang'a branch:
Equity Bank - Equity Collection Account - 0220273636188
KCB - KCB Collection - 1107198356
Co-operative Bank - Co-operative Fees Collection - 01129573999200
Your fee payment letter (with your admission letter at https://admissions.mut.ac.ke) has the payment details. Hostel fees are paid through the student portal via eCitizen/M-Pesa.
@@ P|Fees & Finance|What is MUT's paybill number?
@@ D|Fees & Finance|Can I pay fees through M-Pesa?
Hostel bookings can be paid via eCitizen using the M-Pesa option after logging in to the student portal (https://studentportal.mut.ac.ke). For tuition payment options, confirm with the Finance Office on info@mut.ac.ke or +254 798 959 217.
@@ P|Fees & Finance|Can I pay fees in installments?
@@ D|Fees & Finance|How do I check my fee balance?
Log in to the student portal at https://studentportal.mut.ac.ke. If you cannot see your balance, contact the Finance Office on info@mut.ac.ke or +254 798 959 217.
@@ D|Fees & Finance|Why has my fee balance not updated after payment?
Keep your bank slip or payment receipt and send it to the Finance Office (info@mut.ac.ke, +254 798 959 217, Monday to Friday 8am to 5pm) so they can post it to your account.
@@ D|Fees & Finance|I paid my fees but the system still shows a balance. What should I do?
Keep your bank slip or payment receipt and send it to the Finance Office (info@mut.ac.ke, +254 798 959 217, Monday to Friday 8am to 5pm) so they can post it to your account.
@@ D|Fees & Finance|Where can I get my fee statement?
Log in to the student portal at https://studentportal.mut.ac.ke. If it is not available, request it from the Finance Office: info@mut.ac.ke, +254 798 959 217.
@@ P|Fees & Finance|What happens if I haven't cleared my fees?
@@ P|Fees & Finance|Can I register for units before paying all my fees?
@@ A|Fees & Finance|Does MUT offer scholarships?
Yes. Current opportunities listed on the MUT website include the EAC Scholarship Programme (Cohort 4) and Erasmus+ International Credit Mobility scholarships for 2026/2027. See https://mut.ac.ke/scholarships/ and https://mut.ac.ke/eac-scholarship-programme-cohort-4/. Students can also apply for the MUTSO Bursary.
@@ D|Fees & Finance|How do I apply for a scholarship?
Each scholarship has its own application steps; see https://mut.ac.ke/scholarships/. For the MUTSO Bursary, use the application form: https://mut.ac.ke/wp-content/uploads/2025/01/MUTSO-BURSARY-APPLICATION-FORM.pdf.
@@ D|Fees & Finance|Does HELB cover students at MUT?
MUT's website links to the HEF (Higher Education Funding) portal for student funding applications: https://portal.hef.co.ke/auth/signin and https://www.hef.co.ke/. For details on your funding, contact the Finance Office on info@mut.ac.ke or +254 798 959 217.
@@ D|Fees & Finance|What happens if I cannot afford my fees this semester?
Options include applying for government funding via the HEF portal (https://portal.hef.co.ke/auth/signin), the MUTSO Bursary (https://mut.ac.ke/wp-content/uploads/2025/01/MUTSO-BURSARY-APPLICATION-FORM.pdf), or scholarships (https://mut.ac.ke/scholarships/). You can also talk to the Dean of Students (https://mut.ac.ke/dean-of-students/) or Guidance & Counselling.
@@ A|Accommodation & Hostels|Does MUT have student hostels?
Yes. MUT has about 1,100 hostel spaces on campus (600 for ladies and 516 for gents). Hostels have a kitchen, student cafeteria and security. There are also approved off-campus hostels. See https://mut.ac.ke/hostels-accommodation/.
@@ A|Accommodation & Hostels|How much does accommodation cost?
University hostels are charged at a subsidised rate of Kshs 15,800 per academic year. Approved off-campus hostels charge rent per head, roughly Kshs 2,500 to Kshs 10,000 depending on the hostel (2024/2025 list). See https://mut.ac.ke/hostels-accommodation/.
@@ A|Accommodation & Hostels|How do I apply for a hostel?
1. Download your admission letter on the admissions portal (https://admissions.mut.ac.ke) and update your details.
2. Once verified, log in to the student portal (https://studentportal.mut.ac.ke), open hostel booking and pay for your room via eCitizen using the M-Pesa option.
Pay only after securing accommodation from the university. Questions: 0705 939 269.
@@ A|Accommodation & Hostels|Are first-year students given priority for hostels?
MUT offers accommodation to first years on a first come, first served basis. Spaces on campus are limited, so book early. See https://mut.ac.ke/hostels-accommodation/.
@@ A|Accommodation & Hostels|Are there hostels near MUT?
Yes. MUT publishes a list of approved off-campus hostels, including Wood Crest, Elleninel, Nemises, Gateway, Richaro, Village Market, Rev. Gitura, White House, View Point, Manyarati, Kiru, M.C.W, Wanjoya, Valley View and Kwa Guka. See the full list and rents at https://mut.ac.ke/hostels-accommodation/. Visit a hostel before booking.
@@ P|Accommodation & Hostels|How far are the private hostels from the university?
@@ A|Accommodation & Hostels|What facilities are available in the hostels?
University hostels have a kitchen, student cafeteria, janitor's office, a security office on campus, and 24-hour CCTV. Each room holds 4 students and has shared washrooms and bathrooms. See https://mut.ac.ke/hostels-accommodation/.
@@ P|Accommodation & Hostels|Can I choose my roommate?
@@ A|Accommodation & Hostels|When should I apply for accommodation?
As soon as you are admitted and have downloaded your admission letter: university rooms are given first come, first served. Start at https://admissions.mut.ac.ke, then book through the student portal (https://studentportal.mut.ac.ke).
@@ A|Accommodation & Hostels|What happens if I don't get a university hostel?
Use one of the approved off-campus hostels listed at https://mut.ac.ke/hostels-accommodation/. Do not pay for off-campus accommodation before you arrive. Visit the area, inspect the house and confirm the landlord or agent before making any payment, and never send money to anyone offering to reserve a room for you.
@@ A|Registration & Academic Portal|How do I log into the student portal?
Go to https://studentportal.mut.ac.ke/authentication/login. The new student portal is for new and continuing students. The old portal (https://portal.mut.ac.ke) keeps historical data for continuing students. If you have trouble, contact the ICT Helpdesk: https://helpdesk.mut.ac.ke.
@@ A|Registration & Academic Portal|I forgot my student portal password. What should I do?
Contact the ICT Helpdesk at https://helpdesk.mut.ac.ke for a password reset.
@@ P|Registration & Academic Portal|How do I register for units?
@@ P|Registration & Academic Portal|How do I check my registered units?
@@ A|Registration & Academic Portal|How do I check my exam timetable?
Exam and class timetables are published at https://mut.ac.ke/timetables/.
@@ A|Registration & Academic Portal|Where can I find my class timetable?
Class and exam timetables are published at https://mut.ac.ke/timetables/.
@@ P|Registration & Academic Portal|How do I check my results?
@@ P|Registration & Academic Portal|My results are missing from the portal. What should I do?
@@ P|Registration & Academic Portal|How do I download my transcript?
@@ P|Registration & Academic Portal|How do I print my fee statement?
@@ P|Registration & Academic Portal|Why can't I register for a unit?
@@ A|Registration & Academic Portal|The student portal is not working. What should I do?
Report the problem to the ICT Helpdesk at https://helpdesk.mut.ac.ke.
@@ P|Registration & Academic Portal|How do I update my personal information?
@@ P|Registration & Academic Portal|How do I know whether I have completed my unit registration?
@@ A|Examinations|When are the exams?
The dates for end-of-semester examinations are published at https://mut.ac.ke/timetables/ and https://mut.ac.ke/reporting-and-semester-dates/ (the 2026/2027 exam dates were still to be announced at the last check).
@@ A|Examinations|Where can I find the exam timetable?
Exam and class timetables are published at https://mut.ac.ke/timetables/.
@@ D|Examinations|What happens if I miss an exam?
Register for a Special/Resit/Retake exam using this form: https://mut.ac.ke/wp-content/uploads/2022/10/Special-Resit-Retake-Exam-Registration-Form.pdf. The rules are in the Examination Regulations: https://mut.ac.ke/wp-content/uploads/2026/09/Examination-Regulations-Print-Publication.pdf. Contact your school or the Registrar (Academic and Student Affairs) as soon as possible.
@@ D|Examinations|Can I apply for a special exam?
Yes, use the Special/Resit/Retake Exam Registration Form: https://mut.ac.ke/wp-content/uploads/2022/10/Special-Resit-Retake-Exam-Registration-Form.pdf. The conditions are in the Examination Regulations: https://mut.ac.ke/wp-content/uploads/2026/09/Examination-Regulations-Print-Publication.pdf.
@@ D|Examinations|What happens if I fail a unit?
The rules for failed units, resits and retakes are in the Examination Regulations: https://mut.ac.ke/wp-content/uploads/2026/09/Examination-Regulations-Print-Publication.pdf. To register for a resit/retake use https://mut.ac.ke/wp-content/uploads/2022/10/Special-Resit-Retake-Exam-Registration-Form.pdf.
@@ D|Examinations|How do supplementary exams work?
The rules are in the Examination Regulations: https://mut.ac.ke/wp-content/uploads/2026/09/Examination-Regulations-Print-Publication.pdf. To register use the Special/Resit/Retake form: https://mut.ac.ke/wp-content/uploads/2022/10/Special-Resit-Retake-Exam-Registration-Form.pdf.
@@ D|Examinations|How do I apply for a remark?
Fill in the Application for Remarking Form: https://mut.ac.ke/wp-content/uploads/2022/10/Application-for-Remarking-Form.pdf. Deadlines and fees are in the Examination Regulations: https://mut.ac.ke/wp-content/uploads/2026/09/Examination-Regulations-Print-Publication.pdf.
TXT;

    public function run(ChatService $svc): void {
        $ids = collect($this->cats)->mapWithKeys(fn($kw, $name) => [$name => Category::firstOrCreate(['name' => $name])->id]);
        $n = ['A' => 0, 'D' => 0, 'P' => 0];
        foreach (preg_split('/^@@ /m', $this->data) as $block) {
            $block = trim($block); if ($block === '') continue;
            [$head, $body] = array_pad(explode("\n", $block, 2), 2, '');
            [$st, $cat, $q] = array_map('trim', explode('|', $head, 3));
            $answer = trim($body);
            $exists = KnowledgeEntry::where('question', $q)->first();
            if ($st === 'P') {
                if ($exists) continue;
                $answer = 'Answer not yet provided. Please contact the relevant MUT office.';
            }
            $words = array_filter($svc->tokens($q . ' ' . ($st === 'P' ? '' : strip_tags($answer))), fn($w) => strlen($w) > 3);
            $freq = array_count_values($words); arsort($freq);
            $kw = array_merge($svc->tokens($q), explode(',', $this->cats[$cat]), array_slice(array_keys($freq), 0, 6));
            KnowledgeEntry::updateOrCreate(['question' => $q], [
                'category_id' => $ids[$cat], 'title' => $q, 'answer' => $answer,
                'keywords' => implode(',', array_unique(array_filter($kw, fn($w) => strlen($w) > 2))),
                'is_approved' => $st === 'A',
            ]);
            $n[$st]++;
        }
        foreach ($this->synonyms as $word => $alts) Synonym::firstOrCreate(['word' => $word], ['alternatives' => $alts]);
        Cache::forever('kb_v', microtime(true)); Cache::forget('synonym_groups');
        $this->command?->info("Approved: {$n['A']} | Drafts with answers to verify: {$n['D']} | Still need answers: {$n['P']}");
    }
}
