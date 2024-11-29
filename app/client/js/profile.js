fetch('../backend/profile', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json'
    }
})
    .then(response => {
        console.log('Response:', response);
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
        console.error('Error:', error);
    });

document.getElementById('profileForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const nickname = document.getElementById('nickname').value.trim();
    const birthDate = document.getElementById('birthdate').value.trim();

    if(email !== '') {
        if (!validateEmail(email)) {
            alert('Please enter a valid email address.');
            return;
        }
    }
    if(birthDate !== '') {
        if (!validateBirthDate(birthDate)) {
            alert('Please enter a valid birth date in the format YYYY-MM-DD.');
            return;
        }
    }

    try {
        const response = await fetch('../backend/profile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email,
                password,
                nickname,
                birth_date: birthDate
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        alert('User modified successfully');
        window.location.href = '/client/login.html';
    } catch (error) {
        console.error('Fetch error:', error);
        alert('An error occurred while updating the profile.');
    }
});

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validateBirthDate(birthDate) {
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(birthDate)) {
        return false;
    }

    const dateParts = birthDate.split('-');
    const year = parseInt(dateParts[0], 10);
    const month = parseInt(dateParts[1], 10);
    const day = parseInt(dateParts[2], 10);

    const dateObject = new Date(year, month - 1, day);

    const now = new Date();
    const hundredYearsAgo = new Date(now.getFullYear() - 100, now.getMonth(), now.getDate());
    const isInAdultAge = new Date(now.getFullYear() - 18,now.getMonth(),now.getDay());
    if (dateObject >= isInAdultAge || dateObject < hundredYearsAgo || dateObject > now) {
        return false;
    }
    return (
        dateObject.getFullYear() === year &&
        dateObject.getMonth() === month - 1 &&
        dateObject.getDate() === day
    );
}