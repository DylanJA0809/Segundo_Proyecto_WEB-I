document.addEventListener('DOMContentLoaded', () => {
  const fromSel    = document.getElementById('from');
  const toSel      = document.getElementById('to');
  const findBtn    = document.querySelector('.find-btn');
  const tbody      = document.querySelector('.rides-table tbody');
  const resultEl   = document.querySelector('.search-result');
  const sortBySel  = document.getElementById('sort-by');
  const sortDirSel = document.getElementById('sort-dir');
  const sortBtn    = document.querySelector('.sort-apply');

 
  const BASE_URL = (window.BASE_URL || '/').replace(/\/?$/, '/');
  const API_URL  = BASE_URL + 'passenger/api/search-rides';

  const USER_ICON = window.USER_ICON_URL || (BASE_URL + 'assets/img/user_icon.png');

  let lastRides = [];

  clearTable(tbody);
  updateResultMessage(resultEl, '', '');

  // Carga inicial (sin filtros)
  fetchRidesAndRender('', '', [], true);

  // Buscar
  findBtn?.addEventListener('click', () => {
    const selectedFrom = (fromSel.value || '').trim();
    const selectedTo   = (toSel.value || '').trim();
    const selectedDays = getSelectedDays(); // ["mon","wed",...]
    fetchRidesAndRender(selectedFrom, selectedTo, selectedDays, false);
  });

  // Ordenar sin volver a pedir
  sortBtn?.addEventListener('click', () => {
    const by  = sortBySel?.value || 'date';
    const dir = (sortDirSel?.value || 'asc').toLowerCase();
    const sorted = sortRides(lastRides, by, dir);
    renderResults(sorted, tbody, USER_ICON, BASE_URL);
  });

  async function fetchRidesAndRender(from, to, days, isFirstLoad) {
    try {
      const params = new URLSearchParams();
      if (from) params.set('from', from);
      if (to)   params.set('to', to);
      if (days && days.length) params.set('days', days.join(','));

      const url = `${API_URL}?${params.toString()}`;

      const res  = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
      });

      const data = await res.json().catch(async () => {
        const text = await res.text();
        console.error('Respuesta no JSON:', text);
        throw new Error('Endpoint no devolvió JSON');
      });

      if (!res.ok || !data || data.ok === false) {
        const errMsg = (data && data.error) ? data.error : 'Respuesta no OK del backend';
        console.error('Error backend:', errMsg, data);
        throw new Error(errMsg);
      }

      const rides = Array.isArray(data.rides) ? data.rides : [];

      // Normalizar + calcular "when"
      rides.forEach(r => {
        r.nextDeparture = computeNextDeparture(r.days, r.time); // Date|null
        r.whenLabel     = formatWhenLabel(r.days, r.time);
      });

      // Orden por defecto: próximo viaje más cercano primero
      lastRides = sortRides(rides, 'date', 'asc');

      if (isFirstLoad) {
        initLocationSelects(lastRides, fromSel, toSel);
        ensureDaysHaveDataDay();
      }

      renderResults(lastRides, tbody, USER_ICON, BASE_URL);

      if (resultEl) {
        resultEl.style.display = 'block';
        updateResultMessage(resultEl, from, to);
      }

      updateMap(from, to);
    } catch (err) {
      tbody.innerHTML = `
        <tr>
          <td colspan="8" style="text-align:center; opacity:.8;">
            Error loading rides...
          </td>
        </tr>`;
      console.error('[rides] fetch error:', err);
    }
  }
});

// ---------------- Helpers ----------------

function ensureDaysHaveDataDay() {
  const labels = document.querySelectorAll('.days-checkboxes label');
  labels.forEach(l => {
    if (!l.dataset.day) {
      const txt = (l.textContent || '').trim().toLowerCase(); // "mon"
      l.dataset.day = txt.substring(0,3);
    }
  });
}

function initLocationSelects(rides, from, to) {
  const fromSet = new Set();
  const toSet   = new Set();

  rides.forEach(r => {
    if (r.from) fromSet.add(r.from);
    if (r.to)   toSet.add(r.to);
  });

  if (fromSet.size === 0 && toSet.size === 0) return;

  from.innerHTML = '';
  to.innerHTML   = '';
  from.appendChild(new Option('- Select origin -', ''));
  to.appendChild(new Option('- Select destination -', ''));

  Array.from(fromSet).sort().forEach(f => from.appendChild(new Option(f, f)));
  Array.from(toSet).sort().forEach(t => to.appendChild(new Option(t, t)));
}

function getSelectedDays() {
  const labels = document.querySelectorAll('.days-checkboxes label');
  const out = [];
  labels.forEach(l => {
    const input = l.querySelector('input[type="checkbox"]');
    if (input && input.checked) {
      const d = (l.dataset.day || l.textContent || '').trim().toLowerCase();
      out.push(d.substring(0,3));
    }
  });

  // Normalizar y filtrar válidos
  const allowed = new Set(['mon','tue','wed','thu','fri','sat','sun']);
  return out.filter(d => allowed.has(d));
}

