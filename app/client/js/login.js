// Handle form submission
document.getElementById('loginForm').addEventListener('submit', async function (event) {
    event.preventDefault(); // Prevent default form submission

    // Get form data
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
    
        const result = await response.json(); // Parse JSON response
    
        const messageElement = document.getElementById('message');
        if (result.success) {
            //messageElement.style.color = 'green';
            messageElement.className = 'success';
            messageElement.textContent = `Welcome, ${result.nickname}! Your birth date is ${result.birth_date}.`;
        } else {
            //messageElement.style.color = 'red';
            messageElement.className = 'error';
            messageElement.textContent = result.message || 'Login failed.';
        }
    } catch (error) {
        console.error('Error:', error);
        const messageElement = document.getElementById('message');
        //messageElement.style.color = 'red';
        messageElement.className = 'error';
        messageElement.textContent = 'An error occurred. Please try again later.';
    }
    
});
