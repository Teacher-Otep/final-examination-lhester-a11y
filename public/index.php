<?php
$host    = '127.0.0.1';
$db      = 'cc111smsdb';
$user    = 'root';
$pass    = '';
$port    = '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$successMsg = '';
$errorMsg   = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    try {
        if ($action == 'create') {
            $name       = trim($_POST['name']       ?? '');
            $surname    = trim($_POST['surname']    ?? '');
            $middlename = trim($_POST['middlename'] ?? '');
            $address    = trim($_POST['address']    ?? '');
            $contact    = trim($_POST['contact']    ?? '');

            if (empty($name) || empty($surname)) {
                $errorMsg = 'First name and last name are required.';
            } else {
                $sql = "INSERT INTO students (name, surname, middlename, address, contact_number)
                        VALUES (:name, :surname, :middlename, :address, :contact)";
                $pdo->prepare($sql)->execute([
                    ':name'       => $name,
                    ':surname'    => $surname,
                    ':middlename' => $middlename,
                    ':address'    => $address,
                    ':contact'    => $contact
                ]);
                $successMsg = 'Student registered successfully.';
            }

        } elseif ($action == 'update') {
            $id      = intval($_POST['id']      ?? 0);
            $name    = trim($_POST['name']    ?? '');
            $surname = trim($_POST['surname'] ?? '');

            if ($id <= 0) {
                $errorMsg = 'Please enter a valid Student ID.';
            } elseif (empty($name) && empty($surname)) {
                $errorMsg = 'Provide at least a new first name or last name.';
            } else {
                $fields = [];
                $params = [':id' => $id];
                if (!empty($name))    { $fields[] = 'name = :name';       $params[':name']    = $name; }
                if (!empty($surname)) { $fields[] = 'surname = :surname'; $params[':surname'] = $surname; }

                $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                if ($stmt->rowCount() === 0) {
                    $errorMsg = "No student found with ID $id.";
                } else {
                    $successMsg = "Student ID $id updated successfully.";
                }
            }

        } elseif ($action == 'delete') {
            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                $errorMsg = 'Please enter a valid Student ID.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
                $stmt->execute([':id' => $id]);

                if ($stmt->rowCount() === 0) {
                    $errorMsg = "No student found with ID $id.";
                } else {
                    $successMsg = "Student ID $id has been permanently deleted.";
                }
            }
        }
    } catch (\PDOException $e) {
        $errorMsg = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal — LH</title>
    <link rel="stylesheet" href="script.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Custom Cursor -->
<div id="cursor"></div>
<div id="cursor-ring"></div>

<!-- Animated Background Blobs -->
<div class="bg-blobs" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="app-shell">

    <!-- ===== SIDEBAR ===== -->
    <aside class="glass-sidebar">
        <div class="logo-wrapper">

            <!-- ANIMATED SVG LOGO -->
            <svg id="logo-trigger" class="logo-svg" width="64" height="64" viewBox="0 0 120 120" aria-label="Portal logo">
                <defs>
                    <radialGradient id="bg-grad" cx="50%" cy="50%" r="50%">
                        <stop offset="0%"   stop-color="#a8ff47"/>
                        <stop offset="100%" stop-color="#5bc916"/>
                    </radialGradient>
                    <filter id="glow-f">
                        <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                        <feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>
                    </filter>
                </defs>

                <!-- Outer ring -->
                <circle cx="60" cy="60" r="58" fill="none" stroke="rgba(168,255,71,0.15)" stroke-width="1"/>
                <circle cx="60" cy="60" r="48" fill="rgba(168,255,71,0.04)"/>

                <!-- Hexagon shape -->
                <polygon points="60,8 102,32 102,88 60,112 18,88 18,32"
                         fill="rgba(10,10,18,0.9)"
                         stroke="rgba(168,255,71,0.3)"
                         stroke-width="1.2"/>

                <!-- Inner accent lines -->
                <line x1="60" y1="8" x2="60" y2="26" stroke="rgba(168,255,71,0.5)" stroke-width="1"/>
                <line x1="60" y1="94" x2="60" y2="112" stroke="rgba(168,255,71,0.5)" stroke-width="1"/>
                <line x1="18" y1="32" x2="34" y2="41" stroke="rgba(168,255,71,0.3)" stroke-width="1"/>
                <line x1="86" y1="41" x2="102" y2="32" stroke="rgba(168,255,71,0.3)" stroke-width="1"/>

                <!-- LH monogram -->
                <text x="60" y="67" font-size="26" font-family="Syne, sans-serif"
                      fill="#a8ff47" font-weight="800" text-anchor="middle"
                      filter="url(#glow-f)">LH</text>

                <!-- Dot accents -->
                <circle cx="60" cy="24" r="3" fill="#a8ff47" opacity="0.6"/>
                <circle cx="60" cy="96" r="3" fill="#a8ff47" opacity="0.6"/>
                <circle cx="25" cy="40" r="2" fill="#7b4dff" opacity="0.5"/>
                <circle cx="95" cy="40" r="2" fill="#7b4dff" opacity="0.5"/>
                <circle cx="25" cy="80" r="2" fill="#7b4dff" opacity="0.5"/>
                <circle cx="95" cy="80" r="2" fill="#7b4dff" opacity="0.5"/>

                <!-- Sparkle stars (toggled by JS) -->
                <g id="sparkles" style="opacity:0;transition:opacity 0.2s">
                    <polygon points="15,15 17,11 19,15 23,17 19,19 17,23 15,19 11,17" fill="#a8ff47"/>
                    <polygon points="105,20 107,16 109,20 113,22 109,24 107,28 105,24 101,22" fill="#7b4dff"/>
                    <polygon points="10,70 12,66 14,70 18,72 14,74 12,78 10,74 6,72" fill="#ff5e5e"/>
                    <polygon points="110,65 112,62 114,65 117,67 114,69 112,72 110,69 107,67" fill="#a8ff47"/>
                </g>
            </svg>

            <div>
                <h2 class="brand-text">Lhester</h2>
            </div>
        </div>

        <nav class="side-nav">
            <button class="nav-link fall-in" style="--i:1" onclick="toggle('create')" data-target="create">
                <span class="nav-num">01</span>
                <span class="nav-text">CREATE</span>
            </button>
            <button class="nav-link fall-in" style="--i:2" onclick="toggle('read')" data-target="read">
                <span class="nav-num">02</span>
                <span class="nav-text">READ</span>
            </button>
            <button class="nav-link fall-in" style="--i:3" onclick="toggle('update')" data-target="update">
                <span class="nav-num">03</span>
                <span class="nav-text">UPDATE</span>
            </button>
            <button class="nav-link fall-in" style="--i:4" onclick="toggle('delete')" data-target="delete">
                <span class="nav-num">04</span>
                <span class="nav-text">DELETE</span>
            </button>
        </nav>

    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="viewport">

        <?php if (!empty($successMsg)): ?>
            <div class="toast toast-success" id="toast">✓ &nbsp;<?= htmlspecialchars($successMsg) ?></div>
        <?php elseif (!empty($errorMsg)): ?>
            <div class="toast toast-error" id="toast">✗ &nbsp;<?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <!-- WELCOME (default screen) -->
        <div id="welcome" class="welcome-screen slide-out" style="display:flex;">
            <h1>Student<br><span>Records</span><br>System.</h1>
        </div>

        <!-- CREATE -->
        <section id="create" class="glass-card slide-out" style="display:none;">
            <h1 class="section-title">New Student</h1>
            <form method="POST" class="styled-form">
                <input type="hidden" name="action" value="create">
                <div class="input-wrap">
                    <input type="text" name="name" placeholder="first_name" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="input-wrap">
                    <input type="text" name="surname" placeholder="last_name" required
                           value="<?= htmlspecialchars($_POST['surname'] ?? '') ?>">
                </div>
                <div class="input-wrap">
                    <input type="text" name="middlename" placeholder="middle_name"
                           value="<?= htmlspecialchars($_POST['middlename'] ?? '') ?>">
                </div>
                <div class="input-wrap">
                    <input type="text" name="address" placeholder="residential_address"
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>
                <div class="input-wrap">
                    <input type="text" name="contact" placeholder="contact_number"
                           value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-primary">Confirm &amp; Register →</button>
            </form>
        </section>

        <!-- READ -->
        <section id="read" class="glass-card slide-out" style="display:none;">
            <h1 class="section-title">All Records</h1>
            <div class="table-frame">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Middle</th>
                            <th>Address</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
                        $count = 0;
                        while ($row = $stmt->fetch()):
                            $count++;
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name'] . ' ' . $row['surname']) ?></td>
                            <td><?= htmlspecialchars($row['middlename'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($row['address'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($row['contact_number'] ?? '—') ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($count === 0): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:rgba(200,207,224,0.2); padding:2.5rem; font-size:0.75rem; letter-spacing:0.1em;">
                                NO RECORDS FOUND
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="record-count">total_records: <strong><?= $count ?></strong></p>
        </section>

        <!-- UPDATE -->
        <section id="update" class="glass-card slide-out" style="display:none;">
            <h1 class="section-title">Edit Record</h1>
            <form method="POST" class="styled-form">
                <input type="hidden" name="action" value="update">
                <div class="input-wrap">
                    <input type="number" name="id" placeholder="student_id (required)" required min="1"
                           value="<?= htmlspecialchars($_POST['id'] ?? '') ?>">
                </div>
                <div class="input-wrap">
                    <input type="text" name="name" placeholder="new_first_name (optional)"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="input-wrap">
                    <input type="text" name="surname" placeholder="new_last_name (optional)"
                           value="<?= htmlspecialchars($_POST['surname'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-primary">Push Update →</button>
            </form>
        </section>

        <!-- DELETE -->
        <section id="delete" class="glass-card slide-out" style="display:none;">
            <h1 class="section-title danger-text">Delete Record</h1>
            <form method="POST" class="styled-form" onsubmit="return confirmDelete()">
                <input type="hidden" name="action" value="delete">
                <div class="input-wrap">
                    <input type="number" name="id" placeholder="student_id_to_delete" required min="1"
                           value="<?= htmlspecialchars($_POST['id'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-primary btn-danger">Execute Delete →</button>
            </form>
        </section>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>
