<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LARAVEL_START', microtime(true));

$host = '127.0.0.1';
$port = '3306';
$db = 'elegance_salon';
$user = 'root';
$pass = '';

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            if ($k === 'DB_HOST') $host = $v;
            if ($k === 'DB_PORT') $port = $v;
            if ($k === 'DB_DATABASE') $db = $v;
            if ($k === 'DB_USERNAME') $user = $v;
            if ($k === 'DB_PASSWORD') $pass = $v;
        }
    }
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Connected to MySQL database: $db\n";

    // Re-create users table
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS reviews;");
    $pdo->exec("DROP TABLE IF EXISTS appointments;");
    $pdo->exec("DROP TABLE IF EXISTS service_stylist;");
    $pdo->exec("DROP TABLE IF EXISTS stylists;");
    $pdo->exec("DROP TABLE IF EXISTS services;");
    $pdo->exec("DROP TABLE IF EXISTS gallery_items;");
    $pdo->exec("DROP TABLE IF EXISTS special_offers;");
    $pdo->exec("DROP TABLE IF EXISTS users;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $pdo->exec("CREATE TABLE users (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(255) NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'customer', 'stylist') DEFAULT 'customer',
        status ENUM('active', 'inactive') DEFAULT 'active',
        avatar VARCHAR(255) NULL,
        remember_token VARCHAR(100) NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'users' ready.\n";

    // Create services table
    $pdo->exec("CREATE TABLE services (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        description TEXT NULL,
        duration INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        rating DECIMAL(3,2) DEFAULT 5.0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        image VARCHAR(255) NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'services' ready.\n";

    // Create stylists table
    $pdo->exec("CREATE TABLE stylists (
        id VARCHAR(50) PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        name VARCHAR(255) NOT NULL,
        position VARCHAR(100) NOT NULL,
        specialization VARCHAR(255) NOT NULL,
        experience INT NOT NULL,
        rating DECIMAL(3,2) DEFAULT 5.0,
        phone VARCHAR(255) NULL,
        email VARCHAR(255) NULL,
        address VARCHAR(255) NULL,
        bio TEXT NULL,
        hours VARCHAR(100) NULL,
        days JSON NULL,
        image VARCHAR(255) NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        sales DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'stylists' ready.\n";

    // Create service_stylist pivot table
    $pdo->exec("CREATE TABLE service_stylist (
        service_id VARCHAR(50) NOT NULL,
        stylist_id VARCHAR(50) NOT NULL,
        PRIMARY KEY (service_id, stylist_id),
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
        FOREIGN KEY (stylist_id) REFERENCES stylists(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'service_stylist' ready.\n";

    // Create appointments table
    $pdo->exec("CREATE TABLE appointments (
        id VARCHAR(50) PRIMARY KEY,
        customer_id BIGINT UNSIGNED NOT NULL,
        service_id VARCHAR(50) NOT NULL,
        stylist_id VARCHAR(50) NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time VARCHAR(50) NOT NULL,
        status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
        notes TEXT NULL,
        amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
        FOREIGN KEY (stylist_id) REFERENCES stylists(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'appointments' ready.\n";

    // Create gallery_items table
    $pdo->exec("CREATE TABLE gallery_items (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        image VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'gallery_items' ready.\n";

    // Create special_offers table
    $pdo->exec("CREATE TABLE special_offers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        category_tag VARCHAR(100) NULL,
        badge_text VARCHAR(100) NULL,
        original_price DECIMAL(10,2) NOT NULL,
        special_price DECIMAL(10,2) NOT NULL,
        discount_percent INT DEFAULT 0,
        image VARCHAR(255) NULL,
        is_featured TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'special_offers' ready.\n";

    // Create reviews table
    $pdo->exec("CREATE TABLE reviews (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_id BIGINT UNSIGNED NOT NULL,
        service_id VARCHAR(50) NULL,
        rating INT DEFAULT 5,
        message TEXT NOT NULL,
        status ENUM('published', 'pending', 'archived') DEFAULT 'published',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'reviews' ready.\n";

    // ----------------------------------------------------
    // SEED ONLY ESSENTIAL ADMIN ACCOUNT (NO DUMMY CUSTOMERS)
    // ----------------------------------------------------
    $passAdmin = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (id, name, email, phone, password, role, status) VALUES (1, ?, ?, ?, ?, 'admin', 'active')");
    $stmt->execute(['Elegance Concierge Admin', 'admin@elegancesalon.com', '+92 300 111 0000', $passAdmin]);
    $stmt2 = $pdo->prepare("INSERT INTO users (id, name, email, phone, password, role, status) VALUES (2, ?, ?, ?, ?, 'admin', 'active')");
    $stmt2->execute(['Elegance Salon Admin', 'admin@elegance.com', '+92 300 000 0000', $passAdmin]);
    echo "Admin users seeded.\n";

    // Seed Services
    $servicesData = [
        ['srv-01', 'Signature Haircut', 'Hair', 'Precision cut tailored to your face shape and lifestyle.', 45, 3500, 4.9, 'assets/images/services/Signature Haircut.1.png'],
        ['srv-02', 'Bridal Hair Styling', 'Hair', 'Editorial updos and soft waves for weddings and events.', 90, 12000, 5.0, 'assets/images/services/Bridal Hair Styling.2.jpg'],
        ['srv-03', 'Balayage Color', 'Hair', 'Hand-painted highlights with gloss for luminous dimension.', 180, 18000, 4.8, 'assets/images/services/Balayage Color.3.jpg'],
        ['srv-04', 'Keratin Treatment', 'Treatments', 'Smoothing treatment that restores shine and manageability.', 150, 15000, 4.7, 'assets/images/services/Keratin Treatment.4.jpg'],
        ['srv-05', 'Luxury Hydra Facial', 'Facial', 'Deep cleanse, extract, and infuse with medical-grade serums.', 60, 8500, 4.9, 'assets/images/services/Luxury Hydra Facial.5.jpg'],
        ['srv-06', 'Classic Manicure', 'Nails', 'Shape, cuticle care, massage, and long-wear polish.', 40, 2800, 4.6, 'assets/images/services/Classic Manicure.6.jpg'],
        ['srv-07', 'Spa Pedicure', 'Nails', 'Soak, exfoliation, massage, and gel color for lasting shine.', 55, 3800, 4.8, 'assets/images/services/Spa Pedicure.7.jpg'],
        ['srv-08', 'Glam Makeup', 'Makeup', 'Soft glam to full editorial looks for any occasion.', 75, 9500, 4.9, 'assets/images/services/Glam Makeup.8.jpg'],
        ['srv-09', 'Bridal Makeup', 'Makeup', 'Long-wear bridal artistry with trial-ready consultation.', 120, 22000, 5.0, 'assets/images/services/Bridal Makeup.9.jpg'],
        ['srv-10', 'Gold Radiance Facial', 'Beauty Care', '24k gold mask ritual to plump and illuminate tired skin.', 70, 11000, 4.8, 'assets/images/services/Gold Radiance Facial.10.jpg'],
        ['srv-11', 'Scalp Therapy', 'Treatments', 'Detoxifying scalp massage with botanical oils.', 50, 4500, 4.5, 'assets/images/services/Scalp Therapy.11.jpg']
    ];

    $stmtSvc = $pdo->prepare("INSERT INTO services (id, name, category, description, duration, price, rating, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?)");
    foreach ($servicesData as $s) {
        $stmtSvc->execute($s);
    }
    echo "Services seeded.\n";

    // Seed Stylists
    $stylistsData = [
        ['stf-01', 'Sarah Khan', 'Senior Hair Stylist', 'Hair Styling & Coloring', 12, 4.9, '+92 300 111 2201', 'sarah@elegancesalon.com', 'DHA Phase 5, Lahore', 'Sarah leads the color atelier with a decade of editorial work and a signature approach to lived-in luxury color.', '10:00 AM – 7:00 PM', json_encode(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']), 'assets/images/stylists/Senior Hair Stylist.1.jpg', 50000],
        ['stf-02', 'Ayesha Malik', 'Hair Therapist', 'Treatments & Cuts', 8, 4.8, '+92 300 111 2202', 'ayesha@elegancesalon.com', 'Johar Town, Lahore', 'Ayesha restores hair health with keratin systems and precision cutting for naturally refined movement.', '11:00 AM – 8:00 PM', json_encode(['Tue', 'Wed', 'Thu', 'Fri', 'Sat']), 'assets/images/stylists/Hair Therapist.2.jpg', 38000],
        ['stf-03', 'Hina Raza', 'Beauty Director', 'Makeup & Facials', 10, 5.0, '+92 300 111 2203', 'hina@elegancesalon.com', 'Model Town, Lahore', 'Hina crafts camera-ready skin and bridal looks that photograph beautifully from nikkah to walima.', '10:00 AM – 8:00 PM', json_encode(['Wed', 'Thu', 'Fri', 'Sat', 'Sun']), 'assets/images/stylists/Beauty Director.3.jpg', 72000],
        ['stf-04', 'Omar Sheikh', 'Creative Stylist', 'Men\'s & Color', 6, 4.7, '+92 300 111 2204', 'omar@elegancesalon.com', 'Bahria Town, Lahore', 'Omar brings contemporary barbering and color artistry with a calm, consultative chair-side manner.', '12:00 PM – 8:00 PM', json_encode(['Mon', 'Tue', 'Thu', 'Fri', 'Sat']), 'assets/images/stylists/Creative Stylist.4.jpg', 29000],
        ['stf-05', 'Mehwish Ali', 'Nail Artist', 'Manicure & Pedicure', 7, 4.8, '+92 300 111 2205', 'mehwish@elegancesalon.com', 'Garden Town, Lahore', 'Mehwish is known for architectural nail design, gel art, and meticulous spa pedicure rituals.', '10:00 AM – 6:00 PM', json_encode(['Mon', 'Wed', 'Fri', 'Sat', 'Sun']), 'assets/images/stylists/Nail Artist.5.jpg', 24000]
    ];

    $stmtSt = $pdo->prepare("INSERT INTO stylists (id, name, position, specialization, experience, rating, phone, email, address, bio, hours, days, image, sales, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    foreach ($stylistsData as $st) {
        $stmtSt->execute($st);
    }
    echo "Stylists seeded.\n";

    // Seed Pivot
    $stmtPiv = $pdo->prepare("INSERT INTO service_stylist (service_id, stylist_id) VALUES (?, ?)");
    $pivots = [
        ['srv-01', 'stf-01'], ['srv-01', 'stf-02'], ['srv-01', 'stf-04'],
        ['srv-02', 'stf-01'], ['srv-03', 'stf-01'], ['srv-03', 'stf-04'],
        ['srv-04', 'stf-02'], ['srv-05', 'stf-03'], ['srv-06', 'stf-05'],
        ['srv-07', 'stf-05'], ['srv-08', 'stf-03'], ['srv-09', 'stf-03'],
        ['srv-10', 'stf-03'], ['srv-10', 'stf-05'], ['srv-11', 'stf-02'], ['srv-11', 'stf-04']
    ];
    foreach ($pivots as $p) {
        $stmtPiv->execute($p);
    }

    // Seed Gallery (12 items)
    $galleryData = [
        [1, 'Salon', 'Atelier Interior & Suites', 'Private treatment rooms with champagne gold lighting', 'assets/images/gallerys/gallery.1.jpg', 1],
        [2, 'Hair', 'Editorial Hair Styling', 'Couture updos & soft waves for special occasions', 'assets/images/gallerys/gallery.2.jpg', 2],
        [3, 'Hair', 'Precision Cut & Styling', 'Architectural haircutting tailored to your face structure', 'assets/images/gallerys/gallery.3.jpg', 3],
        [4, 'Hair', 'Champagne Balayage & Gloss', 'Hand-painted dimensional blonde highlights with gloss finish', 'assets/images/gallerys/gallery.4.jpg', 4],
        [5, 'Makeup', 'Editorial Glam Makeup', 'Luminous camera-ready skin and soft glam artistry', 'assets/images/gallerys/gallery.5.jpg', 5],
        [6, 'Facial', 'Hydra Facial Therapy', 'Medical-grade skin extraction & serum infusion ritual', 'assets/images/gallerys/gallery.6.jpg', 6],
        [7, 'Salon', 'Styling Chairs & Stations', 'Sunlit styling stations on MM Alam Road', 'assets/images/gallerys/gallery.7.jpg', 7],
        [8, 'Salon', 'Vanity & Mirror Details', 'Ambient backlit mirrors & private dressing suites', 'assets/images/gallerys/gallery.8.jpg', 8],
        [9, 'Hair', 'Master Stylist Consultation', 'Dedicated one-on-one consultation with senior stylists', 'assets/images/gallerys/gallery.9.jpg', 9],
        [10, 'Facial', '24k Gold Radiance Mask', 'Illuminating luxury gold leaf mask to plump & restore skin', 'assets/images/gallerys/gallery.10.jpg', 10],
        [11, 'Nails', 'Luxury Spa Pedicure & Nails', 'Restorative botanical foot soak with gel polish finish', 'assets/images/gallerys/gallery.11.jpg', 11],
        [12, 'Makeup', 'Haute Couture Look Reveal', 'Flawless final reveal for gala events and bridal occasions', 'assets/images/gallerys/gallery.12.jpg', 12]
    ];

    $stmtGal = $pdo->prepare("INSERT INTO gallery_items (id, category, title, description, image, display_order, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    foreach ($galleryData as $g) {
        $stmtGal->execute($g);
    }
    echo "Gallery seeded.\n";

    echo "DATABASE_CLEAN_RESEED_COMPLETE\n";

} catch (Exception $e) {
    echo "DB_MIGRATION_ERROR: " . $e->getMessage() . "\n";
}
