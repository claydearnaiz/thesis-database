let modal = document.getElementById('loginModal');
let currentUserType = '';

function showLoginModal(userType) {
    modal.style.display = "block";
    currentUserType = userType;
    document.getElementById('modalTitle').textContent = 
        userType === 'admin' ? 'Admin Login' : 'User Login';
    document.getElementById('userType').value = userType;
    document.getElementById('loginForm').reset();
}

function closeLoginModal() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target === modal) {
        closeLoginModal();
    }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    this.submit();
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && modal.style.display === 'block') {
        closeLoginModal();
    }
});

document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    }, 5000);
});

function showThesisDetails(title, abstract, authors) {
    document.getElementById('thesisTitle').textContent = title;
    document.getElementById('thesisAuthors').textContent = authors;
    document.getElementById('thesisAbstract').textContent = abstract;
    document.getElementById('thesisModal').style.display = 'block';
}

function closeThesisModal() {
    document.getElementById('thesisModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('thesisModal')) {
        closeThesisModal();
    }
    if (event.target == document.getElementById('loginModal')) {
        closeLoginModal();
    }
}
