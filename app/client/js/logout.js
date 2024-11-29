document.addEventListener('DOMContentLoaded', function () {
    const logoutForm = document.getElementById('logoutForm');
    if (!logoutForm) {
        console.error('Logout form not found in the DOM.');
        return;
    }

    logoutForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to log out?')) {
            return;
        }

        const userId = localStorage.getItem('user_id') ?? sessionStorage.getItem('user_id');

        localStorage.removeItem('user_id');
        sessionStorage.removeItem('user_id');
        if (!userId) {
            alert('User session expired or user not logged in. Redirecting to login.');
            window.location.href = '../login.html';
            return;
        }

        try {
            const response = await fetch(`../backend/logout/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                alert('You have been logged out.');
                window.location.href = 'index.html';
            } else {
                alert('Logout failed: ' + (data.message || 'Unknown error.'));
            }
        } catch (error) {
            console.error('Error during logout:', error);
            alert('An error occurred during logout. Please try again.');
        }
    });
});
