import React from 'react';

const CITIES = ['All', 'Bengaluru', 'Dharwad', 'Hubballi', 'Mysuru', 'Hyderabad', 'Pune'];

const BUDGETS = [
  { id: 'All', label: 'Any Budget' },
  { id: 'under_5000', label: 'Under ₹5,000' },
  { id: '5000_7000', label: '₹5,000 – ₹7,000' },
  { id: '7000_10000', label: '₹7,000 – ₹10,000' },
  { id: 'above_10000', label: 'Above ₹10,000' },
];

const GENDERS = [
  { id: 'All', label: 'All Categories' },
  { id: 'Male', label: 'Male PG' },
  { id: 'Female', label: 'Female PG' },
  { id: 'Co-living', label: 'Co-living / Unisex' },
];

const RATINGS = [
  { id: '0', label: 'Any Rating' },
  { id: '4', label: '⭐ 4.0+ Stars' },
  { id: '3', label: '⭐ 3.0+ Stars' },
];

export default function PropertyFilters({ filters, onChange, onReset }) {
  const handleTextChange = (e) => {
    onChange({ search: e.target.value });
  };

  const handleCityChange = (e) => {
    onChange({ city: e.target.value });
  };

  const handleBudgetChange = (value) => {
    onChange({ budget: value });
  };

  const handleGenderChange = (value) => {
    onChange({ gender: value });
  };

  const handleRatingChange = (value) => {
    onChange({ rating: value });
  };

  return (
    <div className="filter-sidebar bg-white p-3 p-md-4 rounded-4 border shadow-sm">
      <div className="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <span className="fw-bold text-dark fs-6">
          <i className="bi bi-funnel text-primary me-1"></i> React Filters
        </span>
        <button
          type="button"
          className="btn btn-sm btn-link text-danger text-decoration-none p-0 fw-semibold"
          onClick={onReset}
        >
          <i className="bi bi-arrow-counterclockwise me-1"></i> Reset
        </button>
      </div>

      {/* 1. Search */}
      <div className="mb-4">
        <label className="filter-group-title text-uppercase small fw-bold text-muted d-block mb-2">
          Keyword Search
        </label>
        <div className="input-group">
          <span className="input-group-text bg-white border-end-0 text-muted">
            <i className="bi bi-search"></i>
          </span>
          <input
            type="text"
            className="form-control border-start-0"
            placeholder="PG Name, College, Area..."
            value={filters.search}
            onChange={handleTextChange}
          />
          {filters.search && (
            <button
              className="btn btn-outline-secondary border-start-0 border"
              type="button"
              onClick={() => onChange({ search: '' })}
            >
              <i className="bi bi-x"></i>
            </button>
          )}
        </div>
      </div>

      {/* 2. City Dropdown */}
      <div className="mb-4">
        <label className="filter-group-title text-uppercase small fw-bold text-muted d-block mb-2">
          City / Location
        </label>
        <select
          className="form-select"
          value={filters.city}
          onChange={handleCityChange}
        >
          {CITIES.map((c) => (
            <option key={c} value={c}>
              {c === 'All' ? 'All Student Hubs' : c}
            </option>
          ))}
        </select>
      </div>

      {/* 3. Budget Radio */}
      <div className="mb-4">
        <label className="filter-group-title text-uppercase small fw-bold text-muted d-block mb-2">
          Monthly Budget
        </label>
        <div className="d-flex flex-column gap-2">
          {BUDGETS.map((b) => (
            <div key={b.id} className="form-check">
              <input
                className="form-check-input"
                type="radio"
                name="react-filter-budget"
                id={`react-budget-${b.id}`}
                checked={filters.budget === b.id}
                onChange={() => handleBudgetChange(b.id)}
              />
              <label className="form-check-label small" htmlFor={`react-budget-${b.id}`}>
                {b.label}
              </label>
            </div>
          ))}
        </div>
      </div>

      {/* 4. Gender Category */}
      <div className="mb-4">
        <label className="filter-group-title text-uppercase small fw-bold text-muted d-block mb-2">
          Accommodation Type
        </label>
        <div className="d-flex flex-column gap-2">
          {GENDERS.map((g) => (
            <div key={g.id} className="form-check">
              <input
                className="form-check-input"
                type="radio"
                name="react-filter-gender"
                id={`react-gender-${g.id}`}
                checked={filters.gender === g.id}
                onChange={() => handleGenderChange(g.id)}
              />
              <label className="form-check-label small" htmlFor={`react-gender-${g.id}`}>
                {g.label}
              </label>
            </div>
          ))}
        </div>
      </div>

      {/* 5. Rating */}
      <div className="mb-2">
        <label className="filter-group-title text-uppercase small fw-bold text-muted d-block mb-2">
          Minimum Rating
        </label>
        <div className="d-flex flex-column gap-2">
          {RATINGS.map((r) => (
            <div key={r.id} className="form-check">
              <input
                className="form-check-input"
                type="radio"
                name="react-filter-rating"
                id={`react-rating-${r.id}`}
                checked={filters.rating === r.id}
                onChange={() => handleRatingChange(r.id)}
              />
              <label className="form-check-label small" htmlFor={`react-rating-${r.id}`}>
                {r.label}
              </label>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
