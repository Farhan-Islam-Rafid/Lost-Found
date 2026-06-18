<?php
session_start();
include 'db.php';

// Login protection
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// All products
$result = mysqli_query($conn, "SELECT * FROM products WHERE user_id='$user_id' ORDER BY Id DESC");
$total = mysqli_num_rows($result);

// Count Lost & Found separately
$lost_count  = mysqli_num_rows(mysqli_query($conn, "SELECT Id FROM products WHERE user_id='$user_id' AND type='Lost'"));
$found_count = mysqli_num_rows(mysqli_query($conn, "SELECT Id FROM products WHERE user_id='$user_id' AND type='Found'"));

// Messages
$msgs = mysqli_query($conn, "
    SELECT messages.*, products.name 
    FROM messages 
    JOIN products ON messages.product_id = products.Id
    WHERE products.user_id='$user_id'
    ORDER BY messages.id DESC
");
$msg_count = mysqli_num_rows($msgs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Lost&Found</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    * { box-sizing: border-box; }

    body {
        background: linear-gradient(135deg, #0d6efd, #0944CE);
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
    }

    /* STAT CARDS */
    .stat-card {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 14px;
        color: white;
        padding: 20px;
        text-align: center;
        backdrop-filter: blur(8px);
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .stat-card .number { font-size: 2rem; font-weight: 700; }
    .stat-card .label  { font-size: 0.85rem; opacity: 0.8; }

    /* SEARCH BAR */
    #searchBar {
        border-radius: 50px;
        border: none;
        padding: 10px 20px;
        outline: none;
        width: 100%;
        max-width: 400px;
        font-size: 0.95rem;
    }

    /* FILTER BUTTONS */
    .filter-btn {
        border-radius: 50px;
        padding: 7px 20px;
        font-size: 0.85rem;
        border: 2px solid rgba(255,255,255,0.5);
        color: white;
        background: transparent;
        cursor: pointer;
        transition: 0.2s;
    }
    .filter-btn.active, .filter-btn:hover {
        background: white;
        color: #0d6efd;
        border-color: white;
    }

    /* ITEM CARDS */
    .item-card {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
    }
    .item-card img {
        height: 200px;
        width: 100%;
        object-fit: cover;
    }

    /* MESSAGE SECTION */
    .msg-toggle {
        cursor: pointer;
        user-select: none;
    }
    .msg-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        animation: fadeUp 0.3s ease;
    }
    @keyframes fadeUp {
        from { opacity:0; transform:translateY(10px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* NAV BUTTONS */
    .nav-btn { border-radius: 50px; padding: 9px 22px; font-weight: 500; transition: transform 0.15s; }
    .nav-btn:hover { transform: scale(1.04); }
</style>
</head>
<body>
<div class="container py-5">

    <!-- HEADER -->
    <div class="text-center text-white mb-4">
        <h2 class="fw-bold">👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>'s Dashboard</h2>
        <p class="opacity-75 mb-0">Manage your Lost &amp; Found posts</p>
    </div>

    <!-- NAV BUTTONS -->
    <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
        <a href="add_product.php" class="btn btn-success nav-btn">➕ Add Item</a>
        <a href="index.php"       class="btn btn-light nav-btn">🏠 Home</a>
        <a href="logout.php"      class="btn btn-danger nav-btn">🚪 Logout</a>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="number"><?php echo $total; ?></div>
                <div class="label">Total Posts</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="number text-danger-emphasis"><?php echo $lost_count; ?></div>
                <div class="label">Lost Items</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="number text-success-emphasis"><?php echo $found_count; ?></div>
                <div class="label">Found Items</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="number"><?php echo $msg_count; ?></div>
                <div class="label">Messages</div>
            </div>
        </div>
    </div>

    <!-- MESSAGES (collapsible) -->
    <div class="mb-4">
        <h5 class="text-white msg-toggle" data-bs-toggle="collapse" data-bs-target="#msgSection">
            💬 Messages
            <span class="badge bg-warning text-dark ms-1"><?php echo $msg_count; ?></span>
            <small class="opacity-75 fs-6">(click to toggle)</small>
        </h5>

        <div class="collapse" id="msgSection">
            <?php if ($msg_count > 0): ?>
                <?php while($m = mysqli_fetch_assoc($msgs)): ?>
                <div class="card msg-card p-3 mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong><?php echo htmlspecialchars($m['sender_name']); ?></strong>
                            <span class="text-muted"> → </span>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($m['name']); ?></span>
                        </div>
                        <small class="text-muted"><?php echo $m['created_at']; ?></small>
                    </div>
                    <p class="mb-0 mt-2"><?php echo htmlspecialchars($m['message']); ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-white opacity-75">No messages yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <hr class="border-white opacity-25 mb-4">

    <!-- SEARCH + FILTER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <input type="text" id="searchBar" placeholder="🔍 Search by name..." onkeyup="filterCards()">
        <div class="d-flex gap-2">
            <button class="filter-btn active" onclick="filterType('All',  this)">All</button>
            <button class="filter-btn"        onclick="filterType('Lost', this)">Lost</button>
            <button class="filter-btn"        onclick="filterType('Found',this)">Found</button>
        </div>
    </div>

    <!-- ITEM CARDS -->
    <div class="row" id="itemGrid">

    <?php if ($total > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-4 item-col mb-4"
             data-type="<?php echo $row['type']; ?>"
             data-name="<?php echo strtolower($row['name']); ?>">

            <div class="card item-card h-100">
                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="item image">

                <div class="card-body text-center">
                    <?php if ($row['type'] == "Lost"): ?>
                        <span class="badge bg-danger px-3 py-2">Lost</span>
                    <?php else: ?>
                        <span class="badge bg-success px-3 py-2">Found</span>
                    <?php endif; ?>

                    <h5 class="mt-3 fw-bold"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($row['description']); ?></p>

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="cardpage.php?id=<?php echo $row['Id']; ?>"
                           class="btn btn-primary btn-sm px-3">👁 View</a>

                        <a href="edit_product.php?id=<?php echo $row['Id']; ?>"
                           class="btn btn-warning btn-sm px-3">✏️ Edit</a>

                        <a href="delete_product.php?id=<?php echo $row['Id']; ?>"
                           class="btn btn-danger btn-sm px-3"
                           onclick="return confirmDelete('<?php echo htmlspecialchars($row['name']); ?>')">
                           🗑 Delete
                        </a>
                    </div>
                </div>
            </div>

        </div>
        <?php endwhile; ?>

    <?php else: ?>
        <div class="text-center text-white mt-4 col-12">
            <h4>No Posts Yet 😢</h4>
            <p>Start by adding your first lost or found item.</p>
            <a href="add_product.php" class="btn btn-light rounded-pill px-4 mt-2">➕ Add First Item</a>
        </div>
    <?php endif; ?>

    </div><!-- end row -->

</div><!-- end container -->

<!-- Bootstrap JS (for collapse) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Better delete confirmation
    function confirmDelete(name) {
        return confirm('Delete "' + name + '"? This cannot be undone.');
    }

    // Search filter
    function filterCards() {
        var query = document.getElementById('searchBar').value.toLowerCase();
        document.querySelectorAll('.item-col').forEach(function(col) {
            var name = col.getAttribute('data-name');
            col.style.display = name.includes(query) ? '' : 'none';
        });
    }

    // Type filter (Lost / Found / All)
    var activeType = 'All';
    function filterType(type, btn) {
        activeType = type;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.item-col').forEach(function(col) {
            if (type === 'All' || col.getAttribute('data-type') === type) {
                col.style.display = '';
            } else {
                col.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>