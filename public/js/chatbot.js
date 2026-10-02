const box = document.getElementById('msgs'), form = document.getElementById('f'), inp = document.getElementById('q');
const csrf = document.querySelector('meta[name=csrf-token]').content;

function add(text, who, chips = []) {
  const d = document.createElement('div'); d.className = 'm ' + who; d.textContent = text;
  chips.forEach(c => { const b = document.createElement('button'); b.type = 'button'; b.className = 'chip'; b.textContent = c; b.onclick = () => ask(c); d.append(b); });
  box.append(d); box.scrollTop = box.scrollHeight; return d;
}
async function ask(text) {
  add(text, 'u'); const wait = add('Searching…', 'b');
  try {
    const r = await fetch('/api/chat', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ question: text }) });
    if (!r.ok) throw new Error(r.status);
    const j = await r.json(); wait.remove(); add(j.answer, 'b', j.suggestions);
  } catch { wait.textContent = 'Something went wrong. Check your connection and ask again.'; }
}
form.onsubmit = e => { e.preventDefault(); const v = inp.value.trim(); if (!v) return; inp.value = ''; ask(v); };
add('Ask me about admissions, fees, programs, or anything in the student handbook.', 'b');
