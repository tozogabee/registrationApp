fetch('../backend/profile', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json'
    }
})
    .then(response => {
        console.log('Response:', response); // Log the full response object
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('nickname').value = data.nickname;
            document.getElementById('email').value = data.email;
            document.getElementById('birthdate').value = data.birth_date;
            localStorage.setItem('user_id',data.id);
            sessionStorage.setItem('user_id',data.id);
        } else {
            alert('Failed to fetch user data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error); // Log the error for debugging
    });

document.getElementById('profileForm').addEventListener('submit', async function (event) {
    event.preventDefault(); // Prevent default form submission

    // Collect form data
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const nickname = document.getElementById('nickname').value.trim();
    const birthDate = document.getElementById('birthdate').value.trim();


    try {
        // Send updated profile data to the server
        const response = await fetch('../backend/profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email,
                password, // Optional: Only include if provided
                nickname,
                birth_date: birthDate
            })
        });

        // Ensure the response is okay
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        alert('User modified successfully');
        window.location.href = '/client/login.html'; // Redirect to login page
    } catch (error) {
        console.error('Fetch error:', error); // Log the error for debugging
        alert('An error occurred while updating the profile.');
    }
});