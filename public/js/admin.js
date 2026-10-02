const csrf = document.querySelector('meta[name=csrf-token]').content;
const $ = s => document.querySelector(s);
const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
async function api(url, method = 'GET', body = null) {
  const o = { method, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } };
  if (body instanceof FormData) o.body = body; else if (body) { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(body); }
  const r = await fetch('/admin/api/' + url, o);
  if (!r.ok) { const j = await r.json().catch(() => ({})); throw new Error(Object.values(j.errors || {}).flat().join(' ') || j.message || 'Request failed'); }
  return r.json();
}
let cats = [], page = 1, entries = {}, charts = [];
const tabs = { entries: loadEntries, docs: loadDocs, unanswered: loadUnanswered, history: loadHistory, analytics: loadAnalytics, settings: loadSettings };
document.querySelectorAll('nav button').forEach(b => b.onclick = () => show(b.dataset.tab));
function show(t) {
  document.querySelectorAll('.tab').forEach(e => e.hidden = e.id !== 'tab-' + t);
  document.querySelectorAll('nav button').forEach(b => b.classList.toggle('on', b.dataset.tab === t));
  tabs[t]();
}
async function loadCats() {
  cats = await api('categories');
  const opts = cats.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
  $('#e-cat').innerHTML = '<option value="">No category</option>' + opts; $('#fc').innerHTML = '<option value="">All categories</option>' + opts;
}
// ---- Entries ----
async function loadEntries() {
  const p = new URLSearchParams({ page, search: $('#s').value, category_id: $('#fc').value, approved: $('#fa').value });
  const d = await api('entries?' + p); entries = {};
  $('#e-rows').innerHTML = d.data.map(e => { entries[e.id] = e; return `<tr><td>${esc(e.question)}</td><td>${esc(e.category?.name)}</td>
    <td><span class="badge ${e.is_approved ? '' : 'no'}">${e.is_approved ? 'Approved' : 'Draft'}</span></td><td>${e.hits}</td>
    <td><button class="ghost" data-edit="${e.id}">Edit</button> <button class="del" data-del="${e.id}">Delete</button></td></tr>`; }).join('') || '<tr><td colspan="5">No entries yet. Add one above or upload a PDF.</td></tr>';
  $('#pg').textContent = `Page ${d.current_page} of ${d.last_page}`; $('#prev').disabled = d.current_page <= 1; $('#next').disabled = d.current_page >= d.last_page;
}
$('#e-rows').onclick = async e => {
  const ed = e.target.dataset.edit, del = e.target.dataset.del;
  if (ed) { const x = entries[ed]; $('#ftitle').textContent = 'Edit entry'; $('#e-id').value = x.id; $('#e-title').value = x.title; $('#e-q').value = x.question; $('#e-a').value = x.answer; $('#e-k').value = x.keywords || ''; $('#e-cat').value = x.category_id || ''; $('#e-ok').checked = x.is_approved; scrollTo(0, 0); }
  if (del && confirm('Delete this entry?')) { await api('entries/' + del, 'DELETE'); loadEntries(); }
};
function clearForm() { ['id', 'log', 'title', 'q', 'a', 'k'].forEach(k => $('#e-' + k).value = ''); $('#e-cat').value = ''; $('#e-ok').checked = true; $('#ftitle').textContent = 'Add entry'; $('#e-err').textContent = ''; }
$('#e-clear').onclick = clearForm;
$('#e-save').onclick = async () => {
  const id = $('#e-id').value, body = { title: $('#e-title').value, question: $('#e-q').value, answer: $('#e-a').value, keywords: $('#e-k').value, category_id: $('#e-cat').value || null, is_approved: $('#e-ok').checked, log_id: $('#e-log').value || null };
  try { await api('entries' + (id ? '/' + id : ''), id ? 'PUT' : 'POST', body); clearForm(); loadEntries(); } catch (x) { $('#e-err').textContent = x.message; }
};
['s', 'fc', 'fa'].forEach(i => $('#' + i).oninput = () => { page = 1; loadEntries(); });
$('#prev').onclick = () => { page--; loadEntries(); }; $('#next').onclick = () => { page++; loadEntries(); };
// ---- Documents ----
async function loadDocs() {
  const d = await api('documents');
  $('#d-rows').innerHTML = d.map(x => `<tr><td>${esc(x.title)}</td><td><span class="badge ${x.status === 'failed' ? 'no' : ''}">${x.status}</span></td><td>${x.entries_count}</td><td>${x.created_at.slice(0, 10)}</td><td><button class="del" data-del="${x.id}">Delete</button></td></tr>`).join('') || '<tr><td colspan="5">No documents uploaded.</td></tr>';
}
$('#d-rows').onclick = async e => { if (e.target.dataset.del && confirm('Delete this document? Extracted entries are kept.')) { await api('documents/' + e.target.dataset.del, 'DELETE'); loadDocs(); } };
$('#d-up').onclick = async () => {
  const f = new FormData(); f.append('title', $('#d-title').value); f.append('file', $('#d-file').files[0] || '');
  $('#d-err').textContent = 'Extracting…';
  try { await api('documents', 'POST', f); $('#d-err').textContent = 'Done. Review the draft entries.'; $('#d-title').value = ''; $('#d-file').value = ''; loadDocs(); } catch (x) { $('#d-err').textContent = x.message; }
};
// ---- Unanswered ----
async function loadUnanswered() {
  const d = await api('unanswered');
  $('#u-rows').innerHTML = d.map(x => `<tr><td>${esc(x.question)}</td><td>${x.c}×</td><td><button data-id="${x.id}" data-q="${esc(x.question)}">Create entry</button></td></tr>`).join('') || '<tr><td colspan="3">No unanswered questions.</td></tr>';
}
$('#u-rows').onclick = e => {
  if (!e.target.dataset.id) return; show('entries'); clearForm();
  $('#e-q').value = e.target.dataset.q; $('#e-title').value = e.target.dataset.q.slice(0, 80); $('#e-log').value = e.target.dataset.id; $('#e-a').focus();
};
// ---- History ----
async function loadHistory() {
  const d = await api('logs?' + new URLSearchParams({ search: $('#h-s').value, answered: $('#h-a').value }));
  $('#h-rows').innerHTML = d.data.map(x => `<tr><td>${x.created_at.slice(0, 16).replace('T', ' ')}</td><td>${esc(x.question)}</td><td><span class="badge ${x.answered ? '' : 'no'}">${x.answered ? 'Answered' : 'No match'}</span></td><td>${esc((x.response || '').slice(0, 90))}</td></tr>`).join('');
}
$('#h-s').oninput = loadHistory; $('#h-a').oninput = loadHistory;
// ---- Analytics ----
async function loadAnalytics() {
  const d = await api('analytics'); charts.forEach(c => c.destroy()); charts = [];
  $('#stats').innerHTML = [['Questions', d.total], ['Answered', d.answered], ['Open unanswered', d.unanswered]].map(([l, v]) => `<div class="card"><div class="stat">${v}</div>${l}</div>`).join('');
  charts.push(new Chart($('#c1'), { type: 'line', data: { labels: d.daily.map(x => x.d), datasets: [{ label: 'Total', data: d.daily.map(x => x.t), borderColor: '#1f4e8c' }, { label: 'Answered', data: d.daily.map(x => x.a), borderColor: '#2e8b57' }] } }));
  const topics = d.top_categories.length ? d.top_categories : d.top_entries.map(x => ({ name: x.title, hits: x.hits }));
  charts.push(new Chart($('#c2'), { type: 'bar', data: { labels: topics.map(x => x.name), datasets: [{ label: 'Hits', data: topics.map(x => x.hits), backgroundColor: '#1f4e8c' }] } }));
  $('#freq').innerHTML = d.frequent.map(x => `<tr><td>${esc(x.question)}</td><td>${x.c}×</td></tr>`).join('');
}
// ---- Settings ----
async function loadSettings() {
  await loadCats(); $('#cat-list').innerHTML = cats.map(c => `<span class="badge">${esc(c.name)}</span> `).join('') || 'None yet.';
  const s = await api('synonyms');
  $('#syn-list').innerHTML = s.map(x => `<p><b>${esc(x.word)}</b>: ${esc(x.alternatives)} <button class="del" data-del="${x.id}">Remove</button></p>`).join('') || 'None yet.';
}
$('#cat-add').onclick = async () => { try { await api('categories', 'POST', { name: $('#cat-new').value }); $('#cat-new').value = ''; loadSettings(); } catch (x) { alert(x.message); } };
$('#syn-add').onclick = async () => { try { await api('synonyms', 'POST', { word: $('#syn-w').value, alternatives: $('#syn-a').value }); $('#syn-w').value = $('#syn-a').value = ''; loadSettings(); } catch (x) { alert(x.message); } };
$('#syn-list').onclick = async e => { if (e.target.dataset.del) { await api('synonyms/' + e.target.dataset.del, 'DELETE'); loadSettings(); } };
loadCats().then(() => show('entries'));
