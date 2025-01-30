async function toggleBookmark(thesisId) {
    const bookmarkBtn = document.getElementById(`bookmark-${thesisId}`);
    const isBookmarked = bookmarkBtn.classList.contains('bookmarked');
    
    try {
        const formData = new FormData();
        formData.append('thesis_id', thesisId);
        formData.append('action', isBookmarked ? 'remove' : 'add');
        
        const response = await fetch('../api/bookmark_actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Check if we're on the bookmarks page
            if (window.location.pathname.includes('bookmarks.php') && isBookmarked) {
                // If removing a bookmark from the bookmarks page, reload to remove the card
                window.location.reload();
            } else {
                // Toggle the bookmark state
                bookmarkBtn.classList.toggle('bookmarked');
                
                // Add animation
                bookmarkBtn.classList.add('animate');
                
                // Remove animation class after it completes
                setTimeout(() => {
                    bookmarkBtn.classList.remove('animate');
                }, 300);

                // Optional: Show feedback toast/message
                const action = isBookmarked ? 'removed from' : 'added to';
                showFeedback(`Thesis ${action} bookmarks`, isBookmarked ? 'info' : 'success');
            }
        } else {
            // Handle error
            showFeedback('Failed to update bookmark', 'error');
        }
    } catch (error) {
        console.error('Error toggling bookmark:', error);
        showFeedback('Error updating bookmark', 'error');
    }
}

// Optional feedback function (you can customize this)
function showFeedback(message, type = 'info') {
    // Check if a feedback container exists, if not create one
    let feedbackContainer = document.getElementById('feedback-container');
    if (!feedbackContainer) {
        feedbackContainer = document.createElement('div');
        feedbackContainer.id = 'feedback-container';
        document.body.appendChild(feedbackContainer);
    }

    // Create feedback element
    const feedback = document.createElement('div');
    feedback.className = `feedback-message ${type}`;
    feedback.textContent = message;

    // Add to container
    feedbackContainer.appendChild(feedback);

    // Remove after 3 seconds
    setTimeout(() => {
        feedback.classList.add('fade-out');
        setTimeout(() => {
            feedback.remove();
        }, 300);
    }, 3000);
}