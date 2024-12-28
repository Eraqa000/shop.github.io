document.querySelectorAll('.prev-btn').forEach((button, index) => {
    button.addEventListener('click', function() {
        const cardContainer = document.querySelectorAll('.card-container')[index];
        cardContainer.scrollBy({ left: -cardContainer.offsetWidth, behavior: 'smooth' });
    });
});

document.querySelectorAll('.next-btn').forEach((button, index) => {
    button.addEventListener('click', function() {
        const cardContainer = document.querySelectorAll('.card-container')[index];
        cardContainer.scrollBy({ left: cardContainer.offsetWidth, behavior: 'smooth' });
    });
});

document.querySelectorAll('.tab-button').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.tab-button').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});

document.querySelectorAll('.animated-button').forEach(button => {
    button.addEventListener('click', function() {
        button.style.transform = 'scale(1.2)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 300);
    });
});