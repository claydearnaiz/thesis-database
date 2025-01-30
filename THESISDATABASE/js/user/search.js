document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const thesisCards = document.querySelectorAll('.thesis-card');

    function filterTheses() {
        const searchTerm = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        let hasVisibleCards = false;

        thesisCards.forEach(card => {
            const title = card.dataset.title;
            const authors = card.dataset.authors;
            const keywords = card.dataset.keywords;
            const cardCategory = card.dataset.category;

            const matchesSearch = title.includes(searchTerm) || 
                               authors.includes(searchTerm) || 
                               (keywords && keywords.includes(searchTerm));
            const matchesCategory = category === 'all' || cardCategory === category;

            const shouldShow = matchesSearch && matchesCategory;
            card.style.display = shouldShow ? '' : 'none';
            if (shouldShow) hasVisibleCards = true;
        });

        // no cards = no result message
        const noRecords = document.querySelector('.no-records');
        if (!hasVisibleCards) {
            if (!document.querySelector('.no-results-message')) {
                const noResults = document.createElement('div');
                noResults.className = 'no-records no-results-message';
                noResults.innerHTML = `
                    <i class="fas fa-search"></i>
                    <p>No matching thesis found</p>
                `;
                document.querySelector('.thesis-grid').appendChild(noResults);
            }
        } else {
            const noResults = document.querySelector('.no-results-message');
            if (noResults) noResults.remove();
        }
    }

    searchInput.addEventListener('input', filterTheses);
    categoryFilter.addEventListener('change', filterTheses);
});

function showAbstract(title, abstract, author) {
    const modal = document.getElementById('abstractModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalAbstract = document.getElementById('modalAbstract');
    const modalAuthor = document.getElementById('modalAuthor');
    
    modalTitle.textContent = title;
    modalAbstract.textContent = abstract;
    modalAuthor.textContent = author;
    
    modal.classList.add('show');
    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = function() {
        modal.classList.remove('show');
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    }
}