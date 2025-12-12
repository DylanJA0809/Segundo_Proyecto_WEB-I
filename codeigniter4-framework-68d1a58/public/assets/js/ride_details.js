document.addEventListener('DOMContentLoaded', async () => {
  const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
  const rideId = Number(window.RIDE_ID || 0);

  if (!rideId) {
    alert("Invalid ride ID");
    window.location.href = `${baseUrl}/passenger/search-rides`;
    return;
  }

  try {
    const res = await fetch(`${baseUrl}/passenger/api/ride/${encodeURIComponent(rideId)}`, {
      headers: { 'Accept': 'application/json' }
    });

    const data = await res.json().catch(() => null);

    if (!res.ok || !data || data.ok === false) {
      throw new Error(data?.error || 'Backend error');
    }

    if (!data.ride) {
      alert("Ride not found");
      window.location.href = `${baseUrl}/passenger/search-rides`;
      return;
    }

    fillRideDetails(data.ride);
  } catch (err) {
    console.error('[ride-details] error:', err);
    alert("Could not load ride.");
    window.location.href = `${baseUrl}/passenger/search-rides`;
  }
});

function fillRideDetails(ride) {
  const usernameEl = document.querySelector('.ride-profile .username');
  if (usernameEl) usernameEl.textContent = ride.userEmail || 'Driver';

  const spans = document.querySelectorAll('.route-info label span');
  if (spans[0]) spans[0].textContent = ride.from || '';
  if (spans[1]) spans[1].textContent = ride.to || '';

  // Days
  const daysSet = new Set((ride.days || []).map(d => String(d).trim().toLowerCase().slice(0, 3)));
  document.querySelectorAll('.days-checkboxes label').forEach(label => {
    const txt = label.textContent.trim().toLowerCase().slice(0, 3);
    const input = label.querySelector('input[type="checkbox"]');
    if (input) input.checked = daysSet.has(txt);
  });

  // Time, seats available, fee
  const timeInput = document.querySelector('.ride-fields input[type="time"]');
  const numberInputs = document.querySelectorAll('.ride-fields input[type="number"]');
  const seatsAvailableInput = numberInputs[0]; // el primero 
  const feeInput = numberInputs[1];

  if (timeInput) timeInput.value = (ride.time || '').slice(0, 5);
  if (seatsAvailableInput) seatsAvailableInput.value = ride.seats ?? 1;
  if (feeInput) feeInput.value = ride.fee ?? 0;

  // Vehicle
  const makeEl = document.getElementById('make');
  const modelEl = document.getElementById('model');
  const yearEl = document.getElementById('year');

  if (makeEl) makeEl.value = ride.vehicle?.make || '';
  if (modelEl) modelEl.value = ride.vehicle?.model || '';
  if (yearEl) yearEl.value = ride.vehicle?.year || '';

  document.querySelectorAll('input, select, textarea').forEach(el => {
    if (el.id === 'seats-request') return;
    el.setAttribute('disabled', 'disabled');
  });
}
