/**
 * StayNest - Property Search & Filter Controller (AJAX)
 * Dynamically queries /api/properties.php and updates property listing DOM without page reloads.
 */

document.addEventListener('DOMContentLoaded', () => {
    const propertiesContainer = document.getElementById('properties-container');
    if (!propertiesContainer) return; // Only active on properties page

    // Filter controls
    const searchInput = document.getElementById('filter-search');
    const citySelect = document.getElementById('filter-city');
    const budgetRadios = document.querySelectorAll('input[name="filter-budget"]');
    const genderRadios = document.querySelectorAll('input[name="filter-gender"]');
    const ratingRadios = document.querySelectorAll('input[name="filter-rating"]');
    const sortSelect = document.getElementById('filter-sort');
    const btnResetFilters = document.getElementById('btn-reset-filters');
    const resultsCountDisplay = document.getElementById('results-count');
    const loadingIndicator = document.getElementById('properties-loading');
    const emptyState = document.getElementById('properties-empty');

    let debounceTimer = null;

    // Read initial URL params if user arrived from hero search
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('city') && citySelect) {
        citySelect.value = urlParams.get('city');
    }
    if (urlParams.get('budget')) {
        const bRadio = document.querySelector(`input[name="filter-budget"][value="${urlParams.get('budget')}"]`);
        if (bRadio) bRadio.checked = true;
    }
    if (urlParams.get('gender')) {
        const gRadio = document.querySelector(`input[name="filter-gender"][value="${urlParams.get('gender')}"]`);
        if (gRadio) gRadio.checked = true;
    }
    if (urlParams.get('search') && searchInput) {
        searchInput.value = urlParams.get('search');
    }

    /**
     * Gathers all currently selected filter values and triggers fetch.
     */
    function fetchFilteredProperties() {
        const city = citySelect ? citySelect.value : 'All';
        const search = searchInput ? searchInput.value.trim() : '';
        const sort = sortSelect ? sortSelect.value : '';

        let budget = 'All';
        budgetRadios.forEach(r => { if (r.checked) budget = r.value; });

        let gender = 'All';
        genderRadios.forEach(r => { if (r.checked) gender = r.value; });

        let rating = '0';
        ratingRadios.forEach(r => { if (r.checked) rating = r.value; });

        const queryParams = new URLSearchParams({
            city: city,
            budget: budget,
            gender: gender,
            rating: rating,
            search: search,
            sort: sort
        });

        // Show loading state
        if (loadingIndicator) loadingIndicator.classList.remove('d-none');
        if (emptyState) emptyState.classList.add('d-none');
        propertiesContainer.style.opacity = '0.4';

        fetch(`api/properties.php?${queryParams.toString()}`)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                propertiesContainer.style.opacity = '1';
                if (loadingIndicator) loadingIndicator.classList.add('d-none');

                if (!data.success) {
                    showToast(data.message || 'Error fetching properties', 'danger');
                    return;
                }

                renderPropertyCards(data.properties);

                if (resultsCountDisplay) {
                    resultsCountDisplay.textContent = `${data.count} accommodations found`;
                }

                if (data.count === 0) {
                    if (emptyState) emptyState.classList.remove('d-none');
                } else {
                    if (emptyState) emptyState.classList.add('d-none');
                }
            })
            .catch(err => {
                console.error(err);
                propertiesContainer.style.opacity = '1';
                if (loadingIndicator) loadingIndicator.classList.add('d-none');
                showToast('Unable to load properties. Please try again.', 'danger');
            });
    }

    /**
     * Generates HTML for property cards and injects into container
     * @param {Array} properties 
     */
    function renderPropertyCards(properties) {
        if (!properties || properties.length === 0) {
            propertiesContainer.innerHTML = '';
            return;
        }

        const cardsHtml = properties.map(p => {
            const genderClass = p.gender.toLowerCase() === 'male' ? 'male' : (p.gender.toLowerCase() === 'female' ? 'female' : 'co-living');
            const heartActiveClass = p.is_interested ? 'active' : '';
            const heartIconClass = p.is_interested ? 'bi-heart-fill text-danger' : 'bi-heart';

            const amenitiesHtml = (p.amenities || []).slice(0, 4).map(a => `
                <span class="amenity-pill">
                    <i class="bi ${a.icon || 'bi-check-circle'}"></i> ${escapeHtml(a.name)}
                </span>
            `).join('');

            return `
                <div class="col-md-6 col-lg-4 mb-4 property-col" data-property-id="${p.id}">
                    <div class="property-card">
                        <div class="property-image-wrapper">
                            <img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name)}" loading="lazy" onerror="this.src='assets/images/property-1.jpg'">
                            <span class="property-badge-gender ${genderClass}">
                                <i class="bi ${p.gender === 'Female' ? 'bi-gender-female' : (p.gender === 'Male' ? 'bi-gender-male' : 'bi-people')}"></i>
                                ${escapeHtml(p.gender)}
                            </span>
                            <button class="btn-shortlist-heart ${heartActiveClass}" data-property-id="${p.id}" title="Shortlist Property" aria-label="Shortlist Property">
                                <i class="bi ${heartIconClass}"></i>
                            </button>
                        </div>
                        <div class="property-card-body">
                            <h3 class="property-card-title" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</h3>
                            <div class="property-location">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <span class="text-truncate">${escapeHtml(p.city)} • ${escapeHtml(p.address)}</span>
                            </div>
                            
                            <div class="property-pricing-rating">
                                <div class="property-price">
                                    ${escapeHtml(p.formatted_price)}<small>/month</small>
                                </div>
                                <div class="property-rating">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <span>${p.rating.toFixed(1)}</span>
                                </div>
                            </div>

                            <p class="text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6rem;">
                                ${escapeHtml(p.description)}
                            </p>

                            <div class="property-amenities-pills">
                                ${amenitiesHtml}
                            </div>

                            <div class="property-card-footer">
                                <a href="property-details.php?id=${p.id}" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                    <span>View Details</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        propertiesContainer.innerHTML = cardsHtml;
    }

    function escapeHtml(string) {
        if (!string) return '';
        return String(string)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Event Listeners for Filter Controls
    if (citySelect) {
        citySelect.addEventListener('change', fetchFilteredProperties);
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', fetchFilteredProperties);
    }

    budgetRadios.forEach(r => r.addEventListener('change', fetchFilteredProperties));
    genderRadios.forEach(r => r.addEventListener('change', fetchFilteredProperties));
    ratingRadios.forEach(r => r.addEventListener('change', fetchFilteredProperties));

    // Debounced text search
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchFilteredProperties();
            }, 300);
        });
    }

    // Reset Filters Button
    if (btnResetFilters) {
        btnResetFilters.addEventListener('click', (e) => {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            if (citySelect) citySelect.value = 'All';
            const allBudget = document.querySelector('input[name="filter-budget"][value="All"]');
            if (allBudget) allBudget.checked = true;
            const allGender = document.querySelector('input[name="filter-gender"][value="All"]');
            if (allGender) allGender.checked = true;
            const allRating = document.querySelector('input[name="filter-rating"][value="0"]');
            if (allRating) allRating.checked = true;
            if (sortSelect) sortSelect.value = '';

            fetchFilteredProperties();
            showToast('All filters have been reset.', 'info');
        });
    }

    // Initial Load
    fetchFilteredProperties();
});
