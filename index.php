<?php

/**
 * Elegance Salon — Full-Stack Laravel + PHP + MySQL Front Controller & Router
 */

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (!empty($basePath) && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
    if ($uri === '') $uri = '/';
}

if (php_sapi_name() === 'cli-server') {
    $staticFile = __DIR__ . $uri;
    if ($uri !== '/' && is_file($staticFile) && preg_match('/\.(?:css|js|jpg|jpeg|png|gif|svg|webp|ico|woff|woff2|ttf|eot|mp4|webm|map)$/i', $staticFile)) {
        return false;
    }
}

// Helper for asset URLs
function asset($path) {
    global $basePath;
    $path = ltrim($path, '/');
    return ($basePath ?: '') . '/' . $path;
}

// Database Connection Helper
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
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
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (Exception $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Global Auth Helper
class Auth {
    public static function check() {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    public static function user() {
        if (!self::check()) return null;
        $u = (object) $_SESSION['user'];
        if (!isset($u->formatted_created_at)) {
            $u->formatted_created_at = isset($u->created_at) ? date('F j, Y', strtotime($u->created_at)) : date('F j, Y');
        }
        return $u;
    }

    public static function id() {
        return self::check() ? $_SESSION['user']['id'] : null;
    }

    public static function login($user) {
        $_SESSION['user'] = is_array($user) ? $user : (array) $user;
    }

    public static function logout() {
        unset($_SESSION['user']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
}

// Collections & Bags
class SimpleCollection implements Countable, IteratorAggregate {
    private $items;
    public function __construct($items = []) {
        $this->items = is_array($items) ? array_values($items) : (is_object($items) ? (array) $items : []);
    }
    public function count(): int { return count($this->items); }
    public function getIterator(): Traversable { return new ArrayIterator($this->items); }
    public function all() { return $this->items; }
    public function first() { return $this->items[0] ?? null; }
    public function where($key, $value) {
        return new self(array_filter($this->items, fn($item) => is_object($item) ? (($item->$key ?? null) == $value) : (($item[$key] ?? null) == $value)));
    }
    public function whereIn($key, $values) {
        return new self(array_filter($this->items, fn($item) => is_object($item) ? in_array($item->$key ?? null, (array)$values) : in_array($item[$key] ?? null, (array)$values)));
    }
    public function map(callable $cb) {
        return new self(array_map($cb, $this->items));
    }
    public function filter(callable $cb = null) {
        return new self(array_filter($this->items, $cb ?: fn($i) => !empty($i)));
    }
    public function pluck($key) {
        return array_map(fn($item) => is_object($item) ? ($item->$key ?? null) : ($item[$key] ?? null), $this->items);
    }
    public function toArray() { return $this->items; }
}

function collect($items = []) {
    return new SimpleCollection($items);
}

class SimpleMessageBag {
    private $messages;
    public function __construct($messages = []) { $this->messages = (array) $messages; }
    public function any() { return !empty($this->messages); }
    public function count(): int { return count($this->messages); }
    public function first($key = null) {
        if ($key === null) {
            $first = reset($this->messages);
            return is_array($first) ? reset($first) : $first;
        }
        $msg = $this->messages[$key] ?? null;
        return is_array($msg) ? reset($msg) : $msg;
    }
    public function get($key) { return (array) ($this->messages[$key] ?? []); }
    public function has($key) { return isset($this->messages[$key]); }
    public function all() {
        $flat = [];
        foreach ($this->messages as $m) {
            if (is_array($m)) {
                foreach ($m as $sub) $flat[] = $sub;
            } else {
                $flat[] = $m;
            }
        }
        return $flat;
    }
}

function csrf_token() {
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function old($key, $default = '') {
    return htmlspecialchars($_POST[$key] ?? $_GET[$key] ?? $default ?? '', ENT_QUOTES, 'UTF-8');
}

function session($key = null, $default = null) {
    if ($key === null) {
        return new class {
            public function get($k, $def = null) { return $_SESSION[$k] ?? $def; }
            public function has($k) { return isset($_SESSION[$k]); }
            public function put($k, $v) { $_SESSION[$k] = $v; }
            public function flash($k, $v) { $_SESSION[$k] = $v; }
        };
    }
    return $_SESSION[$key] ?? $default;
}

function route($name, $params = []) {
    global $basePath;
    $prefix = $basePath ?? '';
    $paramStr = '';
    if (is_array($params) && !empty($params)) {
        $paramStr = '?' . http_build_query($params);
    } elseif (!empty($params)) {
        $paramStr = '?' . http_build_query(['id' => $params]);
    }

    switch ($name) {
        case 'home': return ($prefix ?: '') . '/' . $paramStr;
        case 'services': return $prefix . '/services.html' . $paramStr;
        case 'stylists': return $prefix . '/stylists.html' . $paramStr;
        case 'stylist.show':
            $id = is_array($params) ? ($params['id'] ?? '') : $params;
            return $prefix . '/stylist.html?id=' . urlencode($id);
        case 'about': return $prefix . '/about.html' . $paramStr;
        case 'contact': return $prefix . '/contact.html' . $paramStr;
        case 'feedback': return $prefix . '/feedback.html' . $paramStr;
        case 'login': return $prefix . '/login.html' . $paramStr;
        case 'register': return $prefix . '/register.html' . $paramStr;
        case 'logout': return $prefix . '/logout';
        case 'customer.dashboard': return $prefix . '/customer/dashboard.html' . $paramStr;
        case 'customer.booking': return $prefix . '/customer/booking.html' . $paramStr;
        case 'customer.booking.store': return $prefix . '/customer/booking';
        case 'customer.appointments': return $prefix . '/customer/appointments.html' . $paramStr;
        case 'customer.profile': return $prefix . '/customer/profile.html' . $paramStr;
        case 'customer.profile.update': return $prefix . '/customer/profile';
        case 'customer.appointments.cancel':
            $id = is_array($params) ? ($params['id'] ?? '') : $params;
            return $prefix . '/customer/appointments/' . urlencode($id) . '/cancel';
        case 'admin.dashboard': return $prefix . '/admin/dashboard.html' . $paramStr;
        case 'admin.customers': return $prefix . '/admin/customers' . $paramStr;
        case 'admin.services': return $prefix . '/admin/services.html' . $paramStr;
        case 'admin.services.store': return $prefix . '/admin/services';
        case 'admin.stylists': return $prefix . '/admin/stylists' . $paramStr;
        case 'admin.bookings': return $prefix . '/admin/bookings' . $paramStr;
        case 'admin.bookings.status':
            $id = is_array($params) ? ($params['id'] ?? '') : $params;
            return $prefix . '/admin/bookings/status?appointment_id=' . urlencode($id);
        case 'admin.gallery': return $prefix . '/admin/gallery.html' . $paramStr;
        default: return $prefix . '/' . ltrim($name, '/') . $paramStr;
    }
}

// Blade Template Engine
function compileBlade($code) {
    $code = preg_replace('/\{\{\-\-(.+?)\-\-\}\}/s', '', $code);
    $code = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?php echo $1; ?>', $code);
    $code = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?php echo htmlspecialchars($1 ?? "", ENT_QUOTES, "UTF-8"); ?>', $code);
    $code = preg_replace('/@csrf/i', '<?php echo csrf_field(); ?>', $code);

    $balanced = '(\((?:[^()]++|(?1))*\))';

    $code = preg_replace_callback('/@if\s*' . $balanced . '/s', function($m) {
        return '<?php if' . $m[1] . ': ?>';
    }, $code);

    $code = preg_replace_callback('/@elseif\s*' . $balanced . '/s', function($m) {
        return '<?php elseif' . $m[1] . ': ?>';
    }, $code);

    $code = preg_replace('/@else/i', '<?php else: ?>', $code);
    $code = preg_replace('/@endif/i', '<?php endif; ?>', $code);

    $code = preg_replace_callback('/@unless\s*' . $balanced . '/s', function($m) {
        return '<?php if(!' . $m[1] . '): ?>';
    }, $code);
    $code = preg_replace('/@endunless/i', '<?php endif; ?>', $code);

    $code = preg_replace_callback('/@isset\s*' . $balanced . '/s', function($m) {
        $inner = substr($m[1], 1, -1);
        return '<?php if(isset(' . $inner . ')): ?>';
    }, $code);
    $code = preg_replace('/@endisset/i', '<?php endif; ?>', $code);

    $code = preg_replace_callback('/@empty\s*' . $balanced . '/s', function($m) {
        $inner = substr($m[1], 1, -1);
        return '<?php if(empty(' . $inner . ')): ?>';
    }, $code);
    $code = preg_replace('/@endempty/i', '<?php endif; ?>', $code);

    $code = preg_replace_callback('/@foreach\s*' . $balanced . '/s', function($m) {
        return '<?php foreach' . $m[1] . ': ?>';
    }, $code);
    $code = preg_replace('/@endforeach/i', '<?php endforeach; ?>', $code);

    $code = preg_replace_callback('/@for\s*' . $balanced . '/s', function($m) {
        return '<?php for' . $m[1] . ': ?>';
    }, $code);
    $code = preg_replace('/@endfor/i', '<?php endfor; ?>', $code);

    $code = preg_replace('/@php/i', '<?php ', $code);
    $code = preg_replace('/@endphp/i', ' ?>', $code);
    return $code;
}

// View Renderer Helper
function renderBlade($viewName, $data = []) {
    if (!isset($data['errors'])) {
        $errs = [];
        if (isset($_SESSION['error'])) {
            $errs['error'] = [$_SESSION['error']];
        }
        if (isset($_SESSION['errors'])) {
            $errs = array_merge($errs, (array)$_SESSION['errors']);
        }
        $data['errors'] = new SimpleMessageBag($errs);
    }

    if (!isset($data['user']) && Auth::check()) {
        $data['user'] = Auth::user();
    }

    $viewPath = __DIR__ . '/resources/views/' . str_replace('.', '/', $viewName) . '.blade.php';
    if (!file_exists($viewPath)) {
        $htmlPath = __DIR__ . '/' . $viewName . '.html';
        if (file_exists($htmlPath)) {
            include $htmlPath;
            exit;
        } else {
            http_response_code(404);
            echo "<h1>Page Not Found</h1>";
            exit;
        }
    }

    $template = file_get_contents($viewPath);

    if (preg_match('/@extends\s*\([\'"](.+?)[\'"]\)/', $template, $extMatch)) {
        $layoutName = $extMatch[1];
        $layoutPath = __DIR__ . '/resources/views/' . str_replace('.', '/', $layoutName) . '.blade.php';
        $layoutContent = file_exists($layoutPath) ? file_get_contents($layoutPath) : '';

        $sections = [];
        if (preg_match_all('/@section\s*\(\s*[\'"]([a-zA-Z0-9_\-]+)[\'"]\s*,\s*[\'"](.*?)[\'"]\s*\)/', $template, $mSingle, PREG_SET_ORDER)) {
            foreach ($mSingle as $ms) {
                $sections[$ms[1]] = $ms[2];
            }
        }
        if (preg_match_all('/@section\s*\(\s*[\'"]([a-zA-Z0-9_\-]+)[\'"]\s*\)(.*?)@endsection/s', $template, $mBlock, PREG_SET_ORDER)) {
            foreach ($mBlock as $mb) {
                $sections[$mb[1]] = $mb[2];
            }
        }

        $compiled = preg_replace_callback('/@yield\s*\(\s*[\'"]([a-zA-Z0-9_\-]+)[\'"](?:\s*,\s*[\'"](.*?)[\'"])?\s*\)/', function($ym) use ($sections) {
            $key = $ym[1];
            $default = $ym[2] ?? '';
            return $sections[$key] ?? $default;
        }, $layoutContent);
    } else {
        $compiled = $template;
    }

    $compiledPhp = compileBlade($compiled);

    extract($data);
    ob_start();
    eval('?>' . $compiledPhp);
    $content = ob_get_clean();

    // Clear flash session error & success messages after rendering
    unset($_SESSION['error'], $_SESSION['errors'], $_SESSION['success']);

    echo $content;
    exit;
}

function requireCustomer($basePath) {
    if (!Auth::check()) {
        $_SESSION['error'] = 'Please log in to continue.';
        header('Location: ' . $basePath . '/login.html');
        exit;
    }
}

function requireAdmin($basePath) {
    if (!Auth::check()) {
        $_SESSION['error'] = 'Please log in to continue.';
        header('Location: ' . $basePath . '/login.html');
        exit;
    }
    if ((Auth::user()->role ?? '') !== 'admin') {
        $_SESSION['error'] = 'Access denied. Administrator privileges required.';
        header('Location: ' . $basePath . '/customer/dashboard.html');
        exit;
    }
}

// Simple Router
$route = trim($uri, '/');

// API Endpoints
if (strpos($route, 'api/services') === 0) {
    header('Content-Type: application/json');
    $db = getDB();
    $stmt = $db->query("SELECT * FROM services WHERE status='active'");
    echo json_encode($stmt->fetchAll());
    exit;
}

if (strpos($route, 'api/stylists') === 0) {
    header('Content-Type: application/json');
    $db = getDB();
    $stmt = $db->query("SELECT * FROM stylists WHERE status='active'");
    echo json_encode($stmt->fetchAll());
    exit;
}

if (strpos($route, 'api/gallery') === 0) {
    header('Content-Type: application/json');
    $db = getDB();
    $stmt = $db->query("SELECT * FROM gallery_items WHERE status='active' ORDER BY display_order ASC");
    echo json_encode($stmt->fetchAll());
    exit;
}

if (strpos($route, 'api/appointments') === 0) {
    header('Content-Type: application/json; charset=UTF-8');
    $db = getDB();
    $stmt = $db->query("SELECT a.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, s.name as service_name, s.duration as duration, s.price as service_price, st.name as stylist_name FROM appointments a LEFT JOIN users u ON a.customer_id=u.id LEFT JOIN services s ON a.service_id=s.id LEFT JOIN stylists st ON a.stylist_id=st.id ORDER BY a.appointment_date DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

if (strpos($route, 'api/customers') === 0 || strpos($route, 'api/clients') === 0) {
    header('Content-Type: application/json; charset=UTF-8');
    $db = getDB();
    $stmt = $db->query("SELECT id, name, email, phone, status, created_at FROM users WHERE role='customer' ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

// Handle Logout
if ($route === 'logout' || $route === 'logout.html') {
    Auth::logout();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['success'] = 'Logged out successfully.';
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    $homeUrl = $basePath ? $basePath . '/' : '/';
    header('Location: ' . $homeUrl);
    exit;
}

// Handle Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    // 1. User Registration
    if ($route === 'register' || $route === 'register.html') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? $_POST['confirm'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            header('Location: ' . $basePath . '/register.html');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address.';
            header('Location: ' . $basePath . '/register.html');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters long.';
            header('Location: ' . $basePath . '/register.html');
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Password confirmation does not match.';
            header('Location: ' . $basePath . '/register.html');
            exit;
        }

        // Check if email exists
        $chk = $db->prepare("SELECT id FROM users WHERE email=?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $_SESSION['error'] = 'An account with this email address already exists. Please log in.';
            header('Location: ' . $basePath . '/register.html');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmtIns = $db->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')");
        $stmtIns->execute([$name, $email, $phone, $hashedPassword]);

        // Auto-login the newly created user and redirect directly to customer dashboard
        $newUserId = $db->lastInsertId();
        $stmtNew = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmtNew->execute([$newUserId]);
        $newUser = $stmtNew->fetch();
        Auth::login($newUser);

        $_SESSION['success'] = 'Account created successfully! Welcome to your customer dashboard.';
        header('Location: ' . $basePath . '/customer/dashboard.html');
        exit;
    }

    // 2. User Login
    if ($route === 'login' || $route === 'login.html') {
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (empty($email) || empty($pass)) {
            $_SESSION['error'] = 'Please enter both email and password.';
            header('Location: ' . $basePath . '/login.html');
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if (!$u) {
            $_SESSION['error'] = 'Account not found. Please sign up first.';
            $_SESSION['show_signup_link'] = true;
            header('Location: ' . $basePath . '/login.html');
            exit;
        }

        if (!password_verify($pass, $u['password'])) {
            $_SESSION['error'] = 'Incorrect email or password.';
            header('Location: ' . $basePath . '/login.html');
            exit;
        }

        Auth::login($u);
        if ($u['role'] === 'admin') {
            header('Location: ' . $basePath . '/admin/dashboard.html');
        } else {
            header('Location: ' . $basePath . '/customer/dashboard.html');
        }
        exit;
    }

    // 3. Customer Profile Update
    if ($route === 'customer/profile' || $route === 'customer/profile.html') {
        if (!Auth::check()) {
            header('Location: ' . $basePath . '/login.html');
            exit;
        }
        $u = Auth::user();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $upd = $db->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
        $upd->execute([$name, $email, $phone, $u->id]);

        $stmtUser = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmtUser->execute([$u->id]);
        $updatedUser = $stmtUser->fetch();
        Auth::login($updatedUser);

        $_SESSION['success'] = 'Profile updated successfully!';
        header('Location: ' . $basePath . '/customer/profile.html');
        exit;
    }

    // 4. Appointment Booking
    if ($route === 'customer/booking' || $route === 'customer/booking.html') {
        if (!Auth::check()) {
            header('Location: ' . $basePath . '/login.html');
            exit;
        }
        $serviceId = $_POST['service_id'] ?? '';
        $stylistId = $_POST['stylist_id'] ?? '';
        $date = $_POST['appointment_date'] ?? date('Y-m-d');
        $time = $_POST['appointment_time'] ?? '10:00 AM';
        $notes = $_POST['notes'] ?? '';

        // Double-Booking Check
        $chk = $db->prepare("SELECT * FROM appointments WHERE stylist_id=? AND appointment_date=? AND appointment_time=? AND status IN ('Pending', 'Confirmed')");
        $chk->execute([$stylistId, $date, $time]);
        if ($chk->fetch()) {
            $_SESSION['error'] = 'This stylist is already booked at the selected date and time. Please select another slot.';
            header('Location: ' . $basePath . '/customer/booking.html');
            exit;
        }

        // Get Service Price
        $sStmt = $db->prepare("SELECT price FROM services WHERE id=?");
        $sStmt->execute([$serviceId]);
        $sRow = $sStmt->fetch();
        $price = $sRow ? $sRow['price'] : 3500;

        $aptId = 'APT-' . rand(1000, 9999);
        $custUser = Auth::user();
        $ins = $db->prepare("INSERT INTO appointments (id, customer_id, service_id, stylist_id, appointment_date, appointment_time, status, notes, amount) VALUES (?, ?, ?, ?, ?, ?, 'Confirmed', ?, ?)");
        $ins->execute([$aptId, $custUser->id, $serviceId, $stylistId, $date, $time, $notes, $price]);

        $_SESSION['success'] = 'Appointment reserved successfully!';
        header('Location: ' . $basePath . '/customer/dashboard.html');
        exit;
    }

    // 4b. Customer Appointment Cancel
    if (preg_match('#^customer/appointments/([^/]+)/cancel$#', $route, $m)) {
        if (!Auth::check()) {
            header('Location: ' . $basePath . '/login.html');
            exit;
        }
        $aptId = $m[1];
        $custUser = Auth::user();
        $upd = $db->prepare("UPDATE appointments SET status='Cancelled' WHERE id=? AND customer_id=?");
        $upd->execute([$aptId, $custUser->id]);
        $_SESSION['success'] = 'Appointment cancelled successfully.';
        $referrer = $_SERVER['HTTP_REFERER'] ?? ($basePath . '/customer/appointments.html');
        header('Location: ' . $referrer);
        exit;
    }

    // 5. Admin Booking Status Update
    if (strpos($route, 'admin/bookings/status') === 0 || $route === 'admin.bookings.status') {
        $aptId = $_POST['appointment_id'] ?? $_GET['appointment_id'] ?? $_GET['id'] ?? '';
        $status = $_POST['status'] ?? 'Confirmed';
        $upd = $db->prepare("UPDATE appointments SET status=? WHERE id=?");
        $upd->execute([$status, $aptId]);
        $_SESSION['success'] = 'Booking status updated successfully.';
        $referrer = $_SERVER['HTTP_REFERER'] ?? ($basePath . '/admin/dashboard.html');
        header('Location: ' . $referrer);
        exit;
    }

    // 6. Admin Add Service
    if ($route === 'admin/services' || $route === 'admin/services.html') {
        $name = $_POST['name'] ?? '';
        $cat = $_POST['category'] ?? 'Hair';
        $price = $_POST['price'] ?? 5000;
        $duration = $_POST['duration'] ?? 60;
        $desc = $_POST['description'] ?? '';
        $id = 'srv-' . rand(15, 99);
        $ins = $db->prepare("INSERT INTO services (id, name, category, description, duration, price, rating, status, image) VALUES (?, ?, ?, ?, ?, ?, 5.0, 'active', 'assets/images/services/Signature Haircut.1.png')");
        $ins->execute([$id, $name, $cat, $desc, $duration, $price]);
        header('Location: ' . $basePath . '/admin/services.html');
        exit;
    }
}

// Dispatch Page Requests (GET)
$db = getDB();

switch ($route) {
    case '':
    case 'index.html':
    case 'home':
        $services = $db->query("SELECT * FROM services WHERE status='active' LIMIT 11")->fetchAll(PDO::FETCH_OBJ);
        $stylists = $db->query("SELECT * FROM stylists WHERE status='active' LIMIT 5")->fetchAll(PDO::FETCH_OBJ);
        $gallery = $db->query("SELECT * FROM gallery_items WHERE status='active' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_OBJ);
        $reviews = $db->query("SELECT r.*, u.name as customer_name, s.name as service_name FROM reviews r LEFT JOIN users u ON r.customer_id=u.id LEFT JOIN services s ON r.service_id=s.id WHERE r.status='published' LIMIT 6")->fetchAll(PDO::FETCH_OBJ);
        renderBlade('home', compact('services', 'stylists', 'gallery', 'reviews'));
        break;

    case 'services':
    case 'services.html':
        $cat = $_GET['category'] ?? 'All';
        $q = $_GET['q'] ?? '';
        $sql = "SELECT * FROM services WHERE status='active'";
        $params = [];
        if ($cat !== 'All') {
            $sql .= " AND category=?";
            $params[] = $cat;
        }
        if (!empty($q)) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $services = $stmt->fetchAll(PDO::FETCH_OBJ);
        $categories = array_merge(['All'], array_unique(array_column($db->query("SELECT category FROM services WHERE status='active'")->fetchAll(), 'category')));
        renderBlade('services', ['services' => $services, 'categories' => $categories, 'category' => $cat, 'query' => $q]);
        break;

    case 'stylists':
    case 'stylists.html':
        $spec = $_GET['spec'] ?? 'All';
        $rating = (float) ($_GET['rating'] ?? 0);
        $sql = "SELECT * FROM stylists WHERE status='active'";
        $params = [];
        if ($spec !== 'All') {
            $sql .= " AND specialization=?";
            $params[] = $spec;
        }
        if ($rating > 0) {
            $sql .= " AND rating >= ?";
            $params[] = $rating;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stylists = $stmt->fetchAll(PDO::FETCH_OBJ);
        $specializations = array_merge(['All'], array_unique(array_column($db->query("SELECT specialization FROM stylists WHERE status='active'")->fetchAll(), 'specialization')));
        renderBlade('stylists', ['stylists' => $stylists, 'specializations' => $specializations, 'spec' => $spec, 'minRating' => $rating]);
        break;

    case 'stylist':
    case 'stylist.html':
        $stId = $_GET['id'] ?? 'stf-01';
        $stStmt = $db->prepare("SELECT * FROM stylists WHERE id=?");
        $stStmt->execute([$stId]);
        $stylist = $stStmt->fetch(PDO::FETCH_OBJ);
        if (!$stylist) {
            $stylist = $db->query("SELECT * FROM stylists LIMIT 1")->fetch(PDO::FETCH_OBJ);
        }
        if ($stylist) {
            $stylist->days = json_decode($stylist->days ?? '[]', true);
        }
        $services = $db->query("SELECT * FROM services WHERE status='active' LIMIT 6")->fetchAll(PDO::FETCH_OBJ);
        renderBlade('stylist_detail', compact('stylist', 'services'));
        break;

    case 'about':
    case 'about.html':
        renderBlade('about');
        break;

    case 'contact':
    case 'contact.html':
        renderBlade('contact');
        break;

    case 'feedback':
    case 'feedback.html':
        renderBlade('feedback');
        break;

    case 'login':
    case 'login.html':
        if (Auth::check()) {
            $u = Auth::user();
            $dest = (($u->role ?? '') === 'admin') ? '/admin/dashboard.html' : '/customer/dashboard.html';
            header('Location: ' . $basePath . $dest);
            exit;
        }
        if (isset($_GET['next']) && strpos($_GET['next'], 'booking') !== false && empty($_SESSION['error'])) {
            $_SESSION['error'] = 'Please login to book an appointment.';
        }
        renderBlade('auth.login');
        break;

    case 'register':
    case 'register.html':
        if (Auth::check()) {
            $u = Auth::user();
            $dest = (($u->role ?? '') === 'admin') ? '/admin/dashboard.html' : '/customer/dashboard.html';
            header('Location: ' . $basePath . $dest);
            exit;
        }
        renderBlade('auth.register');
        break;

    case 'booking':
    case 'booking.html':
        if (!Auth::check()) {
            $_SESSION['error'] = 'Please login to book an appointment.';
            header('Location: ' . $basePath . '/login.html?next=customer/booking.html');
            exit;
        }
        header('Location: ' . $basePath . '/customer/booking.html');
        exit;

    case 'customer/dashboard':
    case 'customer/dashboard.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        requireCustomer($basePath);
        $u = Auth::user();
        $stmt = $db->prepare("SELECT a.*, s.name as service_name, st.name as stylist_name FROM appointments a LEFT JOIN services s ON a.service_id=s.id LEFT JOIN stylists st ON a.stylist_id=st.id WHERE a.customer_id=? ORDER BY a.appointment_date DESC");
        $stmt->execute([$u->id]);
        $apts = $stmt->fetchAll(PDO::FETCH_OBJ);
        $upcoming = array_filter($apts, fn($a) => in_array($a->status, ['Pending', 'Confirmed']));
        $completed = array_filter($apts, fn($a) => $a->status === 'Completed');
        renderBlade('customer.dashboard', ['user' => $u, 'appointments' => collect($apts), 'upcoming' => collect($upcoming), 'completed' => collect($completed)]);
        break;

    case 'customer/booking':
    case 'customer/booking.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        if (!Auth::check()) {
            $_SESSION['error'] = 'Please login to book an appointment.';
            header('Location: ' . $basePath . '/login.html?next=customer/booking.html');
            exit;
        }
        $services = $db->query("SELECT * FROM services WHERE status='active'")->fetchAll(PDO::FETCH_OBJ);
        $stylists = $db->query("SELECT * FROM stylists WHERE status='active'")->fetchAll(PDO::FETCH_OBJ);
        $selSvc = $_GET['service'] ?? '';
        $selSty = $_GET['stylist'] ?? '';
        renderBlade('customer.booking', compact('services', 'stylists', 'selSvc', 'selSty'));
        break;

    case 'customer/appointments':
    case 'customer/appointments.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        requireCustomer($basePath);
        $u = Auth::user();
        $stmt = $db->prepare("SELECT a.*, s.name as service_name, st.name as stylist_name FROM appointments a LEFT JOIN services s ON a.service_id=s.id LEFT JOIN stylists st ON a.stylist_id=st.id WHERE a.customer_id=? ORDER BY a.appointment_date DESC");
        $stmt->execute([$u->id]);
        $apts = $stmt->fetchAll(PDO::FETCH_OBJ);
        renderBlade('customer.appointments', ['user' => $u, 'appointments' => collect($apts)]);
        break;

    case 'customer/profile':
    case 'customer/profile.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        requireCustomer($basePath);
        $u = Auth::user();
        renderBlade('customer.profile', ['user' => $u]);
        break;

    case 'admin/dashboard':
    case 'admin/dashboard.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $totalCustomers = $db->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch()['c'];
        $totalServices = $db->query("SELECT COUNT(*) as c FROM services WHERE status='active'")->fetch()['c'];
        $totalStylists = $db->query("SELECT COUNT(*) as c FROM stylists WHERE status='active'")->fetch()['c'];
        $totalBookings = $db->query("SELECT COUNT(*) as c FROM appointments")->fetch()['c'];

        $pendingBookings = $db->query("SELECT COUNT(*) as c FROM appointments WHERE status='Pending'")->fetch()['c'];
        $confirmedBookings = $db->query("SELECT COUNT(*) as c FROM appointments WHERE status='Confirmed'")->fetch()['c'];
        $completedBookings = $db->query("SELECT COUNT(*) as c FROM appointments WHERE status='Completed'")->fetch()['c'];
        $cancelledBookings = $db->query("SELECT COUNT(*) as c FROM appointments WHERE status='Cancelled'")->fetch()['c'];

        $totalRevenue = $db->query("SELECT SUM(amount) as s FROM appointments WHERE status IN ('Confirmed', 'Completed')")->fetch()['s'] ?? 0;

        $stmtRec = $db->query("SELECT a.*, u.name as customer_name, s.name as service_name, st.name as stylist_name FROM appointments a LEFT JOIN users u ON a.customer_id=u.id LEFT JOIN services s ON a.service_id=s.id LEFT JOIN stylists st ON a.stylist_id=st.id ORDER BY a.created_at DESC LIMIT 6");
        $recentAppointments = $stmtRec->fetchAll(PDO::FETCH_OBJ);

        renderBlade('admin.dashboard', compact(
            'totalCustomers', 'totalServices', 'totalStylists', 'totalBookings',
            'pendingBookings', 'confirmedBookings', 'completedBookings', 'cancelledBookings',
            'totalRevenue', 'recentAppointments'
        ));
        break;

    case 'admin/customers':
    case 'admin/clients.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $q = $_GET['q'] ?? '';
        $sql = "SELECT * FROM users WHERE role='customer'";
        $params = [];
        if (!empty($q)) {
            $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params = ["%$q%", "%$q%", "%$q%"];
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
        renderBlade('admin.customers', compact('customers', 'q'));
        break;

    case 'admin/services':
    case 'admin/services.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $services = $db->query("SELECT * FROM services")->fetchAll(PDO::FETCH_OBJ);
        renderBlade('admin.services', compact('services'));
        break;

    case 'admin/stylists':
    case 'admin/staff.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $stylists = $db->query("SELECT * FROM stylists")->fetchAll(PDO::FETCH_OBJ);
        renderBlade('admin.stylists', compact('stylists'));
        break;

    case 'admin/bookings':
    case 'admin/appointments.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $status = $_GET['status'] ?? 'All';
        $sql = "SELECT a.*, u.name as customer_name, u.phone as customer_phone, s.name as service_name, st.name as stylist_name FROM appointments a LEFT JOIN users u ON a.customer_id=u.id LEFT JOIN services s ON a.service_id=s.id LEFT JOIN stylists st ON a.stylist_id=st.id";
        $params = [];
        if ($status !== 'All') {
            $sql .= " WHERE a.status=?";
            $params[] = $status;
        }
        $sql .= " ORDER BY a.appointment_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $appointments = $stmt->fetchAll(PDO::FETCH_OBJ);
        renderBlade('admin.bookings', compact('appointments', 'status'));
        break;

    case 'admin/calendar':
    case 'admin/calendar.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Content-Type: text/html; charset=UTF-8");
        $calFile = __DIR__ . '/admin/calendar.html';
        if (file_exists($calFile)) {
            readfile($calFile);
            exit;
        }
        break;

    case 'admin/gallery':
    case 'admin/gallery.html':
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $items = $db->query("SELECT * FROM gallery_items ORDER BY display_order ASC")->fetchAll(PDO::FETCH_OBJ);
        renderBlade('admin.gallery', compact('items'));
        break;

    default:
        // Serve static file if exists
        $filePath = __DIR__ . '/' . $route;
        if (file_exists($filePath) && !is_dir($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'html' => 'text/html; charset=UTF-8',
                'css' => 'text/css; charset=UTF-8',
                'js' => 'application/javascript; charset=UTF-8',
                'json' => 'application/json; charset=UTF-8',
                'svg' => 'image/svg+xml',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                'ico' => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject'
            ];
            $mime = $mimeTypes[$ext] ?? 'text/plain';
            header("Content-Type: $mime");
            readfile($filePath);
            exit;
        }
        // Fallback to home page
        $services = $db->query("SELECT * FROM services WHERE status='active' LIMIT 11")->fetchAll(PDO::FETCH_OBJ);
        $stylists = $db->query("SELECT * FROM stylists WHERE status='active' LIMIT 5")->fetchAll(PDO::FETCH_OBJ);
        $gallery = $db->query("SELECT * FROM gallery_items WHERE status='active' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_OBJ);
        $reviews = $db->query("SELECT r.*, u.name as customer_name, s.name as service_name FROM reviews r LEFT JOIN users u ON r.customer_id=u.id LEFT JOIN services s ON r.service_id=s.id WHERE r.status='published' LIMIT 6")->fetchAll(PDO::FETCH_OBJ);
        renderBlade('home', compact('services', 'stylists', 'gallery', 'reviews'));
        break;
}
