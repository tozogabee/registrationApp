document.addEventListener('DOMContentLoaded', function () {
    const logoutForm = document.getElementById('logoutForm');
    if (!logoutForm) {
        console.error('Logout form not found in the DOM.');
        return;
    }

    logoutForm.addEventListener('submit', async function (e) {
        e.preventDefault(); // Prevent default form submission behavior

        // Optional: Confirm logout action
        if (!confirm('Are you sure you want to log out?')) {
            return;
        }

        //console.log(data);
        // Check if the user ID is available in localStorage or from another source
        const userId = localStorage.getItem('user_id') ?? sessionStorage.getItem('user_id'); // Assuming you store `userId` in localStorage

        if (!userId) {
            alert('User session expired or user not logged in. Redirecting to login.');
            window.location.href = '../login.html'; // Redirect to login page
            return;
        }

        try {
            // Send a POST request to the logout endpoint
            const response = await fetch(`../backend/logout/${userId}`, { // Template literal for URL
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Specify JSON if the backend expects it
                    'X-Requested-With': 'XMLHttpRequest' // Optional: Indicate an AJAX request
                }
            });
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                // Clear stored user data
                localStorage.removeItem('user_id');
                sessionStorage.removeItem('user_id');
                // Redirect to the homepage on successful logout
                alert('You have been logged out.');
                window.location.href = '/client/index.html';
            } else {
                alert('Logout failed: ' + (data.message || 'Unknown error.'));
            }
        } catch (error) {
            console.error('Error during logout:', error);
            alert('An error occurred during logout. Please try again.');
        }
    });
});
