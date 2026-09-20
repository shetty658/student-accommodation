import React from 'react';
import PropertyCard from './PropertyCard.jsx';

export default function PropertyList({
  properties,
  loading,
  error,
  onRetry,
  onResetFilters,
  onToggleShortlist
}) {
  if (loading) {
    return (
      <div className="row g-4">
        {[1, 2, 3, 4, 5, 6].map((n) => (
          <div key={n} className="col-md-6 col-lg-4">
            <div className="skeleton-card">
              <div className="skeleton-box" style={{ height: '180px', width: '100%' }}></div>
              <div className="skeleton-box" style={{ height: '24px', width: '70%' }}></div>
              <div className="skeleton-box" style={{ height: '16px', width: '50%' }}></div>
              <div className="skeleton-box mt-auto" style={{ height: '38px', width: '100%' }}></div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  if (error) {
    return (
      <div className="alert alert-danger p-4 rounded-4 shadow-sm text-center my-4">
        <i className="bi bi-exclamation-triangle-fill fs-2 text-danger d-block mb-2"></i>
        <h4 className="fw-bold">Unable to Load Accommodations</h4>
        <p className="text-muted mb-3">{error}</p>
        <button type="button" className="btn btn-outline-danger px-4 rounded-pill" onClick={onRetry}>
          <i className="bi bi-arrow-clockwise me-1"></i> Try Again
        </button>
      </div>
    );
  }

  if (!properties || properties.length === 0) {
    return (
      <div className="text-center py-5 bg-white rounded-4 border shadow-sm my-3 p-4">
        <div className="text-muted mb-3">
          <i className="bi bi-search fs-1"></i>
        </div>
        <h3 className="h4 fw-bold text-dark mb-2">No Matching Accommodations Found</h3>
        <p className="text-muted small mb-4" style={{ maxWidth: '420px', margin: '0 auto' }}>
          We couldn't find any student PGs matching your exact filters. Try broadening your budget, selecting "All Cities", or resetting your search.
        </p>
        <button
          type="button"
          className="btn btn-primary px-4 py-2 rounded-pill fw-semibold"
          onClick={onResetFilters}
        >
          Reset All Filters
        </button>
      </div>
    );
  }

  return (
    <div className="row g-4">
      {properties.map((property) => (
        <PropertyCard
          key={property.id}
          property={property}
          onToggleShortlist={onToggleShortlist}
        />
      ))}
    </div>
  );
}
