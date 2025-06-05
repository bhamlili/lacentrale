const form = document.getElementById('loginForm');
const errorMessage = document.getElementById('errorMessage');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  errorMessage.style.display = 'none';
  errorMessage.textContent = '';

  const username = form.username.value.trim();
  const password = form.password.value.trim();

  if (!username || !password) {
    errorMessage.textContent = 'Veuillez remplir tous les champs.';
    errorMessage.style.display = 'block';
    return;
  }

  const formData = new FormData(form);

  try {
    const response = await fetch('login.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      window.location.href = 'gestion_rdv.html';
    } else {
      errorMessage.textContent = 'Identifiants incorrects.';
      errorMessage.style.display = 'block';
    }
  } catch (error) {
    errorMessage.textContent = 'Erreur serveur. Réessayez plus tard.';
    errorMessage.style.display = 'block';
    console.error(error);
  }
});
