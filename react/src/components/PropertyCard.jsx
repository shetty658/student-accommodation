import React, { useState } from 'react';

export default function PropertyCard({ property, onToggleShortlist }) {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isInterested, setIsInterested] = useState(Boolean(property.is_interested));

  const handleHeartClick = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (isSubmitting) return;

    setIsSubmitting(true);
    try {
      const res = await fetch('api/interest.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ property_id: property.id })
      });
      const data = await res.json();

      if (res.status === 401 || data.require_login) {
        if (window.showToast) {
          window.showToast(data.message || 'Please login to shortlist properties.', 'warning');
        } else {
          alert(data.message || 'Please login to shortlist properties.');
        }
        setTimeout(() => {
          window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
        }, 1200);
        return;
      }

      if (data.success) {
        setIsInterested(data.action === 'added');
        if (window.showToast) {
          window.showToast(data.message, data.action === 'added' ? 'success' : 'info');
        }
        if (onToggleShortlist) {
          onToggleShortlist(property.id, data.action === 'added', data.shortlist_count);
        }
      } else {
        if (window.showToast) {
          window.showToast(data.message || 'Error updating shortlist', 'danger');
        }
      }
    } catch (err) {
      console.error(err);
      if (window.showToast) {
        window.showToast('Network error while updating shortlist.', 'danger');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const genderClass =
    property.gender?.toLowerCase() === 'male'
      ? 'male'
      : property.gender?.toLowerCase() === 'female'
      ? 'female'
      : 'co-living';

  const genderIcon =
    property.gender === 'Female'
      ? 'bi-gender-female'
      : property.gender === 'Male'
      ? 'bi-gender-male'
      : 'bi-people';

  return (
    <div className="col-md-6 col-lg-4 mb-4">
      <div className="property-card h-100">
        <div className="property-image-wrapper position-relative">
          <img
            src={property.image || 'assets/images/property-1.jpg'}
            alt={property.name}
            loading="lazy"
            onError={(e) => {
              e.target.src = 'assets/images/property-1.jpg';
            }}
          />
          <span className={`property-badge-gender ${genderClass}`}>
            <i className={`bi ${genderIcon} me-1`}></i>
            {property.gender}
          </span>
          <button
            type="button"
            className={`btn-shortlist-heart ${isInterested ? 'active' : ''}`}
            onClick={handleHeartClick}
            disabled={isSubmitting}
            title={isInterested ? 'Remove from shortlist' : 'Add to shortlist'}
            aria-label="Shortlist button"
          >
            <i className={`bi ${isInterested ? 'bi-heart-fill text-danger' : 'bi-heart'}`}></i>
          </button>
        </div>

        <div className="property-card-body d-flex flex-column flex-grow-1 p-3">
          <h3 className="property-card-title h6 fw-bold mb-1 text-truncate" title={property.name}>
            {property.name}
          </h3>
          <div className="property-location text-muted small mb-2 text-truncate">
            <i className="bi bi-geo-alt-fill text-primary me-1"></i>
            {property.city} • {property.address}
          </div>

          <div className="property-pricing-rating d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div className="property-price fw-bold text-primary fs-5">
              {property.formatted_price || `₹${Number(property.price).toLocaleString()}`}
              <small className="text-muted fs-6 fw-normal">/mo</small>
            </div>
            <div className="property-rating badge bg-warning-subtle text-dark border border-warning-subtle d-flex align-items-center gap-1">
              <i className="bi bi-star-fill text-warning"></i>
              <span>{Number(property.rating).toFixed(1)}</span>
            </div>
          </div>

          <p
            className="text-muted small mb-3 flex-grow-1"
            style={{
              display: '-webkit-box',
              WebkitLineClamp: 2,
              WebkitBoxOrient: 'vertical',
              overflow: 'hidden',
              minHeight: '2.5rem'
            }}
          >
            {property.description}
          </p>

          <div className="property-amenities-pills d-flex flex-wrap gap-1 mb-3">
            {(property.amenities || []).slice(0, 4).map((am, idx) => (
              <span key={idx} className="amenity-pill badge bg-light text-secondary border">
                <i className={`bi ${am.icon || 'bi-check-circle'} me-1 text-success`}></i>
                {am.name}
              </span>
            ))}
          </div>

          <div className="property-card-footer mt-auto pt-2">
            <a
              href={`property-details.php?id=${property.id}`}
              className="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2"
            >
              <span>View Details</span>
              <i className="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
