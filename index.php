<?php
require_once __DIR__ . '/includes/functions.php';

$check_in  = $_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day'));
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+2 days')); // ← fixed the typo here
$search_performed = false;
$available_rooms = [];

global $pdo;

try {
    // Search for available rooms
    if (!empty($_GET['check_in']) && !empty($_GET['check_out'])) {
        $search_performed = true;
        $check_in  = $_GET['check_in'];
        $check_out = $_GET['check_out'];

        if (strtotime($check_out) > strtotime($check_in)) {
            $stmt = $pdo->prepare("
                SELECT r.*, COALESCE(rt.name, 'Standard') AS room_type_name
                FROM rooms r
                LEFT JOIN room_types rt ON r.type_id = rt.id
                WHERE r.status = 'available'
                  AND r.id NOT IN (
                    SELECT room_id FROM bookings 
                    WHERE booking_status NOT IN ('cancelled','checked_out')
                      AND (
                        (check_in < ? AND check_out > ?) OR
                        (check_in < ? AND check_out > ?) OR
                        (check_in >= ? AND check_out <= ?)
                      )
                  )
            ");
            $stmt->execute([
                $check_out, $check_in,
                $check_out, $check_in,
                $check_in,  $check_out
            ]);
            $available_rooms = $stmt->fetchAll();
        }
    }

    // If no search → show featured rooms
    if (!$search_performed) {
        $stmt = $pdo->query("
            SELECT r.*, COALESCE(rt.name, 'Standard') AS room_type_name
            FROM rooms r
            LEFT JOIN room_types rt ON r.type_id = rt.id
            WHERE r.status = 'available'
            ORDER BY r.room_number
            LIMIT 6
        ");
        $available_rooms = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $available_rooms = []; // silently continue if any table is missing
}

$page_title = 'Wayside Airbnb';
include 'includes/header.php';
?>

<div class="bg-primary text-white py-5 mb-5 text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to Wayside Airbnb</h1>
        <p class="lead">Comfortable & Affordable Stays in Kajiado</p>
    </div>
</div>

<div class="container">

    <!-- Search Form -->
    <div class="card shadow mb-5">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Check-in Date</label>
                    <input type="date" name="check_in" class="form-control form-control-lg"
                           value="<?= htmlspecialchars($check_in) ?>" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Check-out Date</label>
                    <input type="date" name="check_out" class="form-control form-control-lg"
                           value="<?= htmlspecialchars($check_out) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100">Search Rooms</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rooms Grid -->
    <h3 class="mb-4 text-primary fw-bold">
        <?= $search_performed ? 'Available Rooms' : 'Featured Rooms' ?>
    </h3>

    <?php if (empty($available_rooms)): ?>
        <div class="alert alert-info text-center py-5">
            No rooms available for the selected dates. Please try different dates.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($available_rooms as $room): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0" style="cursor: pointer;" onclick="window.location.href='/WAYSIDE/room_details.php?room_id=<?= $room['id'] ?>&check_in=<?= $check_in ?>&check_out=<?= $check_out ?>'">
                        <?php if (!empty($room['image'])): ?>
                            <img src="<?= htmlspecialchars($room['image']) ?>" class="card-img-top" style="height:220px; object-fit:cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px;">
                                <i class="fas fa-bed fa-5x text-muted"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2">
                                Room <?= htmlspecialchars($room['room_number']) ?>
                                <span class="badge bg-info float-end"><?= htmlspecialchars($room['room_type_name']) ?></span>
                            </h5>
                            <p class="text-muted small flex-grow-1">
                                <?= htmlspecialchars($room['description'] ?? 'Comfortable room with all basic amenities') ?>
                            </p>

                            <div class="mt-auto">
                                <h3 class="text-primary mb-3">
                                    KSh <?= number_format($room['price_per_night'] ?? 5000) ?>
                                    <small class="text-muted">/ night</small>
                                </h3>

                                <button class="btn btn-primary btn-lg w-100" onclick="event.stopPropagation(); window.location.href='/WAYSIDE/room_details.php?room_id=<?= $room['id'] ?>&check_in=<?= $check_in ?>&check_out=<?= $check_out ?>'">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>