document.getElementById('loginForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    try {
        const response = await fetch('../backend/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
    
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
    
        const result = await response.json();
    
        const messageElement = document.getElementById('message');
        if (result.success) {
            localStorage.setItem('userId', result.userId);
            sessionStorage.setItem('user_id',result.userId);
            messageElement.className = 'success';
            messageElement.textContent = `Welcome, ${result.nickname}! Your birth date is ${result.birth_date}.`;
            setTimeout(() => {
                window.location.href = 'profile.html';
            }, 2000);
        } else if (result.message === 'User is already logged in') {
            // User already logged in
            localStorage.setItem('userId', result.userId);
            sessionStorage.setItem('user_id',result.userId);
            messageElement.className = 'info';
            messageElement.textContent = 'You are already logged in. Redirecting to your profile...';
            setTimeout(() => {
                window.location.href = 'profile.html';
            }, 3000);
        } else {
            messageElement.className = 'error';
            messageElement.textContent = result.message || 'Login failed.';
        }
    } catch (error) {
        console.error('Error:', error);
        const messageElement = document.getElementById('message');
        messageElement.className = 'error';
        messageElement.textContent = 'An error occurred. Please try again later.';
    }
    
});
