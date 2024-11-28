document.getElementById('logoutForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent the default form submission

    const userId = localStorage.getItem('userId'); // Retrieve userId from localStorage (or sessionStorage)

    if (!userId) {
        alert('User ID not found. Please log in again.');
        window.location.href = '../login.html'; // Redirect to login if userId is missing
        return;
    }

    // Send a POST request to the logout endpoint
    fetch(`/backend/logout/${userId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear stored user data
            localStorage.removeItem('userId');

            // Redirect to the homepage on successful logout
            window.location.href = '../index.html';
        } else {
            alert('Logout failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
