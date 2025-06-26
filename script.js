// Animasi dan Efek Interaktif
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling untuk navigasi
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });

    // Validasi form dinamis
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });

        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }

    // Search functionality untuk tabel kontak
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterContacts(this.value);
        });
    }

    // Efek hover pada feature cards
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Loading animation
    showLoadingAnimation();
});

// Fungsi validasi form
function validateForm() {
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    let isValid = true;

    // Validasi nama
    if (!name.value.trim()) {
        showError(name, 'Nama harus diisi');
        isValid = false;
    } else if (name.value.trim().length < 2) {
        showError(name, 'Nama minimal 2 karakter');
        isValid = false;
    } else {
        showSuccess(name);
    }

    // Validasi email
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.value.trim()) {
        showError(email, 'Email harus diisi');
        isValid = false;
    } else if (!emailPattern.test(email.value)) {
        showError(email, 'Format email tidak valid');
        isValid = false;
    } else {
        showSuccess(email);
    }

    // Validasi nomor telepon
    const phonePattern = /^[0-9+\-\s()]+$/;
    if (!phone.value.trim()) {
        showError(phone, 'Nomor telepon harus diisi');
        isValid = false;
    } else if (!phonePattern.test(phone.value)) {
        showError(phone, 'Format nomor telepon tidak valid');
        isValid = false;
    } else {
        showSuccess(phone);
    }

    return isValid;
}

// Validasi field individual
function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;

    switch(fieldName) {
        case 'name':
            if (!value) {
                showError(field, 'Nama harus diisi');
            } else if (value.length < 2) {
                showError(field, 'Nama minimal 2 karakter');
            } else {
                showSuccess(field);
            }
            break;

        case 'email':
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!value) {
                showError(field, 'Email harus diisi');
            } else if (!emailPattern.test(value)) {
                showError(field, 'Format email tidak valid');
            } else {
                showSuccess(field);
            }
            break;

        case 'phone':
            const phonePattern = /^[0-9+\-\s()]+$/;
            if (!value) {
                showError(field, 'Nomor telepon harus diisi');
            } else if (!phonePattern.test(value)) {
                showError(field, 'Format nomor telepon tidak valid');
            } else {
                showSuccess(field);
            }
            break;
    }
}

// Tampilkan error
function showError(field, message) {
    field.style.borderColor = '#e74c3c';
    removeMessage(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.9rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// Tampilkan success
function showSuccess(field) {
    field.style.borderColor = '#4CAF50';
    removeMessage(field);
}

// Hapus pesan error/success
function removeMessage(field) {
    const existingMessage = field.parentNode.querySelector('.error-message');
    if (existingMessage) {
        existingMessage.remove();
    }
}

// Filter kontak dalam tabel
function filterContacts(searchTerm) {
    const table = document.querySelector('.contacts-table');
    if (!table) return;

    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) { // Skip header row
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent.toLowerCase();
            if (cellText.includes(searchTerm.toLowerCase())) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

// Loading animation
function showLoadingAnimation() {
    const elements = document.querySelectorAll('.form-container, .table-container, .about-content');
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.6s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 200);
    });
}

// Konfirmasi hapus kontak
function confirmDelete(name) {
    return confirm(`Apakah Anda yakin ingin menghapus kontak "${name}"?`);
}

// Format nomor telepon otomatis
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.startsWith('62')) {
        value = '+' + value;
    } else if (value.startsWith('0')) {
        value = '+62' + value.substring(1);
    }
    
    input.value = value;
}

// Auto-resize textarea
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// Efek parallax sederhana
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const parallax = document.querySelector('.hero');
    
    if (parallax) {
        const speed = scrolled * 0.5;
        parallax.style.transform = `translateY(${speed}px)`;
    }
});

// Dark mode toggle (bonus feature)
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
}

// Load dark mode preference
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}

// Animasi counter untuk statistik
function animateCounter(element, target) {
    let current = 0;
    const increment = target / 100;
    const timer = setInterval(() => {
        current += increment;
        element.textContent = Math.floor(current);
        
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        }
    }, 20);
}

// Intersection Observer untuk animasi scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe semua elemen yang perlu animasi
document.querySelectorAll('.feature-card, .hero-content').forEach(el => {
    observer.observe(el);
});

// Notifikasi toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 2rem;
        background: ${type === 'success' ? '#4CAF50' : '#e74c3c'};
        color: white;
        border-radius: 10px;
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
