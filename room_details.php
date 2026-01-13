<?php
require_once __DIR__ . '/includes/functions.php';

global $pdo;

$room_id = (int)($_GET['room_id'] ?? 0);
$check_in = $_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day'));
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+2 days'));

if (!$room_id) {
    header('Location: index.php');
    exit;
}

// Get room details
$stmt = $pdo->prepare("
    SELECT r.*, rt.name AS room_type_name
    FROM rooms r
    LEFT JOIN room_types rt ON r.type_id = rt.id
    WHERE r.id = ?
");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: index.php');
    exit;
}

// Get gallery
$gallery_stmt = $pdo->prepare("SELECT * FROM room_gallery WHERE room_id = ? ORDER BY id ASC");
$gallery_stmt->execute([$room_id]);
$gallery = $gallery_stmt->fetchAll();

// Calculate nights and total
$nights = max(1, (strtotime($check_out) - strtotime($check_in)) / 86400);
$total_price = ($room['price_per_night'] ?? 5000) * $nights;

$page_title = 'Room Details - Room ' . htmlspecialchars($room['room_number']);
include 'includes/header.php';
?>

<div class="container py-5">
    <a href="index.php?check_in=<?= htmlspecialchars($check_in) ?>&check_out=<?= htmlspecialchars($check_out) ?>" class="btn btn-outline-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Back to Rooms
    </a>

    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4">Room <?= htmlspecialchars($room['room_number']) ?> - <?= htmlspecialchars($room['room_type_name'] ?? 'Standard') ?></h2>
            
            <!-- Gallery -->
            <?php if (!empty($gallery)): ?>
                <div id="roomGallery" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-interval="4000" style="max-height: 500px;">
                    <div class="carousel-inner">
                        <?php foreach ($gallery as $index => $item): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <?php if ($item['is_video']): ?>
                                    <video class="d-block w-100" style="max-height: 500px; object-fit: cover;" controls loop muted playsinline>
                                        <source src="<?= htmlspecialchars($item['file_path']) ?>" type="video/mp4">
                                    </video>
                                <?php else: ?>
                                    <img src="<?= htmlspecialchars($item['file_path']) ?>" class="d-block w-100" style="max-height: 500px; object-fit: cover;" alt="Room Image">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($gallery) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#roomGallery" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#roomGallery" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php elseif ($room['image']): ?>
                <img src="<?= htmlspecialchars($room['image']) ?>" class="img-fluid rounded mb-4" style="max-height: 500px; width: 100%; object-fit: cover;" alt="Room Image">
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center mb-4" style="height: 400px;">
                    <i class="fas fa-bed fa-5x text-muted"></i>
                </div>
            <?php endif; ?>
            
            <!-- Description -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-3">Description</h4>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($room['description'] ?? 'Comfortable room with all basic amenities.')) ?></p>
                    
                    <?php if (!empty($room['amenities'])): ?>
                        <hr>
                        <h5 class="mb-3">Amenities</h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($room['amenities'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Room Features -->
            <?php if (!empty($room['has_bedroom']) || !empty($room['has_bathroom']) || !empty($room['has_toilet']) || !empty($room['has_kitchen']) || !empty($room['has_living_room']) || !empty($room['has_balcony'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Room Features</h4>
                        <div class="row g-3">
                            <?php if (!empty($room['has_bedroom'])): ?>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-bed text-primary"></i> Bedroom</h6>
                                    <?php if (!empty($room['bedroom_description'])): ?>
                                        <p class="text-muted small"><?= nl2br(htmlspecialchars($room['bedroom_description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($room['has_bathroom'])): ?>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-shower text-primary"></i> Bathroom</h6>
                                    <?php if (!empty($room['bathroom_description'])): ?>
                                        <p class="text-muted small"><?= nl2br(htmlspecialchars($room['bathroom_description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($room['has_toilet'])): ?>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-toilet text-primary"></i> Toilet</h6>
                                    <?php if (!empty($room['toilet_description'])): ?>
                                        <p class="text-muted small"><?= nl2br(htmlspecialchars($room['toilet_description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($room['has_kitchen'])): ?>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-utensils text-primary"></i> Kitchen</h6>
                                    <?php if (!empty($room['kitchen_description'])): ?>
                                        <p class="text-muted small"><?= nl2br(htmlspecialchars($room['kitchen_description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($room['has_living_room'])): ?>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-couch text-primary"></i> Living Room</h6>
                                    <?php if (!empty($room['living_room_description'])): ?>
                                        <p class="text-muted small"><?= nl2br(htmlspecialchars($room['living_room_description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($room['has_balcony'])): ?>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-door-open text-primary"></i> Balcony</h6>
                                    <?php if (!empty($room['balcony_description'])): ?>
                                        <p class="text-muted small"><?= nl2br(htmlspecialchars($room['balcony_description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow sticky-top" style="top: 100px;">
                <div class="card-body">
                    <h4 class="card-title mb-4">Booking Information</h4>
                    
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Price per night:</span>
                            <strong>KSh <?= number_format($room['price_per_night'] ?? 5000) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Check-in:</span>
                            <strong><?= date('M d, Y', strtotime($check_in)) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Check-out:</span>
                            <strong><?= date('M d, Y', strtotime($check_out)) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Nights:</span>
                            <strong><?= $nights ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total:</span>
                            <strong class="text-success fs-4">KSh <?= number_format($total_price) ?></strong>
                        </div>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="/WAYSIDE/dashboard/guest/book_room.php?room_id=<?= $room_id ?>&check_in=<?= htmlspecialchars($check_in) ?>&check_out=<?= htmlspecialchars($check_out) ?>" class="btn btn-success btn-lg w-100 mb-3">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </a>
                    <?php else: ?>
                        <a href="/WAYSIDE/login.php?redirect=<?= urlencode('/WAYSIDE/dashboard/guest/book_room.php?room_id=' . $room_id . '&check_in=' . $check_in . '&check_out=' . $check_out) ?>" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="fas fa-sign-in-alt"></i> Login to Book
                        </a>
                    <?php endif; ?>
                    
                    <a href="index.php?check_in=<?= htmlspecialchars($check_in) ?>&check_out=<?= htmlspecialchars($check_out) ?>" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-search"></i> View Other Rooms
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-loop videos
document.addEventListener('DOMContentLoaded', function() {
    const videos = document.querySelectorAll('video');
    videos.forEach(video => {
        video.addEventListener('ended', function() {
            this.currentTime = 0;
            this.play();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
