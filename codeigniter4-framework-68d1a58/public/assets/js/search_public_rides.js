document.addEventListener('DOMContentLoaded', () => { 
  const fromSel   = document.getElementById('from');
  const toSel     = document.getElementById('to');
  const findBtn   = document.querySelector('.find-btn');
  const tbody     = document.querySelector('.rides-table tbody');
  const resultEl  = document.querySelector('.search-result');
  const sortBySel = document.getElementById('sort-by');
  const sortDirSel= document.getElementById('sort-dir');
  const sortBtn   = document.querySelector('.sort-apply');

  let lastRides = []; // cache de resultados para reordenar sin volver a pedir

  clearTable(tbody);
  updateResultMessage(resultEl, '', '');

  // Carga inicial
  fetchRidesAndRender('', '', [], true);

  // Búsqueda con filtros
  findBtn.addEventListener('click', () => {
    const selectedFrom = (fromSel.value || '').trim();
    const selectedTo   = (toSel.value || '').trim();
    const selectedDays = getSelectedDays(); // ej: ["mon","wed"]
    fetchRidesAndRender(selectedFrom, selectedTo, selectedDays, false);
  });

  // Ordenamiento
  sortBtn?.addEventListener('click', () => {
    const by  = sortBySel?.value || 'date';
    const dir = (sortDirSel?.value || 'asc').toLowerCase();
    const sorted = sortRides(lastRides, by, dir);
    renderResults(sorted, tbody);
  });

  async function fetchRidesAndRender(from, to, days, isFirstLoad) {
    try {
      const params = new URLSearchParams();
      if (from) params.set('from', from);
      if (to)   params.set('to', to);
      if (days && days.length) params.set('days', days.join(','));

      //URL endpoint de CodeIgniter (GET)
      const url = `/public-rides/search?${params.toString()}`;

      const res  = await fetch(url, {
        headers: { 'Accept': 'application/json' }
      });
      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('La respuesta no es JSON. Respuesta fue:', text);
        throw new Error('Endpoint no devolvió JSON');
      }

      if (!res.ok || !data || data.ok === false) {
        console.error('Error de backend:', data && data.error ? data.error : 'Respuesta no OK', data);
        throw new Error(data && data.error ? data.error : 'Respuesta no OK del backend');
      }

      const rides = Array.isArray(data.rides) ? data.rides : [];

      // Calcular la próxima salida (hoy -- futuro) para cada ride
      rides.forEach(r => {
        r.nextDeparture = computeNextDeparture(r.days, r.time); // Date o null
        r.whenLabel = formatWhenLabel(r.days, r.time);
      });

      // Orden por defecto
      lastRides = sortRides(rides, 'date', 'asc');

      if (isFirstLoad) {
        initLocationSelects(lastRides, fromSel, toSel);
      }

      renderResults(lastRides, tbody);
      resultEl.style.display = "block";
      updateResultMessage(resultEl, from, to);
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

// Helpers

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
    if (input && input.checked) out.push(l.textContent.trim().toLowerCase().substring(0,3));
  });
  return out;
}

function clearTable(tbody) {
  tbody.innerHTML = `
    <tr>
      <td colspan="8" style="text-align:center; opacity:.8;"></td>
    </tr>`;
}

function renderResults(rows, tbody) {
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
    const fee   = (ride.fee === 0 || ride.fee) ? `$${ride.fee}` : '--';
    const carMake  = ride.vehicle?.make || '';
    const carModel = ride.vehicle?.model || '';
    const carYear  = ride.vehicle?.year || '';
    const carText  = [carMake, carModel, carYear].filter(Boolean).join(' ');
    const whenTxt  = ride.whenLabel || '—';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><img src="/assets/img/user_icon.png" class="small-icon" alt="User"> ${driverEmail}</td>
      <td>${from}</td>
      <td>${to}</td>
      <td>${whenTxt}</td>
      <td>${seats}</td>
      <td>${carText}</td>
      <td>${fee}</td>
      <td><a href="/login">Please Login to Request</a></td>
    `;
    tbody.appendChild(tr);
  });
}

function updateResultMessage(el, from, to) {
  if (!el) return;
  const f = from ? `<b>${from}</b>` : '<b>Any</b>';
  const t = to   ? `<b>${to}</b>`   : '<b>Any</b>';
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
    iframe.src = `https://www.google.com/maps?q=Costa+Rica&z=7&output=embed`;
  }
}

// ---------- cálculo próxima salida y ordenamiento ----------
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
  const short = { sun:'Sun', mon:'Mon', tue:'Tue', wed:'Wed', thu:'Thu', fri:'Fri', sat:'Sat' };
  const dd = Array.isArray(days)
    ? days.map(d => short[d?.slice(0,3)?.toLowerCase()] || d).join(', ')
    : '';
  return [timeHHMM || '', dd].filter(Boolean).join(' · ');
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
