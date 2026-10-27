const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
const BC = {
    esc: s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])),
    badge(s) {
        const m = {
            verifie: ['ok', 'Vérifié'], incomplet: ['gray', 'Incomplet'], actif: ['ok', 'Actif'],
            suspendu: ['gray', 'Suspendu'], connecte: ['ok', 'Connectée'], hors: ['gray', 'Non connectée'],
            attente: ['warn', 'En attente'], confirme: ['ok', 'Confirmée'], introuvable: ['gray', 'Non retrouvée'],
            ok: ['ok', 'OK'], active: ['ok', 'Actif'], approved: ['ok', 'Approuvé'], pending: ['warn', 'En attente']
        };
        if (/^etape/.test(s)) return `<span class="badge warn">Étape ${s.slice(5)} / 4</span>`;
        const [c, t] = m[s] || ['gray', s];
        return `<span class="badge ${c}">${t}</span>`;
    },
    table(el, cols, rows) {
        el.innerHTML = '<thead><tr>' + cols.map(c => `<th>${c.h}</th>`).join('') + '</tr></thead><tbody>' +
            (rows.length
                ? rows.map((r, i) => '<tr>' + cols.map(c => `<td class="${c.c || ''}">${c.f ? c.f(r, i) : BC.esc(r[c.k])}</td>`).join('') + '</tr>').join('')
                : `<tr><td colspan="${cols.length}" class="empty">Aucun résultat</td></tr>`) + '</tbody>';
    },
    toast(m) {
        const t = document.createElement('div');
        t.className = 'toast';
        t.textContent = m;
        t.setAttribute('role', 'status');
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2800);
    },
    tabs() {
        $$('[data-tab]').forEach(b => b.onclick = () => {
            $$('[data-tab]').forEach(x => x.classList.toggle('on', x === b));
            $$('[data-sec]').forEach(s => s.hidden = s.dataset.sec !== b.dataset.tab);
        });
    }
};

document.querySelectorAll('.toast').forEach(t => setTimeout(() => t.remove(), 3200));
