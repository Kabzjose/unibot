<?php
namespace Database\Seeders;
use App\Models\{Category, KnowledgeEntry, Synonym, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        User::forceCreate(['name' => 'Admin', 'email' => 'admin@university.test', 'password' => Hash::make('password'), 'is_admin' => true]);
        $c = Category::create(['name' => 'Admissions']); Category::create(['name' => 'Fees']); Category::create(['name' => 'Programs']);
        KnowledgeEntry::create(['category_id' => $c->id, 'title' => 'Admission requirements', 'question' => 'What are the requirements for admission?',
            'answer' => 'Applicants need a completed application form, secondary school transcripts, a valid ID, and two references. (Edit this sample in the admin dashboard.)',
            'keywords' => 'admission,requirements,apply,application,entry,enrol']);
        Synonym::create(['word' => 'admission', 'alternatives' => 'enrollment,enrolment,application,apply,entry']);
        Synonym::create(['word' => 'fees', 'alternatives' => 'tuition,cost,price,payment']);
    }
}
