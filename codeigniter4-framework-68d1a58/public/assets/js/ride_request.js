document.addEventListener("DOMContentLoaded", () => {
  const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
  const requestBtn = document.getElementById("request-btn");
  if (!requestBtn) return;

  const rideId = Number(window.RIDE_ID || 0);
  if (!rideId) return;

  const seatsInput = document.getElementById("seats-request");

  requestBtn.addEventListener("click", async () => {
    const seats = seatsInput ? Math.max(1, parseInt(seatsInput.value, 10) || 1) : 1;

    try {
      const res = await fetch(`${baseUrl}/passenger/api/reservations`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ ride_id: rideId, seats })
      });

      const data = await res.json().catch(() => null);

      if (!res.ok || !data || data.ok === false) {
        throw new Error(data?.error || 'Could not create reservation.');
      }

      alert('Request sent successfully!');
      window.location.href = `${baseUrl}/passenger/search-rides`;
    } catch (err) {
      console.error('[reservation] error:', err);
      alert(`Error: ${err.message}`);
    }
  });
});
