import React, { useState, useEffect, useCallback } from 'react';
import PropertyFilters from './components/PropertyFilters.jsx';
import PropertyList from './components/PropertyList.jsx';

const INITIAL_FILTERS = {
  city: 'All',
  budget: 'All',
  gender: 'All',
  rating: '0',
  search: '',
  sort: ''
};

export default function App() {
  const [filters, setFilters] = useState(() => {
    // Read query params from browser location if provided
    const params = new URLSearchParams(window.location.search);
    return {
      city: params.get('city') || 'All',
      budget: params.get('budget') || 'All',
      gender: params.get('gender') || 'All',
      rating: params.get('rating') || '0',
      search: params.get('search') || '',
      sort: params.get('sort') || ''
    };
  });

  const [properties, setProperties] = useState([]);
  const [totalCount, setTotalCount] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchProperties = useCallback(async () => {
    setLoading(true);
    setError(null);

    const queryParams = new URLSearchParams({
      city: filters.city,
      budget: filters.budget,
      gender: filters.gender,
      rating: filters.rating,
      search: filters.search.trim(),
      sort: filters.sort
    });

    try {
      const res = await fetch(`api/properties.php?${queryParams.toString()}`);
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      const data = await res.json();

      if (data.success) {
        setProperties(data.properties || []);
        setTotalCount(data.count || 0);
      } else {
        setError(data.message || 'Failed to retrieve accommodations.');
      }
    } catch (err) {
      console.error('Fetch error:', err);
      setError('Unable to load properties from API. Please verify server connection.');
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    const timer = setTimeout(() => {
      fetchProperties();
    }, 200);
    return () => clearTimeout(timer);
  }, [fetchProperties]);

  const handleFilterChange = (partial) => {
    setFilters((prev) => ({ ...prev, ...partial }));
  };

  const handleResetFilters = () => {
    setFilters(INITIAL_FILTERS);
  };

  const handleToggleShortlist = (propertyId, isAdded, newCount) => {
    setProperties((prev) =>
      prev.map((p) =>
        p.id === propertyId ? { ...p, is_interested: isAdded } : p
      )
    );
    // Update navbar badge if available
    const navBadges = document.querySelectorAll('.nav-shortlist-badge');
    navBadges.forEach((badge) => {
      badge.textContent = newCount;
      if (newCount > 0) {
        badge.classList.remove('d-none');
      } else {
        badge.classList.add('d-none');
      }
    });
  };

  return (
    <div className="react-property-explorer">
      <div className="row g-4">
        {/* Left Filter Sidebar */}
        <div className="col-lg-3">
          <PropertyFilters
            filters={filters}
            onChange={handleFilterChange}
            onReset={handleResetFilters}
          />
        </div>

        {/* Right Listings Column */}
        <div className="col-lg-9">
          {/* Top Bar */}
          <div className="bg-white p-3 rounded-4 border shadow-sm mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div className="d-flex align-items-center gap-2">
              <span className="fw-bold text-dark fs-6">
                {loading ? 'Searching residences...' : `${totalCount} Accommodations Found`}
              </span>
              {loading && <span className="spinner-border spinner-border-sm text-primary"></span>}
            </div>

            <div className="d-flex align-items-center gap-2">
              <label htmlFor="react-sort-select" className="small text-muted text-nowrap fw-semibold">
                Sort:
              </label>
              <select
                id="react-sort-select"
                className="form-select form-select-sm"
                style={{ minWidth: '170px' }}
                value={filters.sort}
                onChange={(e) => handleFilterChange({ sort: e.target.value })}
              >
                <option value="">Recommended</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="rating_high">Top Rated First</option>
              </select>
            </div>
          </div>

          {/* Cards Grid */}
          <PropertyList
            properties={properties}
            loading={loading}
            error={error}
            onRetry={fetchProperties}
            onResetFilters={handleResetFilters}
            onToggleShortlist={handleToggleShortlist}
          />
        </div>
      </div>
    </div>
  );
}