function clearTable(tbody) {
  tbody.innerHTML = `
    <tr>
      <td colspan="8" style="text-align:center; opacity:.8;"></td>
    </tr>`;
}

function renderResults(rows, tbody, userIconUrl, baseUrl) {
  tbody.innerHTML = '';

  if (!rows.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" style="text-align:center; opacity:.8;">
          No rides found...
        </td>
      </tr>`;
    return;
  }

  rows.forEach(ride => {
    const driverEmail = ride.userEmail || 'driver';
    const from  = ride.from || '';
    const to    = ride.to   || '';
    const seats = ride.seats ?? '';
    const fee   = (ride.fee === 0 || ride.fee) ? `₡${Number(ride.fee).toLocaleString('es-CR')}` : '--';

    const carMake  = ride.vehicle?.make || '';
    const carModel = ride.vehicle?.model || '';
    const carYear  = ride.vehicle?.year || '';
    const carText  = [carMake, carModel, carYear].filter(Boolean).join(' ');

    const whenTxt  = ride.whenLabel || '—';

    const detailsHref = baseUrl + `passenger/ride-details/${ride.id}`;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><img src="${userIconUrl}" class="small-icon" alt="User"> ${escapeHtml(driverEmail)}</td>
      <td><a href="${detailsHref}">${escapeHtml(from)}</a></td>
      <td>${escapeHtml(to)}</td>
      <td>${escapeHtml(whenTxt)}</td>
      <td>${escapeHtml(String(seats))}</td>
      <td>${escapeHtml(carText)}</td>
      <td>${escapeHtml(fee)}</td>
      <td><a href="${detailsHref}">Request</a></td>
    `;
    tbody.appendChild(tr);
  });
}

function updateResultMessage(el, from, to) {
  if (!el) return;
  const f = from ? `<b>${escapeHtml(from)}</b>` : '<b>Any</b>';
  const t = to   ? `<b>${escapeHtml(to)}</b>`   : '<b>Any</b>';
  el.innerHTML = `Rides found from ${f} to ${t}`;
}

function updateMap(from, to) {
  const iframe = document.querySelector('.map-iframe');
  if (!iframe) return;

  if (from && to) {
    iframe.src = `https://www.google.com/maps?q=${encodeURIComponent(from + ' to ' + to)}&z=11&output=embed`;
  } else if (from || to) {
    const place = from || to;
    iframe.src = `https://www.google.com/maps?q=${encodeURIComponent(place)}&z=12&output=embed`;
  } else {
    iframe.src = `https://www.google.com/maps?q=Costa%20Rica&z=7&output=embed`;
  }
}

// ----------- próxima salida y ordenamiento -----------

function computeNextDeparture(days, timeHHMM) {
  if (!Array.isArray(days) || !days.length || !timeHHMM) return null;

  const dayToIndex = { sun:0, mon:1, tue:2, wed:3, thu:4, fri:5, sat:6 };

  const wanted = days
    .map(d => (d || '').toLowerCase().slice(0,3))
    .map(d => dayToIndex[d])
    .filter(i => i !== undefined);

  if (!wanted.length) return null;

  const [hh, mm] = (timeHHMM || '00:00').split(':').map(n => parseInt(n,10) || 0);

  const now = new Date();
  now.setSeconds(0,0);

  for (let addDays = 0; addDays < 14; addDays++) {
    const d = new Date(now);
    d.setDate(now.getDate() + addDays);

    if (!wanted.includes(d.getDay())) continue;

    d.setHours(hh, mm, 0, 0);
    if (d >= now) return d;
  }
  return null;
}

function formatWhenLabel(days, timeHHMM) {
  const shortDaysMap = { sun:'Sun', mon:'Mon', tue:'Tue', wed:'Wed', thu:'Thu', fri:'Fri', sat:'Sat' };
  const dd = Array.isArray(days)
    ? days.map(d => shortDaysMap[d?.slice(0,3)?.toLowerCase()] || d).join(', ')
    : '';
  const t  = timeHHMM || '';
  return [t, dd].filter(Boolean).join(' · ');
}

function sortRides(rides, by = 'date', dir = 'asc') {
  const asc = dir !== 'desc' ? 1 : -1;
  const copy = [...rides];

  if (by === 'origin') {
    copy.sort((a,b) => asc * String(a.from||'').localeCompare(String(b.from||'')));
  } else if (by === 'destination') {
    copy.sort((a,b) => asc * String(a.to||'').localeCompare(String(b.to||'')));
  } else {
    copy.sort((a,b) => {
      const ax = a.nextDeparture ? a.nextDeparture.getTime() : Infinity;
      const bx = b.nextDeparture ? b.nextDeparture.getTime() : Infinity;
      return asc * (ax - bx);
    });
  }
  return copy;
}

// Seguridad básica para insertar texto
function escapeHtml(str) {
  return String(str ?? '')
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;')
    .replaceAll('"','&quot;')
    .replaceAll("'",'&#039;');
}
