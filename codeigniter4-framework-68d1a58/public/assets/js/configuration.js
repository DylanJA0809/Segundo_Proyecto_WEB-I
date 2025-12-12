document.addEventListener('DOMContentLoaded', () => {
  const form       = document.querySelector('.configuration-form');
  const publicName = document.getElementById('public-name');
  const publicBio  = document.getElementById('public-bio');
  const saveBtn    = form?.querySelector('.save-btn');

  (async function loadProfile() {
    try {
      const res  = await fetch('/profile/api/bio', { headers: { 'Accept': 'application/json' } });
      const data = await res.json();

      if (!res.ok || !data?.ok) throw new Error(data?.error || 'No se pudo cargar tu perfil.');

      publicName.value = data.first_name || '';
      publicBio.value  = data.bio ?? '';
    } catch (err) {
      console.error('[configuration] load error:', err);
      alert(err.message || 'Error cargando perfil');
    }
  })();

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      if (saveBtn) saveBtn.disabled = true;

      const payload = { bio: (publicBio.value || '').trim() };

      const res  = await fetch('/profile/api/bio', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok || !data?.ok) throw new Error(data?.error || 'No se pudo guardar la bio.');

      alert('Changes saved successfully!');
    } catch (err) {
      console.error('[configuration] save error:', err);
      alert(err.message || 'Error guardando cambios');
    } finally {
      if (saveBtn) saveBtn.disabled = false;
    }
  });
});
