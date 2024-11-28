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
    })
    fetch('../backend/profile', {
        method: 'POST',
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
            const messageElement = document.getElementById('message');
            if(data.success) {
                messageElement.className = 'success';
                messageElement.textContent = 'Updated user successfully';
                setTimeout(() => {
                    window.location.href = '/client/index.html';
                }, 2000);
            } else {
                messageElement.className = 'error';
                messageElement.textContent = data.message || 'update failed!';
            }
        })
