<?php
/**
 * StayNest - Reusable Property Card Template
 * Renders an individual accommodation card with badges, amenities, pricing, and shortlist button.
 *
 * Expects $property array to be defined in caller scope.
 */

if (!isset($property) || !is_array($property)) {
    return;
}

$propId = (int)$property['id'];
$propName = htmlspecialchars($property['name']);
$propCity = htmlspecialchars($property['city']);
$propAddress = htmlspecialchars($property['address']);
$propPrice = '₹' . number_format((float)$property['price']);
$propGender = htmlspecialchars($property['gender']);
$propRating = number_format((float)$property['rating'], 1);
$propDesc = htmlspecialchars($property['description']);
$propImg = !empty($property['image']) ? htmlspecialchars($property['image']) : 'assets/images/property-1.jpg';
$isInterested = !empty($property['is_interested']);

$genderClass = strtolower($property['gender']) === 'male' ? 'male' : (strtolower($property['gender']) === 'female' ? 'female' : 'co-living');
$genderIcon = $property['gender'] === 'Female' ? 'bi-gender-female' : ($property['gender'] === 'Male' ? 'bi-gender-male' : 'bi-people');
?>
<div class="col-md-6 col-lg-4 mb-4 property-col" data-property-id="<?= $propId ?>" id="property-card-col-<?= $propId ?>">
    <div class="property-card">
        <div class="property-image-wrapper">
            <img src="<?= $propImg ?>" alt="<?= $propName ?>" loading="lazy" onerror="this.src='assets/images/property-1.jpg'">
            <span class="property-badge-gender <?= $genderClass ?>">
                <i class="bi <?= $genderIcon ?>"></i> <?= $propGender ?>
            </span>
            <button class="btn-shortlist-heart <?= $isInterested ? 'active' : '' ?>" data-property-id="<?= $propId ?>" title="Shortlist Property" aria-label="Shortlist Property">
                <i class="bi <?= $isInterested ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
            </button>
        </div>
        <div class="property-card-body">
            <h3 class="property-card-title" title="<?= $propName ?>"><?= $propName ?></h3>
            <div class="property-location">
                <i class="bi bi-geo-alt-fill text-primary"></i>
                <span class="text-truncate"><?= $propCity ?> • <?= $propAddress ?></span>
            </div>

            <div class="property-pricing-rating">
                <div class="property-price">
                    <?= $propPrice ?><small>/month</small>
                </div>
                <div class="property-rating">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span><?= $propRating ?></span>
                </div>
            </div>

            <p class="text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6rem;">
                <?= $propDesc ?>
            </p>

            <?php if (!empty($property['amenities']) && is_array($property['amenities'])): ?>
                <div class="property-amenities-pills">
                    <?php foreach (array_slice($property['amenities'], 0, 4) as $am): ?>
                        <span class="amenity-pill">
                            <i class="bi <?= htmlspecialchars($am['icon'] ?? 'bi-check-circle') ?>"></i>
                            <?= htmlspecialchars($am['name'] ?? '') ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="property-card-footer">
                <a href="property-details.php?id=<?= $propId ?>" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                    <span>View Details</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
