<?php
declare(strict_types=1);
session_start();
$config = require __DIR__ . '/config.php';
$dbDir = __DIR__ . '/data';
$dbFile = $dbDir . '/messages.sqlite';
$success = '';
$error = '';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Your form session expired. Refresh and try again.';
    } else {
        $name = trim((string)($_POST['sender_name'] ?? ''));
        $email = trim((string)($_POST['sender_email'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        if ($name === '' || $email === '' || $message === '') {
            $error = 'Please complete all fields.';
        } elseif (strlen($name) > 100 || strlen($email) > 254 || strlen($message) > 5000) {
            $error = 'One or more fields are too long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                if (!is_dir($dbDir) && !mkdir($dbDir, 0750, true) && !is_dir($dbDir)) {
                    throw new RuntimeException('Unable to create database directory.');
                }
                $pdo = new PDO('sqlite:' . $dbFile, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $pdo->exec('CREATE TABLE IF NOT EXISTS messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    sender_name TEXT NOT NULL,
                    sender_email TEXT NOT NULL,
                    message TEXT NOT NULL,
                    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )');
                $stmt = $pdo->prepare('INSERT INTO messages (sender_name, sender_email, message)
                    VALUES (:name, :email, :message)');
                $stmt->execute([':name' => $name, ':email' => $email, ':message' => $message]);
                $success = 'Your message was saved successfully. Thank you for reaching out.';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (Throwable $exception) {
                error_log('Contact form database error: ' . $exception->getMessage());
                $error = 'Sorry, your message could not be saved right now.';
            }
        }
    }
}
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= e($config['name']) ?> | Personal portfolio">
<title><?= e($config['name']) ?> | Personal Portfolio</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
  <nav class="nav">
    <h2 class="logo">👑 <?= e($config['name']) ?></h2>
    <button class="menu-toggle" type="button" aria-label="Toggle menu" aria-expanded="false">☰</button>
    <ul class="nav-links">
      <li><a href="#home">Home</a></li><li><a href="#about">About</a></li>
      <li><a href="#hobbies">Hobbies</a></li><li><a href="#skills">Skills</a></li><li><a href="#contact">Contact</a></li>
    </ul>
    <a href="#contact" class="cv">Contact Me</a>
  </nav>
</header>
<main>
<section id="home">
  <div class="profile"><div class="photo-placeholder">YD</div></div>
  <div class="welcome">
    <h3>Welcome to my website!</h3>
    <h1><?= e($config['name']) ?></h1>
    <h2><?= e($config['tagline']) ?></h2>
    <p><?= e($config['intro']) ?></p>
    <a href="#about" class="button">Learn More →</a>
  </div>
  <aside class="social">
    <h2>Follow Me ♡</h2>
    <p>YouTube | Instagram | Telegram | TikTok</p>
    <h3>Dream Big, Achieve More.</h3>
  </aside>
</section>
<section id="about">
  <h2>About Me</h2>
  <p>Hello! I'm <?= e($config['name']) ?>. I enjoy learning, creativity, technology, and personal growth. This is my personal space on the internet.</p>
  <a href="#contact" class="button">Let's Connect →</a>
</section>
<section id="hobbies">
  <h2>My Hobbies</h2>
  <div class="cards">
  <?php foreach ($config['hobbies'] as $hobby): ?><article class="card"><h3><?= e($hobby) ?></h3></article><?php endforeach; ?>
  </div>
</section>
<section id="skills">
  <h2>My Skills</h2>
  <div class="cards">
  <?php foreach ($config['skills'] as $skill): ?><article class="card"><p><?= e($skill) ?></p></article><?php endforeach; ?>
  </div>
  <p class="hint">Update the skills list in config.php so it reflects Blen's actual abilities.</p>
</section>
<section id="contact">
  <h2>Contact Me</h2>
  <p>Feel free to send me a message!</p>
  <?php if ($success): ?><p class="notice success" role="status"><?= e($success) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="notice error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" action="#contact">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <label for="sender_name">Your Name</label>
    <input id="sender_name" name="sender_name" type="text" placeholder="Your Name" maxlength="100" required>
    <label for="sender_email">Your Email</label>
    <input id="sender_email" name="sender_email" type="email" placeholder="Your Email" maxlength="254" required>
    <label for="message">Your Message</label>
    <textarea id="message" name="message" placeholder="Your Message" rows="5" maxlength="5000" required></textarea>
    <button type="submit" class="button">Send Message</button>
  </form>
  <p class="contact-email">Email: <?= e($config['email']) ?></p>
  <p class="hint">Messages are saved in the server's SQLite database. This form does not prepare or send email.</p>
</section>
</main>
<footer><p>© <?= date('Y') ?> <?= e($config['name']) ?>. All Rights Reserved.</p></footer>
<script src="assets/js/main.js"></script>
</body>
</html>
