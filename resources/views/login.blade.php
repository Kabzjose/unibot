<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin sign in</title><link rel="stylesheet" href="/css/app.css"></head>
<body><div class="wrap" style="max-width:380px"><form method="post" action="/login" class="card">@csrf<h2>Admin sign in</h2>
<p><label>Email<input name="email" type="email" value="{{ old('email') }}" required></label></p>
<p><label>Password<input name="password" type="password" required></label></p>
@error('email')<p class="err">{{ $message }}</p>@enderror<button>Sign in</button></form></div></body></html>
