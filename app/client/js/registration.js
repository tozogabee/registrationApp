document.getElementById('registerForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const nickname = document.getElementById('nickname').value.trim();
    const birth_date = document.getElementById('birth_date').value;
    const password = document.getElementById('password').value.trim();

    try {
        const response = await fetch('../backend/registration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, nickname, birth_date, password })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const result = await response.json();

        const messageElement = document.getElementById('message');
        if (result.success) {
            messageElement.className = 'success';
            messageElement.textContent = 'Registration successful!';
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        } else {
            messageElement.textContent = result.message || 'Registration failed.';
            messageElement.className = 'error';
        }
    } catch (error) {
        console.error('Error:', error);
        const messageElement = document.getElementById('message');
        messageElement.className = 'error';
        messageElement.textContent = 'An error occurred. Please try again later.';
    }
});
