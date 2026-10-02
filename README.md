# University Student Chatbot (pure code, no AI)

This ZIP is an overlay for a fresh Laravel 11/12 project.

1. composer create-project laravel/laravel unibot
2. Copy everything from this ZIP into unibot/ (allow overwriting routes/web.php and DatabaseSeeder.php)
3. cd unibot && composer require smalot/pdfparser
4. Create an empty MySQL database `unibot`, then edit .env: DB_CONNECTION=mysql, DB_DATABASE=unibot, DB_USERNAME, DB_PASSWORD
5. php artisan migrate --seed
6. php artisan serve  -> http://localhost:8000 (chatbot), /admin (dashboard)

Admin login: admin@university.test / password  (change it immediately)

Matching: tokenise -> stop-word removal -> synonym expansion -> MySQL FULLTEXT (LIKE fallback) -> relevance score
(keyword, tag, phrase, similarity, popularity) -> best approved entry above threshold (ChatService::MIN_SCORE) or fallback.
Uploaded PDFs become DRAFT entries; approve them before students can see them.
Scanned (image-only) PDFs are not supported without OCR.
