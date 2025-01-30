document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const thesisCards = document.querySelectorAll('.thesis-card');

    function filterTheses() {
        const searchTerm = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        const status = statusFilter.value;
        let hasVisibleCards = false;

        thesisCards.forEach(card => {
            const title = card.dataset.title;
            const authors = card.dataset.authors;
            const keywords = card.dataset.keywords;
            const cardCategory = card.dataset.category;
            const cardStatus = card.dataset.status;

            const matchesSearch = title.includes(searchTerm) || 
                               authors.includes(searchTerm) || 
                               (keywords && keywords.includes(searchTerm));
            const matchesCategory = category === 'all' || cardCategory === category;
            const matchesStatus = status === 'all' || cardStatus === status;

            const shouldShow = matchesSearch && matchesCategory && matchesStatus;
            card.style.display = shouldShow ? '' : 'none';
            if (shouldShow) hasVisibleCards = true;
        });

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
    statusFilter.addEventListener('change', filterTheses);
});

const modal = document.getElementById('abstractModal');
const span = document.getElementsByClassName('close')[0];

function showAbstract(title, abstract) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalAbstract').textContent = abstract;
    modal.style.display = 'block';
}

span.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

