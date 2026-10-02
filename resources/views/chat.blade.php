<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><title>Student Information Assistant</title><link rel="stylesheet" href="/css/app.css"></head>
<body><div class="chat"><header><h1>Student Information Assistant</h1><p>Answers come from approved university documents.</p></header>
<div id="msgs" aria-live="polite"></div>
<form id="f"><input id="q" maxlength="500" placeholder="Type your question" aria-label="Your question" autocomplete="off"><button>Ask</button></form></div>
<script src="/js/chatbot.js"></script></body></html>
