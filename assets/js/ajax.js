/**
 * StayNest - Core AJAX Engine
 * Handles async Shortlist/Interest toggling, Shortlist page item removal,
 * badge synchronization, and JSON API communication using fetch().
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Delegated Event Listener for Heart / Shortlist Toggle Buttons
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-shortlist-heart, .btn-shortlist-action');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        const propertyId = btn.getAttribute('data-property-id');
        if (!propertyId) return;

        // Visual feedback during request
        btn.classList.add('disabled');
        const heartIcon = btn.querySelector('i');

        try {
            const response = await fetch('api/interest.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ property_id: propertyId })
            });

            const data = await response.json();

            if (response.status === 401 || (data && data.require_login)) {
                showToast(data.message || 'Please login to shortlist properties.', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                }, 1500);
                return;
            }

            if (!data.success) {
                showToast(data.message || 'Unable to update shortlist.', 'danger');
                return;
            }

            // Successfully added or removed
            if (data.action === 'added') {
                btn.classList.add('active');
                if (heartIcon) {
                    heartIcon.classList.remove('bi-heart');
                    heartIcon.classList.add('bi-heart-fill', 'text-danger');
                }
                const btnText = btn.querySelector('.btn-label');
                if (btnText) btnText.textContent = 'Shortlisted';
                showToast(data.message || 'Property added to shortlist!', 'success');
            } else if (data.action === 'removed') {
                btn.classList.remove('active');
                if (heartIcon) {
                    heartIcon.classList.remove('bi-heart-fill', 'text-danger');
                    heartIcon.classList.add('bi-heart');
                }
                const btnText = btn.querySelector('.btn-label');
                if (btnText) btnText.textContent = 'Add to Shortlist';
                showToast(data.message || 'Property removed from shortlist.', 'info');
            }

            // Synchronize all instances of this property's button on the page
            document.querySelectorAll(`.btn-shortlist-heart[data-property-id="${propertyId}"]`).forEach(otherBtn => {
                if (otherBtn !== btn) {
                    if (data.action === 'added') {
                        otherBtn.classList.add('active');
                        const icon = otherBtn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('bi-heart');
                            icon.classList.add('bi-heart-fill', 'text-danger');
                        }
                    } else {
                        otherBtn.classList.remove('active');
                        const icon = otherBtn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('bi-heart-fill', 'text-danger');
                            icon.classList.add('bi-heart');
                        }
                    }
                }
            });

            // Update Navbar Shortlist Counter
            if (typeof data.shortlist_count !== 'undefined') {
                updateNavbarShortlistBadge(data.shortlist_count);
            }

        } catch (error) {
            console.error('Shortlist error:', error);
            showToast('Network error while updating shortlist. Please try again.', 'danger');
        } finally {
            btn.classList.remove('disabled');
        }
    });

    // 2. Shortlist Page Explicit Remove Button
    document.body.addEventListener('click', async (e) => {
        const removeBtn = e.target.closest('.btn-remove-shortlist');
        if (!removeBtn) return;

        e.preventDefault();
        const propertyId = removeBtn.getAttribute('data-property-id');
        const cardCol = document.getElementById(`shortlist-card-${propertyId}`);

        removeBtn.disabled = true;
        removeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Removing...';

        try {
            const response = await fetch('api/shortlist.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ property_id: propertyId })
            });

            const data = await response.json();

            if (data.success) {
                showToast('Property removed from your shortlist.', 'info');
                
                // Animate removal
                if (cardCol) {
                    cardCol.style.transition = 'all 0.35s ease';
                    cardCol.style.opacity = '0';
                    cardCol.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        cardCol.remove();
                        // Check if remaining cards is 0
                        const remaining = document.querySelectorAll('#shortlist-grid .col-md-6, #shortlist-grid .col-lg-4');
                        if (remaining.length === 0) {
                            const grid = document.getElementById('shortlist-grid');
                            const emptyState = document.getElementById('shortlist-empty-state');
                            if (grid) grid.classList.add('d-none');
                            if (emptyState) emptyState.classList.remove('d-none');
                        }
                    }, 350);
                }

                if (typeof data.shortlist_count !== 'undefined') {
                    updateNavbarShortlistBadge(data.shortlist_count);
                    const headerCount = document.getElementById('shortlist-header-count');
                    if (headerCount) headerCount.textContent = data.shortlist_count;
                }
            } else {
                showToast(data.message || 'Could not remove property.', 'danger');
                removeBtn.disabled = false;
                removeBtn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Remove';
            }
        } catch (err) {
            console.error(err);
            showToast('Failed to connect to server.', 'danger');
            removeBtn.disabled = false;
            removeBtn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Remove';
        }
    });
});

/**
 * Updates navbar badge counter for shortlisted properties
 * @param {number} count 
 */
function updateNavbarShortlistBadge(count) {
    const badges = document.querySelectorAll('.nav-shortlist-badge');
    badges.forEach(badge => {
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    });
}
