<?php
/**
 * StayNest - Reusable Footer Component
 * Displays platform branding, links, contact details, copyright, and global scripts.
 */
?>
<footer class="footer-staynest">
    <div class="container">
        <div class="row g-4 mb-5">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-houses-fill fs-2 text-success"></i>
                    <span class="fs-4 fw-bold text-white">StayNest</span>
                </div>
                <p class="text-secondary mb-4" style="max-width: 320px;">
                    Helping students find safe, comfortable, and affordable PG accommodations near their college campuses and work hubs.
                </p>
                <div class="d-flex gap-2">
                    <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="social-circle-btn" aria-label="Twitter">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-circle-btn" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="social-circle-btn" aria-label="LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-circle-btn" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5>Explore</h5>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="properties.php">Find PG</a></li>
                    <li><a href="properties.php?view=react">React Explorer</a></li>
                    <li><a href="shortlist.php">Shortlisted PGs</a></li>
                    <li><a href="index.php#about">About Platform</a></li>
                    <li><a href="index.php#contact">Contact Support</a></li>
                </ul>
            </div>

            <!-- Top Student Cities -->
            <div class="col-lg-3 col-md-6">
                <h5>Popular Cities</h5>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="properties.php?city=Bengaluru">PGs in Bengaluru</a></li>
                    <li><a href="properties.php?city=Dharwad">PGs in Dharwad</a></li>
                    <li><a href="properties.php?city=Hubballi">PGs in Hubballi</a></li>
                    <li><a href="properties.php?city=Mysuru">PGs in Mysuru</a></li>
                    <li><a href="properties.php?city=Hyderabad">PGs in Hyderabad</a></li>
                    <li><a href="properties.php?city=Pune">PGs in Pune</a></li>
                </ul>
            </div>

            <!-- Support & Contact -->
            <div class="col-lg-3 col-md-6">
                <h5>Contact Us</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 text-secondary">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt text-primary fs-5 mt-1"></i>
                        <span>Campus Innovation Hub, 100 Ft Road, Bengaluru, Karnataka 560038</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-telephone text-success fs-5"></i>
                        <span>+91 91234 56789</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope text-warning fs-5"></i>
                        <span>support@staynest.in</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="pt-4 border-top border-secondary border-opacity-25 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-secondary small">
            <div>
                © 2026 StayNest. All rights reserved. Built for Academic & Engineering Excellence.
            </div>
            <div class="d-flex gap-3">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Security</a>
            </div>
        </div>
    </div>
</footer>

<!-- Global Toast Container -->
<div class="toast-container-custom toast-container position-fixed bottom-0 end-0 p-3"></div>

<!-- Core JS Assets: Bootstrap 5 Bundle + StayNest Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/ajax.js"></script>
