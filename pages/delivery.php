<?php
require_once '../include/header.php';
require_once '../classes/connection.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Fetch orders assigned to this support admin
$query = "
    SELECT 
        o.order_id, 
        o.order_number, 
        o.total_amount, 
        o.delivery_status,
        u.full_name AS customer_name,
        u.phone AS customer_phone,
        u.address AS customer_address,
        u.latitude,
        u.longitude
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.support_admin_id = $admin_id
    ORDER BY o.created_at DESC
";
$orders = $mysqli->query($query);
if (!$orders) {
    die('Query Error: ' . $mysqli->error);
}
?>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">My Deliveries</h1>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order No.</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_address']); ?></td>
                            <td><?php echo number_format($row['total_amount'], 2); ?> XAF</td>
                            <td>
                                <span class="label label-<?php 
                                    echo $row['delivery_status'] == 'delivered' ? 'success' : 
                                        ($row['delivery_status'] == 'out_for_delivery' ? 'info' : 'default'); ?>">
                                    <?php echo ucfirst($row['delivery_status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-info map-btn" 
                                        data-lat="<?php echo $row['latitude']; ?>" 
                                        data-lng="<?php echo $row['longitude']; ?>" 
                                        data-address="<?php echo htmlspecialchars($row['customer_address']); ?>">
                                    View Map
                                </button>

                                <?php if ($row['delivery_status'] != 'delivered'): ?>
                                    <button class="btn btn-xs btn-success deliver-btn" data-order-id="<?php echo $row['order_id']; ?>">
                                        Delivered
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No assigned deliveries.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Live Delivery Route</h4>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 500px; width: 100%; border-radius: 8px;"></div>
                <small class="text-muted">Your location updates automatically every 10 seconds.</small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</div> <!-- /#wrapper -->

<!-- JS -->
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/metisMenu.min.js"></script>
<script src="../js/startmin.js"></script>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- Leaflet Routing Machine -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

<script>
let map, routingControl, adminMarker, trackingInterval;

// Open map modal and start live tracking
$('.map-btn').on('click', function() {
    const custLat = parseFloat($(this).data('lat'));
    const custLng = parseFloat($(this).data('lng'));
    const custAddress = $(this).data('address');

    $('#mapModal').modal('show');

    setTimeout(() => {
        if (!navigator.geolocation) {
            alert('Your browser does not support location tracking.');
            return;
        }

        navigator.geolocation.getCurrentPosition(initMap, () => alert('Unable to fetch your location.'));

        function initMap(position) {
            const adminLoc = [position.coords.latitude, position.coords.longitude];
            const customerLoc = [custLat, custLng];

            // Initialize map
            map = L.map('map').setView(adminLoc, 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Admin marker
            adminMarker = L.marker(adminLoc, {icon: L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                iconSize: [32, 32]
            })}).addTo(map).bindPopup('<b>Your Location</b>').openPopup();

            // Customer marker
            L.marker(customerLoc).addTo(map).bindPopup('<b>Customer</b><br>' + custAddress);

            // Route
            routingControl = L.Routing.control({
                waypoints: [L.latLng(adminLoc[0], adminLoc[1]), L.latLng(customerLoc[0], customerLoc[1])],
                routeWhileDragging: false,
                draggableWaypoints: false,
                addWaypoints: false
            }).addTo(map);

            // Live tracking
            trackingInterval = setInterval(() => {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const newLoc = [pos.coords.latitude, pos.coords.longitude];
                    adminMarker.setLatLng(newLoc);
                    routingControl.setWaypoints([L.latLng(newLoc[0], newLoc[1]), L.latLng(customerLoc[0], customerLoc[1])]);

                    // Update admin location in DB
                    $.post('../include/delivery.php', {
                        action: 'update_location',
                        latitude: newLoc[0],
                        longitude: newLoc[1]
                    });
                });
            }, 10000);
        }
    }, 400);
});

// Stop tracking when modal closes
$('#mapModal').on('hidden.bs.modal', function () {
    if (map) map.remove();
    map = null;
    if (trackingInterval) clearInterval(trackingInterval);
    trackingInterval = null;
});

// AJAX: Mark as delivered
$('.deliver-btn').on('click', function() {
    const button = $(this);
    const orderId = button.data('order-id');

    if (!confirm('Mark this order as delivered?')) return;

    $.ajax({
        url: '../include/delivery.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'mark_delivered', order_id: orderId },
        success: function(response) {
            if (response.status === 'success') {
                button.closest('tr').find('td:nth-child(7) .label')
                    .removeClass('label-default label-info')
                    .addClass('label-success')
                    .text('Delivered');
                button.remove();
                alert('Success: ' + response.message);
            } else {
                alert('Warning: ' + response.message);
            }
        },
        error: function() {
            alert('Server error occurred.');
        }
    });
});
</script>

</body>
</html>
