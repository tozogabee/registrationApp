document.addEventListener('DOMContentLoaded', function () {
    // Get the user ID from localStorage (or sessionStorage)
    const userId = localStorage.getItem('userId');

    if (!userId) {
        alert('User not logged in. Redirecting to login page.');
        window.location.href = '../login.html'; // Redirect to login page
        return;
    }

    // Fetch user details from the server
    fetch(`http://localhost:8080/backend/getProfile/${userId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate the form fields with user data
            document.getElementById('nickname').value = data.user.nickname || '';
            document.getElementById('email').value = data.user.email || '';
        } else {
            alert('Failed to fetch user data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error fetching user data:', error);
    });
});
