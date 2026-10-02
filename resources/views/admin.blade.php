<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><title>Chatbot admin</title><link rel="stylesheet" href="/css/app.css"></head>
<body><div class="wrap">
<div class="bar"><h1>Chatbot admin</h1><span><a href="/" target="_blank">Open chatbot</a> <form method="post" action="/logout" style="display:inline">@csrf<button class="ghost">Sign out</button></form></span></div>
<nav><button data-tab="entries">Knowledge &amp; FAQs</button><button data-tab="docs">Documents</button><button data-tab="unanswered">Unanswered</button><button data-tab="history">History</button><button data-tab="analytics">Analytics</button><button data-tab="settings">Categories &amp; synonyms</button></nav>

<section class="tab" id="tab-entries"><div class="card"><h3 id="ftitle">Add entry</h3>
<input type="hidden" id="e-id"><input type="hidden" id="e-log">
<div class="grid"><label>Title<input id="e-title"></label><label>Category<select id="e-cat"></select></label></div>
<p><label>Question / FAQ wording<input id="e-q"></label></p><p><label>Approved answer<textarea id="e-a" rows="4"></textarea></label></p>
<p><label>Keywords / tags (comma separated)<input id="e-k"></label></p>
<p><label><input type="checkbox" id="e-ok" checked style="width:auto"> Approved (visible to students)</label></p>
<button id="e-save">Save entry</button> <button class="ghost" id="e-clear">Clear</button> <span class="err" id="e-err"></span></div>
<div class="card"><div class="grid"><input id="s" placeholder="Search entries"><select id="fc"></select><select id="fa"><option value="">All statuses</option><option value="1">Approved</option><option value="0">Draft</option></select></div>
<div class="tw"><table><thead><tr><th>Question</th><th>Category</th><th>Status</th><th>Hits</th><th></th></tr></thead><tbody id="e-rows"></tbody></table></div>
<p><button class="ghost" id="prev">Previous</button> <button class="ghost" id="next">Next</button> <span id="pg"></span></p></div></section>

<section class="tab" id="tab-docs" hidden><div class="card"><h3>Upload PDF</h3><p>Text is extracted into draft entries. Review and approve them under Knowledge &amp; FAQs.</p>
<div class="grid"><input id="d-title" placeholder="Document title"><input id="d-file" type="file" accept="application/pdf"></div><p><button id="d-up">Upload and extract</button> <span class="err" id="d-err"></span></p></div>
<div class="card tw"><table><thead><tr><th>Title</th><th>Status</th><th>Entries</th><th>Uploaded</th><th></th></tr></thead><tbody id="d-rows"></tbody></table></div></section>

<section class="tab" id="tab-unanswered" hidden><div class="card tw"><table><thead><tr><th>Question</th><th>Asked</th><th></th></tr></thead><tbody id="u-rows"></tbody></table></div></section>

<section class="tab" id="tab-history" hidden><div class="card"><div class="grid"><input id="h-s" placeholder="Search questions"><select id="h-a"><option value="">All</option><option value="1">Answered</option><option value="0">Unanswered</option></select></div>
<div class="tw"><table><thead><tr><th>When</th><th>Question</th><th>Result</th><th>Response</th></tr></thead><tbody id="h-rows"></tbody></table></div></div></section>

<section class="tab" id="tab-analytics" hidden><div class="grid" id="stats"></div>
<div class="grid"><div class="card"><h3>Questions per day</h3><canvas id="c1"></canvas></div><div class="card"><h3>Most searched topics</h3><canvas id="c2"></canvas></div></div>
<div class="card"><h3>Frequently asked</h3><div class="tw"><table><tbody id="freq"></tbody></table></div></div></section>

<section class="tab" id="tab-settings" hidden><div class="grid"><div class="card"><h3>Categories</h3><div id="cat-list"></div><p><input id="cat-new" placeholder="New category"></p><button id="cat-add">Add category</button></div>
<div class="card"><h3>Synonyms</h3><div id="syn-list"></div><p><input id="syn-w" placeholder="Word, e.g. fees"></p><p><input id="syn-a" placeholder="Same meaning: tuition,cost,price"></p><button id="syn-add">Add synonyms</button></div></div></section>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script><script src="/js/admin.js"></script></body></html>
