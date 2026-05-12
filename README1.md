TUBES\_KPL\_NULL/

│

├── backend/

│   │

│   ├── config/

│   │   └── Database.php

│   │

│   ├── controllers/

│   │   ├── AuthController.php

│   │   ├── ObatController.php

│   │   └── TransaksiController.php

│   │

│   ├── models/

│   │   ├── Database.php

│   │   ├── User.php

│   │   ├── Obat.php

│   │   └── Transaction.php

│   │

│   ├── routes/

│   │   **├── login.php**

**│   │   ├── logout.php**

│   │   ├── obat.php

│   │   └── transaction.php

│   │

│   ├── helpers/

│   │   ├── Validasi.php

│   │   ├── Response.php

│   │   └── Performance.php

│   │

│   ├── middleware/

│   │   └── AuthMiddleware.php

│   │

│   └── tests/

│       ├**── AuthTest.php**

│       ├── ObatTest.php

│       ├── LoginTest.php

│       └── ObatTest.php

│

├── frontend/

│   │

│   ├── pages/

│   │   ├── login.php

│   │   ├── dashboard.php

│   │   ├── transaksi.php

│   │   ├── logout.php

│   │   └── update.php

│   │

│   ├── js/

│   │   ├── login.js

│   │   ├── modal.js

│   │   ├── search.js

│   │   └── transaction.js

│   │

│   ├── css/

│   │   └── style.css

│   │

│   └── uploads/

│

├── database/

│   │

│   └── apotek\_db.sql

│

├── index.php

│

├── hash.php

│

├── README.md

│

&#x20;.gitignore



***/backend/config Database.php***

<?php



class Database {



\&#x20;   private $host = "localhost";

\&#x20;   private $db\\\_name = "apotek\\\_db";

\&#x20;   private $username = "root";

\&#x20;   private $password = "";



\&#x20;   private $connection;



\&#x20;   public function connect() {



\&#x20;       if ($this->connection === null) {



\&#x20;           try {



\&#x20;               $this->connection = new PDO(

\&#x20;                   "mysql:host={$this->host};dbname={$this->db\\\_name};charset=utf8",

\&#x20;                   $this->username,

\&#x20;                   $this->password

\&#x20;               );



\&#x20;               $this->connection->setAttribute(

\&#x20;                   PDO::ATTR\\\_ERRMODE,

\&#x20;                   PDO::ERRMODE\\\_EXCEPTION

\&#x20;               );



\&#x20;               $this->connection->setAttribute(

\&#x20;                   PDO::ATTR\\\_DEFAULT\\\_FETCH\\\_MODE,

\&#x20;                   PDO::FETCH\\\_ASSOC

\&#x20;               );



\&#x20;           } catch (PDOException $e) {



\&#x20;               die("Koneksi database gagal : " . $e->getMessage());

\&#x20;           }

\&#x20;       }



\&#x20;       return $this->connection;

\&#x20;   }

}


\*\*\*/backend/controllers AuthController.php\*\*\*
<?php





require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../models/User.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../helpers/Validator.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../helpers/Response.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../helpers/Performance.php';



class AuthController {



\&#x20;   private $userModel;



\&#x20;   public function \\\_\\\_construct() {

\&#x20;       

\&#x20;       if (session\\\_status() === PHP\\\_SESSION\\\_NONE) {

\&#x20;           session\\\_start();

\&#x20;       }

\&#x20;       $this->userModel = new User();

\&#x20;   }



\&#x20;   public function login() {

\&#x20;       try {

\&#x20;           

\&#x20;           error\\\_log("Login attempt: " . print\\\_r($\\\_POST, true));

\&#x20;           if ($\\\_SERVER\\\['REQUEST\\\_METHOD'] !== 'POST') {

\&#x20;               return Response::json(false, "Method tidak diizinkan");

\&#x20;           }

\&#x20;           

\&#x20;           if (empty($\\\_POST\\\['username']) || empty($\\\_POST\\\['password'])) {

\&#x20;               return Response::json(false, "Username dan password wajib diisi");

\&#x20;           }

\&#x20;           

\&#x20;           Validator::required($\\\_POST\\\['username'] ?? '', 'Username');

\&#x20;           Validator::required($\\\_POST\\\['password'] ?? '', 'Password');



\&#x20;           $user = $this->userModel->login(

\&#x20;               $\\\_POST\\\['username'],

\&#x20;               $\\\_POST\\\['password']

\&#x20;           );



\&#x20;           if (!$user) {

\&#x20;               error\\\_log("Login failed for user: " . $\\\_POST\\\['username']);

\&#x20;               return Response::json(false, "Username / Password salah");

\&#x20;           }



\&#x20;           session\\\_regenerate\\\_id(true);

\&#x20;           

\&#x20;           $\\\_SESSION\\\['login'] = true;

\&#x20;           $\\\_SESSION\\\['username'] = $user\\\['username'];

\&#x20;           $\\\_SESSION\\\['user\\\_id'] = $user\\\['id'] ?? 1;

\&#x20;           

\&#x20;           error\\\_log("Login success for user: " . $user\\\['username']);



\&#x20;           return Response::json(true, "Login berhasil");



\&#x20;       } catch (Exception $e) {

\&#x20;           error\\\_log("Login error: " . $e->getMessage());

\&#x20;           return Response::json(false, $e->getMessage());

\&#x20;       }

\&#x20;   }



\&#x20;   public function logout() {

\&#x20;       if (session\\\_status() === PHP\\\_SESSION\\\_NONE) {

\&#x20;           session\\\_start();

\&#x20;       }

\&#x20;       



\&#x20;       $\\\_SESSION = \\\[];

\&#x20;       

\&#x20;       if (ini\\\_get("session.use\\\_cookies")) {

\&#x20;           $params = session\\\_get\\\_cookie\\\_params();

\&#x20;           setcookie(session\\\_name(), '', time() - 42000,

\&#x20;               $params\\\["path"], $params\\\["domain"],

\&#x20;               $params\\\["secure"], $params\\\["httponly"]

\&#x20;           );

\&#x20;       }

\&#x20;       

\&#x20;       session\\\_destroy();

\&#x20;       

\&#x20;       if (!empty($\\\_SERVER\\\['HTTP\\\_X\\\_REQUESTED\\\_WITH']) \\\&\\\& strtolower($\\\_SERVER\\\['HTTP\\\_X\\\_REQUESTED\\\_WITH']) == 'xmlhttprequest') {

\&#x20;           return Response::json(true, "Logout berhasil");

\&#x20;       } else {

\&#x20;           header("Location: ../../frontend/pages/login.php");

\&#x20;           exit;

\&#x20;       }

\&#x20;   }

}


\*\*\*/backend/controllers ObatController.php\*\*\*
<?php

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../models/Obat.php';



class ObatController {

\&#x20;   private $obatModel;



\&#x20;   public function \\\_\\\_construct() {

\&#x20;       $this->obatModel = new Obat();

\&#x20;   }



\&#x20;   public function index() {

\&#x20;       $data = $this->obatModel->getAll();

\&#x20;       require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../../frontend/pages/dashboard.php';

\&#x20;   }



\&#x20;   public function search() {

\&#x20;       $keyword = $\\\_GET\\\['keyword'] ?? '';

\&#x20;       if (empty($keyword)) {

\&#x20;           $data = $this->obatModel->getAll();

\&#x20;       } else {

\&#x20;           $data = $this->obatModel->search($keyword);

\&#x20;       }

\&#x20;       header('Content-Type: application/json');

\&#x20;       echo json\\\_encode($data);

\&#x20;       exit;

\&#x20;   }



\&#x20;   public function store() {

\&#x20;       $nama = $\\\_POST\\\['nama\\\_obat'];

\&#x20;       $kategori = $\\\_POST\\\['kategori'];

\&#x20;       $stok = $\\\_POST\\\['stok'];

\&#x20;       $harga = $\\\_POST\\\['harga'];

\&#x20;       

\&#x20;       $gambar = "";

\&#x20;       if (isset($\\\_FILES\\\['gambar']) \\\&\\\& $\\\_FILES\\\['gambar']\\\['error'] == 0) {

\&#x20;           $targetDir = "../../frontend/uploads/";

\&#x20;           if (!file\\\_exists($targetDir)) {

\&#x20;               mkdir($targetDir, 0777, true);

\&#x20;           }

\&#x20;           $gambar = time() . "\\\_" . $\\\_FILES\\\['gambar']\\\['name'];

\&#x20;           move\\\_uploaded\\\_file($\\\_FILES\\\['gambar']\\\['tmp\\\_name'], $targetDir . $gambar);

\&#x20;       }



\&#x20;       $this->obatModel->create($nama, $kategori, $stok, $harga, $gambar);

\&#x20;       header("Location: ../../frontend/pages/dashboard.php");

\&#x20;       exit;

\&#x20;   }



\&#x20;   public function edit() {

\&#x20;       $id = $\\\_POST\\\['id'];

\&#x20;       $nama = $\\\_POST\\\['nama\\\_obat'];

\&#x20;       $kategori = $\\\_POST\\\['kategori'];

\&#x20;       $stok = $\\\_POST\\\['stok'];

\&#x20;       $harga = $\\\_POST\\\['harga'];

\&#x20;       

\&#x20;       $obatLama = $this->obatModel->getById($id);

\&#x20;       $gambar = $obatLama\\\['gambar'];



\&#x20;       if (isset($\\\_FILES\\\['gambar']) \\\&\\\& $\\\_FILES\\\['gambar']\\\['error'] == 0) {

\&#x20;           $targetDir = "../../frontend/uploads/";

\&#x20;           if (!file\\\_exists($targetDir)) {

\&#x20;               mkdir($targetDir, 0777, true);

\&#x20;           }

\&#x20;           $gambar = time() . "\\\_" . $\\\_FILES\\\['gambar']\\\['name'];

\&#x20;           move\\\_uploaded\\\_file($\\\_FILES\\\['gambar']\\\['tmp\\\_name'], $targetDir . $gambar);

\&#x20;       }



\&#x20;       $this->obatModel->update($id, $nama, $kategori, $stok, $harga, $gambar);

\&#x20;       header("Location: ../../frontend/pages/dashboard.php");

\&#x20;       exit;

\&#x20;   }



\&#x20;   public function delete() {

\&#x20;       if (isset($\\\_GET\\\['id'])) {

\&#x20;           $this->obatModel->delete($\\\_GET\\\['id']);

\&#x20;       }

\&#x20;       header("Location: ../../frontend/pages/dashboard.php");

\&#x20;       exit;

\&#x20;   }

}

\*\*\*/backend/controllers PerformanceTestController.php\*\*\*
<?php



session\\\_start();



require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../models/Obat.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../models/User.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../helpers/PerformanceLogger.php';



class PerformanceTestController {

\&#x20;   

\&#x20;   private $obatModel;

\&#x20;   private $userModel;

\&#x20;   

\&#x20;   public function \\\_\\\_construct() {

\&#x20;       $this->obatModel = new Obat();

\&#x20;       $this->userModel = new User();

\&#x20;   }

\&#x20;   

\&#x20;   // Test database connection performance

\&#x20;   public function testDatabaseConnection() {

\&#x20;       PerformanceLogger::start('db\\\_connection');

\&#x20;       

\&#x20;       $db = new Database();

\&#x20;       $conn = $db->connect();

\&#x20;       

\&#x20;       PerformanceLogger::end('db\\\_connection');

\&#x20;       

\&#x20;       return PerformanceLogger::getLog('db\\\_connection');

\&#x20;   }

\&#x20;   

\&#x20;   // Test getAll performance with different limits

\&#x20;   public function testGetAllPerformance() {

\&#x20;       $results = \\\[];

\&#x20;       

\&#x20;       // Test multiple times to get average

\&#x20;       for ($i = 1; $i <= 5; $i++) {

\&#x20;           $start = PerformanceLogger::start("getAll\\\_run\\\_{$i}");

\&#x20;           $data = $this->obatModel->getAll();

\&#x20;           PerformanceLogger::end("getAll\\\_run\\\_{$i}");

\&#x20;           $results\\\[$i] = count($data);

\&#x20;       }

\&#x20;       

\&#x20;       return PerformanceLogger::getLogs();

\&#x20;   }

\&#x20;   

\&#x20;   // Test login performance

\&#x20;   public function testLoginPerformance() {

\&#x20;       $testCredentials = \\\[

\&#x20;           \\\['admin', 'admin123'],

\&#x20;           \\\['wrong', 'password'],

\&#x20;           \\\['admin', 'wrongpass']

\&#x20;       ];

\&#x20;       

\&#x20;       foreach ($testCredentials as $cred) {

\&#x20;           $start = PerformanceLogger::start("login\\\_{$cred\\\[0]}");

\&#x20;           $result = $this->userModel->login($cred\\\[0], $cred\\\[1]);

\&#x20;           PerformanceLogger::end("login\\\_{$cred\\\[0]}");

\&#x20;       }

\&#x20;       

\&#x20;       return PerformanceLogger::getLogs();

\&#x20;   }

\&#x20;   

\&#x20;   // Comprehensive performance report

\&#x20;   public function runAllTests() {

\&#x20;       PerformanceLogger::enable();

\&#x20;       

\&#x20;       // Clear previous logs

\&#x20;       $testResults = \\\[];

\&#x20;       

\&#x20;       // 1. Test database connection

\&#x20;       $testResults\\\['db\\\_connection'] = $this->testDatabaseConnection();

\&#x20;       

\&#x20;       // 2. Test getAll

\&#x20;       $testResults\\\['getAll'] = $this->testGetAllPerformance();

\&#x20;       

\&#x20;       // 3. Test login

\&#x20;       $testResults\\\['login'] = $this->testLoginPerformance();

\&#x20;       

\&#x20;       // 4. Test search

\&#x20;       $testResults\\\['search'] = $this->obatModel->searchPerformanceTest();

\&#x20;       

\&#x20;       // Generate report

\&#x20;       return \\\[

\&#x20;           'timestamp' => date('Y-m-d H:i:s'),

\&#x20;           'results' => $testResults,

\&#x20;           'summary' => $this->generateSummary(),

\&#x20;           'recommendations' => $this->generateRecommendations()

\&#x20;       ];

\&#x20;   }

\&#x20;   

\&#x20;   private function generateSummary() {

\&#x20;       $logs = PerformanceLogger::getLogs();

\&#x20;       $summary = \\\[

\&#x20;           'total\\\_tests' => count($logs),

\&#x20;           'avg\\\_execution\\\_time' => 0,

\&#x20;           'avg\\\_memory\\\_usage' => 0,

\&#x20;           'fastest\\\_test' => \\\['name' => '', 'time' => PHP\\\_FLOAT\\\_MAX],

\&#x20;           'slowest\\\_test' => \\\['name' => '', 'time' => 0]

\&#x20;       ];

\&#x20;       

\&#x20;       $totalTime = 0;

\&#x20;       foreach ($logs as $name => $log) {

\&#x20;           $totalTime += $log\\\['execution\\\_time'];

\&#x20;           if ($log\\\['execution\\\_time'] < $summary\\\['fastest\\\_test']\\\['time']) {

\&#x20;               $summary\\\['fastest\\\_test'] = \\\['name' => $name, 'time' => $log\\\['execution\\\_time']];

\&#x20;           }

\&#x20;           if ($log\\\['execution\\\_time'] > $summary\\\['slowest\\\_test']\\\['time']) {

\&#x20;               $summary\\\['slowest\\\_test'] = \\\['name' => $name, 'time' => $log\\\['execution\\\_time']];

\&#x20;           }

\&#x20;       }

\&#x20;       

\&#x20;       $summary\\\['avg\\\_execution\\\_time'] = $totalTime / $summary\\\['total\\\_tests'];

\&#x20;       

\&#x20;       return $summary;

\&#x20;   }

\&#x20;   

\&#x20;   private function generateRecommendations() {

\&#x20;       $logs = PerformanceLogger::getLogs();

\&#x20;       $recommendations = \\\[];

\&#x20;       

\&#x20;       foreach ($logs as $name => $log) {

\&#x20;           $timeMs = $log\\\['execution\\\_time'] \\\* 1000;

\&#x20;           

\&#x20;           if ($timeMs > 500) {

\&#x20;               $recommendations\\\[] = "⚠️ {$name} terlalu lambat ({$timeMs}ms). Pertimbangkan optimasi query.";

\&#x20;           } elseif ($timeMs > 100) {

\&#x20;               $recommendations\\\[] = "📝 {$name} cukup lambat ({$timeMs}ms). Bisa dioptimasi.";

\&#x20;           } else {

\&#x20;               $recommendations\\\[] = "✅ {$name} performa baik ({$timeMs}ms).";

\&#x20;           }

\&#x20;           

\&#x20;           if ($log\\\['memory\\\_used'] > 5 \\\* 1024 \\\* 1024) { // > 5MB

\&#x20;               $recommendations\\\[] = "💾 {$name} menggunakan memory besar (" . round($log\\\['memory\\\_used'] / 1024 / 1024, 2) . "MB)";

\&#x20;           }

\&#x20;       }

\&#x20;       

\&#x20;       return $recommendations;

\&#x20;   }

}


\*\*\*/backend/controllers TransaksiController.php\*\*\*
<?php



session\\\_start();



require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../models/Transaksi.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../models/Obat.php';



class TransaksiController {



\&#x20;   private $transaksiModel;

\&#x20;   private $obatModel;



\&#x20;   public function \\\_\\\_construct() {



\&#x20;       $this->transaksiModel = new Transaksi();

\&#x20;       $this->obatModel      = new Obat();



\&#x20;       if (!isset($\\\_SESSION\\\['cart'])) {



\&#x20;           $\\\_SESSION\\\['cart'] = \\\[];

\&#x20;       }

\&#x20;   }



\&#x20;   /\\\*

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   | ADD TO CART

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   \\\*/



\&#x20;   public function tambah() {



\&#x20;       $id\\\_obat = (int) ($\\\_POST\\\['id\\\_obat'] ?? 0);

\&#x20;       $jumlah  = (int) ($\\\_POST\\\['jumlah'] ?? 0);



\&#x20;       if ($id\\\_obat <= 0 || $jumlah <= 0) {



\&#x20;           return \\\[

\&#x20;               "success" => false,

\&#x20;               "message" => "Input transaksi tidak valid"

\&#x20;           ];

\&#x20;       }



\&#x20;       $obat = $this->obatModel->getById($id\\\_obat);



\&#x20;       if (!$obat) {



\&#x20;           return \\\[

\&#x20;               "success" => false,

\&#x20;               "message" => "Obat tidak ditemukan"

\&#x20;           ];

\&#x20;       }



\&#x20;       if ($jumlah > $obat\\\['stok']) {



\&#x20;           return \\\[

\&#x20;               "success" => false,

\&#x20;               "message" => "Stok tidak mencukupi"

\&#x20;           ];

\&#x20;       }



\&#x20;       $\\\_SESSION\\\['cart']\\\[] = \\\[



\&#x20;           "id"       => $obat\\\['id'],

\&#x20;           "nama"     => $obat\\\['nama\\\_obat'],

\&#x20;           "harga"    => $obat\\\['harga'],

\&#x20;           "jumlah"   => $jumlah,

\&#x20;           "subtotal" => $obat\\\['harga'] \\\* $jumlah

\&#x20;       ];



\&#x20;       return \\\[

\&#x20;           "success" => true,

\&#x20;           "message" => "Berhasil tambah ke keranjang"

\&#x20;       ];

\&#x20;   }



\&#x20;   /\\\*

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   | CHECKOUT

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   \\\*/



\&#x20;   public function bayar() {



\&#x20;       if (empty($\\\_SESSION\\\['cart'])) {



\&#x20;           return \\\[

\&#x20;               "success" => false,

\&#x20;               "message" => "Keranjang kosong"

\&#x20;           ];

\&#x20;       }



\&#x20;       foreach ($\\\_SESSION\\\['cart'] as $item) {



\&#x20;           $this->transaksiModel->create(

\&#x20;               $item\\\['id'],

\&#x20;               $item\\\['jumlah'],

\&#x20;               $item\\\['subtotal']

\&#x20;           );



\&#x20;           $this->obatModel->reduceStock(

\&#x20;               $item\\\['id'],

\&#x20;               $item\\\['jumlah']

\&#x20;           );

\&#x20;       }



\&#x20;       unset($\\\_SESSION\\\['cart']);



\&#x20;       return \\\[

\&#x20;           "success" => true,

\&#x20;           "message" => "Pembayaran berhasil"

\&#x20;       ];

\&#x20;   }



\&#x20;   /\\\*

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   | CANCEL

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   \\\*/



\&#x20;   public function batal() {



\&#x20;       unset($\\\_SESSION\\\['cart']);



\&#x20;       return \\\[

\&#x20;           "success" => true,

\&#x20;           "message" => "Transaksi dibatalkan"

\&#x20;       ];

\&#x20;   }

}


\*\*\*/backend/helpers performance.php\*\*\*
<?php



class Performance {



\&#x20;   public static function start() {

\&#x20;       return microtime(true);

\&#x20;   }



\&#x20;   public static function end($startTime) {

\&#x20;       $endTime = microtime(true);

\&#x20;       return round(($endTime - $startTime), 5);

\&#x20;   }

\&#x20;   

\&#x20;   // Tambahan: hitung waktu eksekusi dalam milliseconds

\&#x20;   public static function measure($callback) {

\&#x20;       $start = self::start();

\&#x20;       $result = $callback();

\&#x20;       $time = self::end($start);

\&#x20;       

\&#x20;       return \\\[

\&#x20;           'result' => $result,

\&#x20;           'time\\\_seconds' => $time,

\&#x20;           'time\\\_ms' => $time \\\* 1000

\&#x20;       ];

\&#x20;   }

\&#x20;   

\&#x20;   // Tambahan: benchmark multiple runs

\&#x20;   public static function benchmark($callback, $runs = 10) {

\&#x20;       $times = \\\[];

\&#x20;       

\&#x20;       for ($i = 0; $i < $runs; $i++) {

\&#x20;           $start = self::start();

\&#x20;           $callback();

\&#x20;           $times\\\[] = self::end($start);

\&#x20;       }

\&#x20;       

\&#x20;       return \\\[

\&#x20;           'min' => min($times) \\\* 1000,

\&#x20;           'max' => max($times) \\\* 1000,

\&#x20;           'avg' => (array\\\_sum($times) / $runs) \\\* 1000,

\&#x20;           'runs' => $runs

\&#x20;       ];

\&#x20;   }

}
\*/\*\*backend/helpers\*\*\* <b>performanceLogger.php</b>
<?php



class PerformanceLogger {

\&#x20;   

\&#x20;   private static $logs = \\\[];

\&#x20;   private static $isEnabled = true;

\&#x20;   

\&#x20;   // Enable/disable performance logging

\&#x20;   public static function enable() {

\&#x20;       self::$isEnabled = true;

\&#x20;   }

\&#x20;   

\&#x20;   public static function disable() {

\&#x20;       self::$isEnabled = false;

\&#x20;   }

\&#x20;   

\&#x20;   // Start measuring time and memory

\&#x20;   public static function start($operationName) {

\&#x20;       if (!self::$isEnabled) return null;

\&#x20;       

\&#x20;       self::$logs\\\[$operationName] = \\\[

\&#x20;           'start\\\_time' => microtime(true),

\&#x20;           'start\\\_memory' => memory\\\_get\\\_usage(),

\&#x20;           'end\\\_time' => null,

\&#x20;           'end\\\_memory' => null,

\&#x20;           'execution\\\_time' => null,

\&#x20;           'memory\\\_used' => null

\&#x20;       ];

\&#x20;       

\&#x20;       return $operationName;

\&#x20;   }

\&#x20;   

\&#x20;   // End measuring

\&#x20;   public static function end($operationName) {

\&#x20;       if (!self::$isEnabled || !isset(self::$logs\\\[$operationName])) return null;

\&#x20;       

\&#x20;       self::$logs\\\[$operationName]\\\['end\\\_time'] = microtime(true);

\&#x20;       self::$logs\\\[$operationName]\\\['end\\\_memory'] = memory\\\_get\\\_usage();

\&#x20;       self::$logs\\\[$operationName]\\\['execution\\\_time'] = 

\&#x20;           self::$logs\\\[$operationName]\\\['end\\\_time'] - self::$logs\\\[$operationName]\\\['start\\\_time'];

\&#x20;       self::$logs\\\[$operationName]\\\['memory\\\_used'] = 

\&#x20;           self::$logs\\\[$operationName]\\\['end\\\_memory'] - self::$logs\\\[$operationName]\\\['start\\\_memory'];

\&#x20;       

\&#x20;       return self::$logs\\\[$operationName];

\&#x20;   }

\&#x20;   

\&#x20;   // Get all logs

\&#x20;   public static function getLogs() {

\&#x20;       return self::$logs;

\&#x20;   }

\&#x20;   

\&#x20;   // Get specific log

\&#x20;   public static function getLog($operationName) {

\&#x20;       return self::$logs\\\[$operationName] ?? null;

\&#x20;   }

\&#x20;   

\&#x20;   // Display formatted report

\&#x20;   public static function displayReport() {

\&#x20;       echo "<pre>";

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       echo "|                    PERFORMANCE TESTING REPORT                    |\\\\n";

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       

\&#x20;       foreach (self::$logs as $name => $log) {

\&#x20;           $timeMs = round($log\\\['execution\\\_time'] \\\* 1000, 2);

\&#x20;           $memoryKb = round($log\\\['memory\\\_used'] / 1024, 2);

\&#x20;           $memoryMb = round($memoryKb / 1024, 2);

\&#x20;           

\&#x20;           $status = $timeMs < 100 ? "✅" : ($timeMs < 500 ? "⚠️" : "❌");

\&#x20;           

\&#x20;           echo sprintf(

\&#x20;               "║ %s %-30s : %8.2f ms  |  %8.2f KB  (%5.2f MB) ║\\\\n",

\&#x20;               $status,

\&#x20;               $name,

\&#x20;               $timeMs,

\&#x20;               $memoryKb,

\&#x20;               $memoryMb

\&#x20;           );

\&#x20;       }

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       echo "</pre>";

\&#x20;   }

\&#x20;   

\&#x20;   // Save logs to file

\&#x20;   public static function saveToFile($filename = "performance\\\_log.json") {

\&#x20;       $data = \\\[

\&#x20;           'timestamp' => date('Y-m-d H:i:s'),

\&#x20;           'logs' => self::$logs

\&#x20;       ];

\&#x20;       file\\\_put\\\_contents(\\\_\\\_DIR\\\_\\\_ . '/../../logs/' . $filename, json\\\_encode($data, JSON\\\_PRETTY\\\_PRINT));

\&#x20;   }

}


\*\*\*/backend/helpers response.php\*\*\*
<?php



class Response {



\&#x20;   public static function json(

\&#x20;       $success,

\&#x20;       $message,

\&#x20;       $data = \\\[]

\&#x20;   ) {



\&#x20;       header('Content-Type: application/json');



\&#x20;       echo json\\\_encode(\\\[



\&#x20;           "success" => $success,

\&#x20;           "message" => $message,

\&#x20;           "data"    => $data



\&#x20;       ]);



\&#x20;       exit;

\&#x20;   }

}


\*\*\*/backend/helpers validator.php\*\*\*
<?php



class Validator {



\&#x20;   /\\\*

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   | REQUIRED

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   \\\*/



\&#x20;   public static function required($value, $fieldName) {



\&#x20;       if(empty(trim($value))) {



\&#x20;           throw new Exception(

\&#x20;               $fieldName . " wajib diisi"

\&#x20;           );

\&#x20;       }

\&#x20;   }



\&#x20;   /\\\*

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   | NUMERIC

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   \\\*/



\&#x20;   public static function numeric($value, $fieldName) {



\&#x20;       if(!is\\\_numeric($value)) {



\&#x20;           throw new Exception(

\&#x20;               $fieldName . " harus berupa angka"

\&#x20;           );

\&#x20;       }

\&#x20;   }



\&#x20;   /\\\*

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   | MIN VALUE

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   \\\*/



\&#x20;   public static function min($value, $min, $fieldName) {



\&#x20;       if($value < $min) {



\&#x20;           throw new Exception(

\&#x20;               $fieldName .

\&#x20;               " minimal " .

\&#x20;               $min

\&#x20;           );

\&#x20;       }

\&#x20;   }



\&#x20;   /\\\*

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   | MAX LENGTH

\&#x20;   |--------------------------------------------------------------------------

\&#x20;   \\\*/



\&#x20;   public static function maxLength($value, $max, $fieldName) {



\&#x20;       if(strlen($value) > $max) {



\&#x20;           throw new Exception(

\&#x20;               $fieldName .

\&#x20;               " maksimal " .

\&#x20;               $max .

\&#x20;               " karakter"

\&#x20;           );

\&#x20;       }

\&#x20;   }

}


\*\*\*/backend/middleware Authmiddleware.php\*\*\*
<?php



class AuthMiddleware {



\&#x20;   public static function check() {



\&#x20;       session\\\_start();



\&#x20;       if(!isset($\\\_SESSION\\\['login'])) {



\&#x20;           header("Location: ../frontend/pages/login.php");

\&#x20;           exit;

\&#x20;       }

\&#x20;   }

}


\*\*\*/backend/models Obat.php\*\*\*
<?php

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../config/Database.php';



class Obat {

\&#x20;   private $conn;



\&#x20;   public function \\\_\\\_construct() {

\&#x20;       $database = new Database();

\&#x20;       $this->conn = $database->connect();

\&#x20;   }



\&#x20;   public function getAll() {

\&#x20;       $query = "SELECT \\\* FROM obat ORDER BY id DESC";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->execute();

\&#x20;       return $stmt->fetchAll();

\&#x20;   }



\&#x20;   public function search($keyword) {

\&#x20;       $query = "SELECT \\\* FROM obat WHERE nama\\\_obat LIKE :keyword OR kategori LIKE :keyword ORDER BY id DESC";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $searchTerm = "%{$keyword}%";

\&#x20;       $stmt->bindParam(':keyword', $searchTerm);

\&#x20;       $stmt->execute();

\&#x20;       return $stmt->fetchAll();

\&#x20;   }

\&#x20; 



\&#x20;   public function getById($id) {

\&#x20;       $query = "SELECT \\\* FROM obat WHERE id = :id";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(':id', $id);

\&#x20;       $stmt->execute();

\&#x20;       return $stmt->fetch();

\&#x20;   }



\&#x20;   public function create($nama, $kategori, $stok, $harga, $gambar) {

\&#x20;       $query = "INSERT INTO obat (nama\\\_obat, kategori, stok, harga, gambar) 

\&#x20;                 VALUES (:nama, :kategori, :stok, :harga, :gambar)";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(':nama', $nama);

\&#x20;       $stmt->bindParam(':kategori', $kategori);

\&#x20;       $stmt->bindParam(':stok', $stok);

\&#x20;       $stmt->bindParam(':harga', $harga);

\&#x20;       $stmt->bindParam(':gambar', $gambar);

\&#x20;       return $stmt->execute();

\&#x20;   }



\&#x20;   public function update($id, $nama, $kategori, $stok, $harga, $gambar) {

\&#x20;       $query = "UPDATE obat SET nama\\\_obat=:nama, kategori=:kategori, stok=:stok, harga=:harga, gambar=:gambar WHERE id=:id";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(':id', $id);

\&#x20;       $stmt->bindParam(':nama', $nama);

\&#x20;       $stmt->bindParam(':kategori', $kategori);

\&#x20;       $stmt->bindParam(':stok', $stok);

\&#x20;       $stmt->bindParam(':harga', $harga);

\&#x20;       $stmt->bindParam(':gambar', $gambar);

\&#x20;       return $stmt->execute();

\&#x20;   }



\&#x20;   public function delete($id) {

\&#x20;       $query = "DELETE FROM obat WHERE id = :id";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(':id', $id);

\&#x20;       return $stmt->execute();

\&#x20;   }



\&#x20;   public function reduceStock($id, $jumlah) {

\&#x20;       $query = "UPDATE obat SET stok = stok - :jumlah WHERE id = :id AND stok >= :jumlah";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(':id', $id);

\&#x20;       $stmt->bindParam(':jumlah', $jumlah);

\&#x20;       return $stmt->execute();

\&#x20;   }

}


\*\*\*/backend/models transaksi.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . "/../config/Database.php";



class Transaksi {



\&#x20;   private $conn;



\&#x20;   public function \\\_\\\_construct() {

\&#x20;       $database = new Database();

\&#x20;       $this->conn = $database->connect();

\&#x20;   }



\&#x20;   public function create($id\\\_obat, $jumlah, $total) {

\&#x20;       $query = "INSERT INTO transaksi (id\\\_obat, jumlah, total, tanggal) 

\&#x20;                 VALUES (:id\\\_obat, :jumlah, :total, NOW())";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(':id\\\_obat', $id\\\_obat);

\&#x20;       $stmt->bindParam(':jumlah', $jumlah);

\&#x20;       $stmt->bindParam(':total', $total);

\&#x20;       return $stmt->execute();

\&#x20;   }



\&#x20;   public function getAll() {

\&#x20;       $query = "SELECT t.\\\*, o.nama\\\_obat FROM transaksi t 

\&#x20;                 JOIN obat o ON t.id\\\_obat = o.id 

\&#x20;                 ORDER BY t.id DESC";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->execute();

\&#x20;       return $stmt->fetchAll();

\&#x20;   }

}


\*\*\*/backend/models user.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . "/../config/Database.php";



class User {



\&#x20;   private $conn;



\&#x20;   public function \\\_\\\_construct() {

\&#x20;       $database = new Database();

\&#x20;       $this->conn = $database->connect();

\&#x20;   }



\&#x20;   public function login($username, $password) {

\&#x20;       if (empty($username) || empty($password)) {

\&#x20;           error\\\_log("Login: username or password empty");

\&#x20;           return false;

\&#x20;       }



\&#x20;       $query = "SELECT \\\* FROM users WHERE username = :username LIMIT 1";



\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(":username", $username);

\&#x20;       $stmt->execute();



\&#x20;       $user = $stmt->fetch();



\&#x20;       if (!$user) {

\&#x20;           error\\\_log("Login: user not found - " . $username);

\&#x20;           return false;

\&#x20;       }



\&#x20;       if (!password\\\_verify($password, $user\\\['password'])) {

\&#x20;           error\\\_log("Login: password wrong for user - " . $username);

\&#x20;           return false;

\&#x20;       }



\&#x20;       error\\\_log("Login: success for user - " . $username);

\&#x20;       return $user;

\&#x20;   }

\&#x20;   

\&#x20;   public function createUser($username, $password) {

\&#x20;       $hashedPassword = password\\\_hash($password, PASSWORD\\\_DEFAULT);

\&#x20;       $query = "INSERT INTO users (username, password) VALUES (:username, :password)";

\&#x20;       $stmt = $this->conn->prepare($query);

\&#x20;       $stmt->bindParam(':username', $username);

\&#x20;       $stmt->bindParam(':password', $hashedPassword);

\&#x20;       return $stmt->execute();

\&#x20;   }

}


\*\*\*/backend/routes auth.php\*\*\*
<?php



require\\\_once "../controllers/AuthController.php";



$controller = new AuthController();



$action = $\\\_GET\\\['action'] ?? '';



switch ($action) {

\&#x20;   case 'login':

\&#x20;       $controller->login();

\&#x20;       break;

\&#x20;   case 'logout':

\&#x20;       $controller->logout();  // SEKARANG SUDAH ADA

\&#x20;       break;

\&#x20;   default:

\&#x20;       header('Content-Type: application/json');

\&#x20;       echo json\\\_encode(\\\[

\&#x20;           "success" => false,

\&#x20;           "message" => "Route tidak ditemukan"

\&#x20;       ]);

}


\*\*\*/backend/routes login.php\*\*\*
<?php



header("Content-Type: application/json");



error\\\_reporting(E\\\_ALL);

ini\\\_set('display\\\_errors', 1);



require\\\_once \\\_\\\_DIR\\\_\\\_ . "/../controllers/AuthController.php";



$auth = new AuthController();



try {

\&#x20;   $auth->login();

} catch (Exception $e) {

\&#x20;   http\\\_response\\\_code(500);

\&#x20;   echo json\\\_encode(\\\[

\&#x20;       "success" => false,

\&#x20;       "message" => "Server error: " . $e->getMessage()

\&#x20;   ]);

}


\*\*\*/backend/routes logout.php\*\*\*
<?php

if (session\\\_status() === PHP\\\_SESSION\\\_NONE) {

\&#x20;   session\\\_start();

}



$\\\_SESSION = \\\[];



session\\\_destroy();



// arahkan langsung ke login frontend

header("Location: ../../frontend/pages/login.php");

exit;

\*\*\*/backend/routes obat.php\*\*\*
<?php

require\\\_once '../controllers/ObatController.php';



$obat = new ObatController();

$action = $\\\_GET\\\['action'] ?? '';



switch($action) {

\&#x20;   case 'create':

\&#x20;       $obat->store();

\&#x20;       break;

\&#x20;   case 'edit':

\&#x20;       $obat->edit();

\&#x20;       break;

\&#x20;   case 'delete':

\&#x20;       $obat->delete();

\&#x20;       break;

\&#x20;   case 'search':        

\&#x20;       $obat->search();

\&#x20;       break;

\&#x20;   default:

\&#x20;       $obat->index();

\&#x20;       break;

}

\*\*\*/backend/routes performance.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../controllers/PerformanceTestController.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../helpers/PerformanceLogger.php';



$controller = new PerformanceTestController();

$action = $\\\_GET\\\['action'] ?? 'report';



switch ($action) {

\&#x20;   case 'run':

\&#x20;       // Run all tests and return JSON

\&#x20;       header('Content-Type: application/json');

\&#x20;       $results = $controller->runAllTests();

\&#x20;       echo json\\\_encode($results, JSON\\\_PRETTY\\\_PRINT);

\&#x20;       break;

\&#x20;       

\&#x20;   case 'report':

\&#x20;       // Display HTML report

\&#x20;       $controller->runAllTests();


&#x20;       <!DOCTYPE html>

&#x20;       <html>

&#x20;       <head>

&#x20;           <title>Performance Testing Report</title>

&#x20;           <style>

&#x20;               body {

&#x20;                   font-family: 'Courier New', monospace;

&#x20;                   background: #1e1e2e;

&#x20;                   color: #cdd6f4;

&#x20;                   padding: 20px;

&#x20;               }

&#x20;               .container {

&#x20;                   max-width: 1200px;

&#x20;                   margin: 0 auto;

&#x20;               }

&#x20;               .header {

&#x20;                   background: #313244;

&#x20;                   padding: 20px;

&#x20;                   border-radius: 10px;

&#x20;                   margin-bottom: 20px;

&#x20;               }

&#x20;               .test-card {

&#x20;                   background: #181825;

&#x20;                   border-radius: 10px;

&#x20;                   margin-bottom: 15px;

&#x20;                   overflow: hidden;

&#x20;               }

&#x20;               .test-header {

&#x20;                   background: #45475a;

&#x20;                   padding: 12px 20px;

&#x20;                   font-weight: bold;

&#x20;                   cursor: pointer;

&#x20;               }

&#x20;               .test-body {

&#x20;                   padding: 20px;

&#x20;                   display: none;

&#x20;               }

&#x20;               .test-body.show {

&#x20;                   display: block;

&#x20;               }

&#x20;               .metric {

&#x20;                   display: inline-block;

&#x20;                   background: #313244;

&#x20;                   padding: 5px 10px;

&#x20;                   border-radius: 5px;

&#x20;                   margin: 5px;

&#x20;                   font-size: 12px;

&#x20;               }

&#x20;               .good { color: #a6e3a1; }

&#x20;               .warning { color: #f9e2af; }

&#x20;               .bad { color: #f38ba8; }

&#x20;               pre {

&#x20;                   background: #11111b;

&#x20;                   padding: 15px;

&#x20;                   border-radius: 8px;

&#x20;                   overflow-x: auto;

&#x20;               }

&#x20;               button {

&#x20;                   background: #89b4fa;

&#x20;                   color: #1e1e2e;

&#x20;                   border: none;

&#x20;                   padding: 10px 20px;

&#x20;                   border-radius: 8px;

&#x20;                   cursor: pointer;

&#x20;                   font-weight: bold;

&#x20;                   margin-right: 10px;

&#x20;               }

&#x20;               button:hover {

&#x20;                   background: #b4befe;

&#x20;               }

&#x20;           </style>

&#x20;       </head>

&#x20;       <body>

&#x20;           <div class="container">

&#x20;               <div class="header">

&#x20;                   <h1>⚡ Performance Testing Report</h1>

&#x20;                   <p>Generated: <?= date('Y-m-d H:i:s') ?></p>

&#x20;                   <button onclick="runTests()">🔄 Run Tests Again</button>

&#x20;                   <button onclick="exportJSON()">📥 Export JSON</button>

&#x20;               </div>

&#x20;

&#x20;               <div id="reportContent">

&#x20;                   <?php PerformanceLogger::displayReport(); ?>

&#x20;               </div>

&#x20;

&#x20;               <div id="detailedReport"></div>

&#x20;           </div>

&#x20;

&#x20;           <script>

&#x20;               async function runTests() {

&#x20;                   document.getElementById('reportContent').innerHTML = '<div style="text-align:center">Running tests... ⏳</div>';

&#x20;

&#x20;                   const response = await fetch('?action=run');

&#x20;                   const data = await response.json();

&#x20;

&#x20;                   displayDetailedReport(data);

&#x20;               }

&#x20;

&#x20;               function displayDetailedReport(data) {

&#x20;                   let html = '<h2>📊 Detailed Analysis</h2>';

&#x20;

&#x20;                   for (const \[testName, result] of Object.entries(data.results)) {

&#x20;                       html += `

&#x20;                           <div class="test-card">

&#x20;                               <div class="test-header" onclick="toggleTest(this)">

&#x20;                                   📈 ${testName}

&#x20;                               </div>

&#x20;                               <div class="test-body">

&#x20;                                   <pre>${JSON.stringify(result, null, 2)}</pre>

&#x20;                               </div>

&#x20;                           </div>

&#x20;                       `;

&#x20;                   }

&#x20;

&#x20;                   html += `

&#x20;                       <div class="test-card">

&#x20;                           <div class="test-header" onclick="toggleTest(this)">

&#x20;                               📋 Summary

&#x20;                           </div>

&#x20;                           <div class="test-body">

&#x20;                               <pre>${JSON.stringify(data.summary, null, 2)}</pre>

&#x20;                           </div>

&#x20;                       </div>

&#x20;                       <div class="test-card">

&#x20;                           <div class="test-header" onclick="toggleTest(this)">

&#x20;                               💡 Recommendations

&#x20;                           </div>

&#x20;                           <div class="test-body">

&#x20;                               <ul>

&#x20;                                   ${data.recommendations.map(rec => `<li>${rec}</li>`).join('')}

&#x20;                               </ul>

&#x20;                           </div>

&#x20;                       </div>

&#x20;                   `;

&#x20;

&#x20;                   document.getElementById('detailedReport').innerHTML = html;

&#x20;               }

&#x20;

&#x20;               function toggleTest(element) {

&#x20;                   const body = element.nextElementSibling;

&#x20;                   body.classList.toggle('show');

&#x20;               }

&#x20;

&#x20;               function exportJSON() {

&#x20;                   fetch('?action=run')

&#x20;                       .then(res => res.json())

&#x20;                       .then(data => {

&#x20;                           const blob = new Blob(\[JSON.stringify(data, null, 2)], {type: 'application/json'});

&#x20;                           const url = URL.createObjectURL(blob);

&#x20;                           const a = document.createElement('a');

&#x20;                           a.href = url;

&#x20;                           a.download = `performance\\\_test\\\_${Date.now()}.json`;

&#x20;                           a.click();

&#x20;                           URL.revokeObjectURL(url);

&#x20;                       });

&#x20;               }

&#x20;           </script>

&#x20;       </body>

&#x20;       </html>

&#x20;       <?php

&#x20;       break;

&#x20;

&#x20;   default:

&#x20;       header('Content-Type: application/json');

&#x20;       echo json\_encode(\['error' => 'Invalid action']);

}

***/backend/routes Transaksi.php***

<?php



require\\\_once '../controllers/TransactionController.php';



$transaction = new TransactionController();



$action = $\\\_GET\\\['action'] ?? '';



switch($action) {



\&#x20;   case 'add':

\&#x20;       $transaction->addToCart();

\&#x20;       break;



\&#x20;   case 'pay':

\&#x20;       $transaction->pay();

\&#x20;       break;



\&#x20;   case 'cancel':

\&#x20;       $transaction->cancel();

\&#x20;       break;



\&#x20;   default:

\&#x20;       $transaction->index();

}

\*\*\*/backend/tests AuthTest.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . "/../models/User.php";



echo "UNIT TEST LOGIN\\\\n";



$userModel = new User();



/\\\*

|--------------------------------------------------------------------------

| TEST 1

|--------------------------------------------------------------------------

\\\*/



$result1 = $userModel->login("admin", "admin123");



if ($result1) {



\&#x20;   echo "TEST LOGIN VALID = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST LOGIN VALID = GAGAL\\\\n";

}



/\\\*

|--------------------------------------------------------------------------

| TEST 2

|--------------------------------------------------------------------------

\\\*/



$result2 = $userModel->login("salah", "123");



if (!$result2) {



\&#x20;   echo "TEST LOGIN INVALID = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST LOGIN INVALID = GAGAL\\\\n";

}

\*\*\*/backend/tests LoginTests.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . "/../config/Database.php";



class User {



\&#x20;   private $conn;



\&#x20;   public function \\\_\\\_construct() {



\&#x20;       $database = new Database();



\&#x20;       $this->conn = $database->connect();

\&#x20;   }



\&#x20;   public function login($username, $password) {



\&#x20;       /\\\*

\&#x20;       |--------------------------------------------------------------------------

\&#x20;       | Defensive Programming

\&#x20;       |--------------------------------------------------------------------------

\&#x20;       \\\*/



\&#x20;       if (empty($username) || empty($password)) {



\&#x20;           return false;

\&#x20;       }



\&#x20;       $query = "SELECT \\\* FROM users WHERE username = :username";



\&#x20;       $stmt = $this->conn->prepare($query);



\&#x20;       $stmt->bindParam(':username', $username);



\&#x20;       $stmt->execute();



\&#x20;       $user = $stmt->fetch(PDO::FETCH\\\_ASSOC);



\&#x20;       if ($user \\\&\\\& password\\\_verify($password, $user\\\['password'])) {



\&#x20;           return $user;

\&#x20;       }



\&#x20;       return false;

\&#x20;   }

}

\*\*\*/backend/tests ObatTests.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . "/../models/Obat.php";



echo "UNIT TEST OBAT\\\\n";



$obatModel = new Obat();



/\\\*

|--------------------------------------------------------------------------

| TEST GET ALL

|--------------------------------------------------------------------------

\\\*/



$data = $obatModel->getAll();



if (is\\\_array($data)) {



\&#x20;   echo "TEST GET ALL = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST GET ALL = GAGAL\\\\n";

}



/\\\*

|--------------------------------------------------------------------------

| TEST CREATE

|--------------------------------------------------------------------------

\\\*/



$create = $obatModel->create(

\&#x20;   "Paracetamol Test",

\&#x20;   "Tablet",

\&#x20;   10,

\&#x20;   5000,

\&#x20;   ""

);



if ($create) {



\&#x20;   echo "TEST CREATE = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST CREATE = GAGAL\\\\n";

}



/\\\*

|--------------------------------------------------------------------------

| TEST SEARCH

|--------------------------------------------------------------------------

\\\*/



$search = $obatModel->search("Paracetamol");



if (is\\\_array($search)) {



\&#x20;   echo "TEST SEARCH = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST SEARCH = GAGAL\\\\n";

}

\*\*\*/backend/tests PerformanceTest.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../helpers/Performance.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../helpers/PerformanceLogger.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../models/Obat.php';

require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../config/Database.php';



class PerformanceTest {

\&#x20;   

\&#x20;   private $obatModel;

\&#x20;   private $testResults = \\\[];

\&#x20;   private $db;

\&#x20;   

\&#x20;   public function \\\_\\\_construct() {

\&#x20;       $this->obatModel = new Obat();

\&#x20;       $database = new Database();

\&#x20;       $this->db = $database->connect();

\&#x20;   }

\&#x20;   

\&#x20;   // Test response time under 100ms

\&#x20;   public function testResponseTime() {

\&#x20;       echo "\\\\n📈 TEST 1: RESPONSE TIME (< 100ms)\\\\n";

\&#x20;       echo str\\\_repeat("-", 50) . "\\\\n";

\&#x20;       

\&#x20;       $start = Performance::start();

\&#x20;       $data = $this->obatModel->getAll();

\&#x20;       $executionTime = Performance::end($start);

\&#x20;       

\&#x20;       $status = $executionTime < 0.1 ? "✅ PASS" : "❌ FAIL";

\&#x20;       $timeMs = $executionTime \\\* 1000;

\&#x20;       

\&#x20;       echo "GetAll execution time: " . round($timeMs, 2) . " ms\\\\n";

\&#x20;       echo "Status: {$status}\\\\n";

\&#x20;       echo "Threshold: 100ms\\\\n\\\\n";

\&#x20;       

\&#x20;       $this->testResults\\\['response\\\_time'] = \\\[

\&#x20;           'passed' => $executionTime < 0.1,

\&#x20;           'time\\\_ms' => round($timeMs, 2),

\&#x20;           'threshold\\\_ms' => 100

\&#x20;       ];

\&#x20;   }

\&#x20;   

\&#x20;   // Test memory usage under 2MB

\&#x20;   public function testMemoryUsage() {

\&#x20;       echo "\\\\n💾 TEST 2: MEMORY USAGE (< 2MB)\\\\n";

\&#x20;       echo str\\\_repeat("-", 50) . "\\\\n";

\&#x20;       

\&#x20;       $startMemory = memory\\\_get\\\_usage();

\&#x20;       $data = $this->obatModel->getAll();

\&#x20;       $endMemory = memory\\\_get\\\_usage();

\&#x20;       

\&#x20;       $memoryUsed = $endMemory - $startMemory;

\&#x20;       $memoryKb = round($memoryUsed / 1024, 2);

\&#x20;       

\&#x20;       $status = $memoryUsed < 2 \\\* 1024 \\\* 1024 ? "✅ PASS" : "❌ FAIL";

\&#x20;       

\&#x20;       echo "Memory used: {$memoryKb} KB\\\\n";

\&#x20;       echo "Status: {$status}\\\\n";

\&#x20;       echo "Threshold: 2048 KB\\\\n\\\\n";

\&#x20;       

\&#x20;       $this->testResults\\\['memory\\\_usage'] = \\\[

\&#x20;           'passed' => $memoryUsed < 2 \\\* 1024 \\\* 1024,

\&#x20;           'memory\\\_kb' => $memoryKb,

\&#x20;           'threshold\\\_kb' => 2048

\&#x20;       ];

\&#x20;   }

\&#x20;   

\&#x20;   // Test concurrent requests simulation

\&#x20;   public function testConcurrentRequests($requests = 10) {

\&#x20;       echo "\\\\n🔄 TEST 3: CONCURRENT REQUESTS ({$requests} requests)\\\\n";

\&#x20;       echo str\\\_repeat("-", 50) . "\\\\n";

\&#x20;       

\&#x20;       $start = Performance::start();

\&#x20;       

\&#x20;       for ($i = 0; $i < $requests; $i++) {

\&#x20;           $this->obatModel->getAll();

\&#x20;       }

\&#x20;       

\&#x20;       $totalTime = Performance::end($start);

\&#x20;       $avgTime = $totalTime / $requests;

\&#x20;       $avgTimeMs = $avgTime \\\* 1000;

\&#x20;       

\&#x20;       $status = $avgTime < 0.05 ? "✅ EXCELLENT" : ($avgTime < 0.1 ? "⚠️ GOOD" : "❌ SLOW");

\&#x20;       

\&#x20;       echo "Total time: " . round($totalTime \\\* 1000, 2) . " ms\\\\n";

\&#x20;       echo "Average per request: " . round($avgTimeMs, 2) . " ms\\\\n";

\&#x20;       echo "Status: {$status}\\\\n\\\\n";

\&#x20;       

\&#x20;       $this->testResults\\\['concurrent\\\_requests'] = \\\[

\&#x20;           'total\\\_requests' => $requests,

\&#x20;           'total\\\_time\\\_ms' => round($totalTime \\\* 1000, 2),

\&#x20;           'avg\\\_time\\\_ms' => round($avgTimeMs, 2),

\&#x20;           'status' => $status

\&#x20;       ];

\&#x20;   }

\&#x20;   

\&#x20;   // Test database query performance

\&#x20;   public function testQueryPerformance() {

\&#x20;       echo "\\\\n TEST 4: DATABASE QUERY PERFORMANCE\\\\n";

\&#x20;       echo str\\\_repeat("-", 50) . "\\\\n";

\&#x20;       

\&#x20;       $queries = \\\[

\&#x20;           'SELECT \\\* FROM obat' => "SELECT \\\* FROM obat LIMIT 10",

\&#x20;           'SELECT with WHERE' => "SELECT \\\* FROM obat WHERE id = 1",

\&#x20;           'SELECT with LIKE' => "SELECT \\\* FROM obat WHERE nama\\\_obat LIKE '%a%' LIMIT 10"

\&#x20;       ];

\&#x20;       

\&#x20;       foreach ($queries as $name => $query) {

\&#x20;           $start = Performance::start();

\&#x20;           $stmt = $this->db->prepare($query);

\&#x20;           $stmt->execute();

\&#x20;           $results = $stmt->fetchAll();

\&#x20;           $time = Performance::end($start);

\&#x20;           $timeMs = $time \\\* 1000;

\&#x20;           

\&#x20;           $status = $timeMs < 50 ? "✅" : ($timeMs < 100 ? "⚠️" : "❌");

\&#x20;           

\&#x20;           echo "{$status} {$name}: " . round($timeMs, 2) . " ms (rows: " . count($results) . ")\\\\n";

\&#x20;           

\&#x20;           $this->testResults\\\['queries']\\\[$name] = \\\[

\&#x20;               'time\\\_ms' => round($timeMs, 2),

\&#x20;               'rows\\\_returned' => count($results),

\&#x20;               'status' => $status

\&#x20;           ];

\&#x20;       }

\&#x20;       

\&#x20;       echo "\\\\n";

\&#x20;   }

\&#x20;   



\&#x20;   public function testLoadScaling() {

\&#x20;       echo "\\\\n📊 TEST 5: LOAD SCALING\\\\n";

\&#x20;       echo str\\\_repeat("-", 50) . "\\\\n";

\&#x20;       

\&#x20;       $testSizes = \\\[10, 50, 100];

\&#x20;       

\&#x20;       foreach ($testSizes as $size) {

\&#x20;           $start = Performance::start();

\&#x20;           

\&#x20;           // Simulate loading $size records dengan SELECT langsung

\&#x20;           for ($i = 0; $i < $size; $i++) {

\&#x20;               $query = "SELECT \\\* FROM obat WHERE id = 1";

\&#x20;               $stmt = $this->db->prepare($query);

\&#x20;               $stmt->execute();

\&#x20;               $stmt->fetch();

\&#x20;           }

\&#x20;           

\&#x20;           $time = Performance::end($start);

\&#x20;           $timeMs = $time \\\* 1000;

\&#x20;           

\&#x20;           echo "Size {$size}: " . round($timeMs, 2) . " ms (avg " . round($timeMs / $size, 2) . " ms/record)\\\\n";

\&#x20;           

\&#x20;           $this->testResults\\\['load\\\_scaling']\\\[$size] = \\\[

\&#x20;               'total\\\_time\\\_ms' => round($timeMs, 2),

\&#x20;               'avg\\\_per\\\_record\\\_ms' => round($timeMs / $size, 2)

\&#x20;           ];

\&#x20;       }

\&#x20;       

\&#x20;       echo "\\\\n";

\&#x20;   }

\&#x20;   

\&#x20;   // Test API endpoint performance

\&#x20;   public function testApiPerformance() {

\&#x20;       echo "\\\\n🌐 TEST 6: API ENDPOINT PERFORMANCE\\\\n";

\&#x20;       echo str\\\_repeat("-", 50) . "\\\\n";

\&#x20;       

\&#x20;       $endpoints = \\\[

\&#x20;           'GET obat' => '../../backend/routes/obat.php?action=search\\\&keyword=a',

\&#x20;           'GET transaksi' => '../../backend/routes/transaksi.php'

\&#x20;       ];

\&#x20;       

\&#x20;       foreach ($endpoints as $name => $url) {

\&#x20;           $start = Performance::start();

\&#x20;           

\&#x20;           // Simulate API call

\&#x20;           $ch = curl\\\_init("http://localhost{$url}");

\&#x20;           curl\\\_setopt($ch, CURLOPT\\\_RETURNTRANSFER, true);

\&#x20;           curl\\\_setopt($ch, CURLOPT\\\_TIMEOUT, 5);

\&#x20;           $response = curl\\\_exec($ch);

\&#x20;           $httpCode = curl\\\_getinfo($ch, CURLINFO\\\_HTTP\\\_CODE);

\&#x20;           curl\\\_close($ch);

\&#x20;           

\&#x20;           $time = Performance::end($start);

\&#x20;           $timeMs = $time \\\* 1000;

\&#x20;           

\&#x20;           $status = $timeMs < 100 ? "✅" : ($timeMs < 500 ? "⚠️" : "❌");

\&#x20;           

\&#x20;           echo "{$status} {$name}: " . round($timeMs, 2) . " ms (HTTP {$httpCode})\\\\n";

\&#x20;           

\&#x20;           $this->testResults\\\['api\\\_performance']\\\[$name] = \\\[

\&#x20;               'time\\\_ms' => round($timeMs, 2),

\&#x20;               'http\\\_code' => $httpCode,

\&#x20;               'status' => $status

\&#x20;           ];

\&#x20;       }

\&#x20;       

\&#x20;       echo "\\\\n";

\&#x20;   }

\&#x20;   

\&#x20;   // Generate final report

\&#x20;   public function generateReport() {

\&#x20;       echo "\\\\n";

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       echo "║                    FINAL PERFORMANCE TEST REPORT                 ║\\\\n";

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       

\&#x20;       $totalPassed = 0;

\&#x20;       $totalTests = 0;

\&#x20;       

\&#x20;       foreach ($this->testResults as $category => $result) {

\&#x20;           if (isset($result\\\['passed'])) {

\&#x20;               $totalTests++;

\&#x20;               if ($result\\\['passed']) $totalPassed++;

\&#x20;           }

\&#x20;       }

\&#x20;       

\&#x20;       // Check query performance for pass/fail

\&#x20;       if (isset($this->testResults\\\['queries'])) {

\&#x20;           foreach ($this->testResults\\\['queries'] as $query) {

\&#x20;               $totalTests++;

\&#x20;               if ($query\\\['time\\\_ms'] < 100) $totalPassed++;

\&#x20;           }

\&#x20;       }

\&#x20;       

\&#x20;       $passRate = ($totalPassed / max($totalTests, 1)) \\\* 100;

\&#x20;       

\&#x20;       echo sprintf("║ %-66s ║\\\\n", "Overall Pass Rate: " . round($passRate, 1) . "% ({$totalPassed}/{$totalTests})");

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       // Response time result

\&#x20;       if (isset($this->testResults\\\['response\\\_time'])) {

\&#x20;           $icon = $this->testResults\\\['response\\\_time']\\\['passed'] ? "✅" : "❌";

\&#x20;           $timeMs = $this->testResults\\\['response\\\_time']\\\['time\\\_ms'];

\&#x20;           echo sprintf("║ %s Response Time    : %5.2f ms (threshold: 100ms)        ║\\\\n", $icon, $timeMs);

\&#x20;       }

\&#x20;       

\&#x20;       // Memory usage result

\&#x20;       if (isset($this->testResults\\\['memory\\\_usage'])) {

\&#x20;           $icon = $this->testResults\\\['memory\\\_usage']\\\['passed'] ? "✅" : "❌";

\&#x20;           $memoryKb = $this->testResults\\\['memory\\\_usage']\\\['memory\\\_kb'];

\&#x20;           echo sprintf("║ %s Memory Usage     : %5.2f KB (threshold: 2048 KB)      ║\\\\n", $icon, $memoryKb);

\&#x20;       }

\&#x20;       

\&#x20;       // Concurrent requests

\&#x20;       if (isset($this->testResults\\\['concurrent\\\_requests'])) {

\&#x20;           $avgMs = $this->testResults\\\['concurrent\\\_requests']\\\['avg\\\_time\\\_ms'];

\&#x20;           $statusIcon = $this->testResults\\\['concurrent\\\_requests']\\\['status'] === "✅ EXCELLENT" ? "✅" : "⚠️";

\&#x20;           echo sprintf("║ %s Concurrent (10)   : %5.2f ms avg per request           ║\\\\n", $statusIcon, $avgMs);

\&#x20;       }

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       

\&#x20;       // Query results summary

\&#x20;       if (isset($this->testResults\\\['queries'])) {

\&#x20;           echo "║ 📊 Query Performance:                                              ║\\\\n";

\&#x20;           foreach ($this->testResults\\\['queries'] as $name => $query) {

\&#x20;               $icon = $query\\\['status'] === "✅" ? "✅" : ($query\\\['status'] === "⚠️" ? "⚠️" : "❌");

\&#x20;               $shortName = strlen($name) > 35 ? substr($name, 0, 32) . "..." : $name;

\&#x20;               echo sprintf("║    %s %-35s : %5.2f ms                         ║\\\\n", $icon, $shortName, $query\\\['time\\\_ms']);

\&#x20;           }

\&#x20;       }

\&#x20;       

\&#x20;       echo "+==================================================================+\\\\n";

\&#x20;       

\&#x20;       // Save to file

\&#x20;       $this->saveReport();

\&#x20;   }

\&#x20;   

\&#x20;   private function saveReport() {

\&#x20;       $report = \\\[

\&#x20;           'timestamp' => date('Y-m-d H:i:s'),

\&#x20;           'results' => $this->testResults,

\&#x20;           'server\\\_info' => \\\[

\&#x20;               'php\\\_version' => phpversion(),

\&#x20;               'memory\\\_limit' => ini\\\_get('memory\\\_limit'),

\&#x20;               'max\\\_execution\\\_time' => ini\\\_get('max\\\_execution\\\_time'),

\&#x20;               'database' => 'MySQL'

\&#x20;           ]

\&#x20;       ];

\&#x20;       

\&#x20;       $dir = \\\_\\\_DIR\\\_\\\_ . '/../logs';

\&#x20;       if (!file\\\_exists($dir)) {

\&#x20;           mkdir($dir, 0777, true);

\&#x20;       }

\&#x20;       

\&#x20;       $filename = $dir . '/performance\\\_report\\\_' . date('Ymd\\\_His') . '.json';

\&#x20;       file\\\_put\\\_contents($filename, json\\\_encode($report, JSON\\\_PRETTY\\\_PRINT));

\&#x20;       echo "\\\\n📁 Report saved to: logs/" . basename($filename) . "\\\\n";

\&#x20;   }

\&#x20;   

\&#x20;   // Run all tests

\&#x20;   public function runAll() {

\&#x20;       echo "\\\\n";

\&#x20;       echo "====================================================================\\\\n";

\&#x20;       echo "║              PERFORMANCE TESTING SUITE v2.0                      ║\\\\n";

\&#x20;       echo "====================================================================\\\\n";

\&#x20;       

\&#x20;       $this->testResponseTime();

\&#x20;       $this->testMemoryUsage();

\&#x20;       $this->testConcurrentRequests(10);

\&#x20;       $this->testQueryPerformance();

\&#x20;       $this->testLoadScaling();

\&#x20;       $this->testApiPerformance();

\&#x20;       $this->generateReport();

\&#x20;       

\&#x20;       return $this->testResults;

\&#x20;   }

}



// Run the tests if executed directly

if (php\\\_sapi\\\_name() === 'cli' || basename($\\\_SERVER\\\['PHP\\\_SELF']) === 'PerformanceTest.php') {

\&#x20;   $test = new PerformanceTest();

\&#x20;   $test->runAll();

}

\*\*\*/backend/tests TransaksiTests.php\*\*\*
<?php



require\\\_once \\\_\\\_DIR\\\_\\\_ . "/../models/Transaction.php";



echo "UNIT TEST TRANSACTION\\\\n";



$transaction = new Transaction();



/\\\*

|--------------------------------------------------------------------------

| TEST ADD CART

|--------------------------------------------------------------------------

\\\*/



$cart = \\\[];



$item = \\\[



\&#x20;   "id"       => 1,

\&#x20;   "nama"     => "Paracetamol",

\&#x20;   "harga"    => 5000,

\&#x20;   "jumlah"   => 2,

\&#x20;   "subtotal" => 10000

];



$cart\\\[] = $item;



if (count($cart) > 0) {



\&#x20;   echo "TEST ADD CART = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST ADD CART = GAGAL\\\\n";

}



/\\\*

|--------------------------------------------------------------------------

| TEST TOTAL

|--------------------------------------------------------------------------

\\\*/



$total = 0;



foreach ($cart as $c) {



\&#x20;   $total += $c\\\['subtotal'];

}



if ($total === 10000) {



\&#x20;   echo "TEST TOTAL = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST TOTAL = GAGAL\\\\n";

}



/\\\*

|--------------------------------------------------------------------------

| TEST CHECKOUT

|--------------------------------------------------------------------------

\\\*/



$result = $transaction->checkout($cart);



if ($result) {



\&#x20;   echo "TEST CHECKOUT = BERHASIL\\\\n";



} else {



\&#x20;   echo "TEST CHECKOUT = GAGAL\\\\n";

}


\*\*\*/frontend/css style.css

/frontend/js login.js\*\*\*
function handleLogin(event) {

\&#x20;   event.preventDefault();



\&#x20;   const username = document.getElementById("username").value;

\&#x20;   const password = document.getElementById("password").value;



\&#x20;   fetch("../../backend/routes/auth.php?action=login", {

\&#x20;       method: "POST",

\&#x20;       headers: {

\&#x20;           "Content-Type": "application/x-www-form-urlencoded"

\&#x20;       },

\&#x20;       body: `username=${encodeURIComponent(username)}\\\&password=${encodeURIComponent(password)}`

\&#x20;   })

\&#x20;   .then(res => res.json())

\&#x20;   .then(data => {



\&#x20;       if (data.success) {

\&#x20;           window.location.href = "../pages/dashboard.php";

\&#x20;       } else {

\&#x20;           document.getElementById("message").innerText = data.message;

\&#x20;       }



\&#x20;   })

\&#x20;   .catch(err => {

\&#x20;       console.log(err);

\&#x20;       document.getElementById("message").innerText = "Server error";

\&#x20;   });

}


\*\*\*/frontend/js modal.js\*\*\*
function openModal() {

\&#x20;   document.getElementById("modal").classList.remove("hidden");

}



function closeModal() {

\&#x20;   document.getElementById("modal").classList.add("hidden");

}



\*\*\*/frontend/js search.js\*\*\*
const SEARCH\\\_STATE = {



\&#x20;   IDLE: "IDLE",

\&#x20;   LOADING: "LOADING",

\&#x20;   SUCCESS: "SUCCESS",

\&#x20;   EMPTY: "EMPTY",

\&#x20;   ERROR: "ERROR"

};



let currentSearchState =

\&#x20;   SEARCH\\\_STATE.IDLE;



/\\\*

|--------------------------------------------------------------------------

| SEARCH OBAT

|--------------------------------------------------------------------------

\\\*/



async function searchObat() {



\&#x20;   const keyword =

\&#x20;       document.getElementById("searchInput")

\&#x20;       .value

\&#x20;       .trim();



\&#x20;   if (!keyword) {



\&#x20;       setSearchState(

\&#x20;           SEARCH\\\_STATE.EMPTY

\&#x20;       );



\&#x20;       renderEmpty();



\&#x20;       return;

\&#x20;   }



\&#x20;   setSearchState(

\&#x20;       SEARCH\\\_STATE.LOADING

\&#x20;   );



\&#x20;   try {



\&#x20;       const response = await fetch(

\&#x20;           `../../backend/routes/obat.php?action=search\\\&keyword=${encodeURIComponent(keyword)}`

\&#x20;       );



\&#x20;       const data =

\&#x20;           await response.json();



\&#x20;       if (data.length === 0) {



\&#x20;           setSearchState(

\&#x20;               SEARCH\\\_STATE.EMPTY

\&#x20;           );



\&#x20;           renderEmpty();



\&#x20;           return;

\&#x20;       }



\&#x20;       setSearchState(

\&#x20;           SEARCH\\\_STATE.SUCCESS

\&#x20;       );



\&#x20;       renderData(data);



\&#x20;   } catch (error) {



\&#x20;       setSearchState(

\&#x20;           SEARCH\\\_STATE.ERROR

\&#x20;       );



\&#x20;       renderError();

\&#x20;   }

}



/\\\*

|--------------------------------------------------------------------------

| FSM STATE

|--------------------------------------------------------------------------

\\\*/



function setSearchState(state) {



\&#x20;   currentSearchState = state;



\&#x20;   const status =

\&#x20;       document.getElementById("status");



\&#x20;   status.innerText =

\&#x20;       `STATE : ${state}`;

}



/\\\*

|--------------------------------------------------------------------------

| RENDER DATA

|--------------------------------------------------------------------------

\\\*/



function renderData(data) {



\&#x20;   const result =

\&#x20;       document.getElementById("result");



\&#x20;   result.innerHTML = "";



\&#x20;   data.forEach(obat => {



\&#x20;       result.innerHTML += `



\&#x20;           <div class="bg-white rounded-2xl p-5 border border-slate-200">



\&#x20;               <h3 class="font-bold text-slate-800 text-lg">

\&#x20;                   ${obat.nama\\\_obat}

\&#x20;               </h3>



\&#x20;               <p class="text-slate-500 text-sm mt-1">

\&#x20;                   ${obat.kategori}

\&#x20;               </p>



\&#x20;               <div class="mt-3 text-emerald-600 font-bold">

\&#x20;                   Stok : ${obat.stok}

\&#x20;               </div>



\&#x20;           </div>

\&#x20;       `;

\&#x20;   });

}



/\\\*

|--------------------------------------------------------------------------

| EMPTY

|--------------------------------------------------------------------------

\\\*/



function renderEmpty() {



\&#x20;   document.getElementById("result")

\&#x20;       .innerHTML =

\&#x20;       `

\&#x20;       <div class="text-slate-400 text-center py-10">

\&#x20;           Data tidak ditemukan

\&#x20;       </div>

\&#x20;       `;

}



/\\\*

|--------------------------------------------------------------------------

| ERROR

|--------------------------------------------------------------------------

\\\*/



function renderError() {



\&#x20;   document.getElementById("result")

\&#x20;       .innerHTML =

\&#x20;       `

\&#x20;       <div class="text-red-500 text-center py-10">

\&#x20;           Terjadi kesalahan sistem

\&#x20;       </div>

\&#x20;       `;

}


\*\*\*/frontend/js transaction.js\*\*\*
const TRANSACTION\\\_STATE = {



\&#x20;   DRAFT: "DRAFT",

\&#x20;   PENDING: "PENDING",

\&#x20;   COMPLETED: "COMPLETED",

\&#x20;   CANCELLED: "CANCELLED"

};



let transactionState =

\&#x20;   TRANSACTION\\\_STATE.DRAFT;



/\\\*

|--------------------------------------------------------------------------

| ADD TO CART

|--------------------------------------------------------------------------

\\\*/



function addToCart() {



\&#x20;   if (

\&#x20;       transactionState ===

\&#x20;       TRANSACTION\\\_STATE.COMPLETED

\&#x20;   ) {



\&#x20;       alert("Transaksi sudah selesai");



\&#x20;       return;

\&#x20;   }



\&#x20;   transactionState =

\&#x20;       TRANSACTION\\\_STATE.PENDING;



\&#x20;   renderTransactionState();

}



/\\\*

|--------------------------------------------------------------------------

| CHECKOUT

|--------------------------------------------------------------------------

\\\*/



async function pay() {



\&#x20;   if (

\&#x20;       transactionState !==

\&#x20;       TRANSACTION\\\_STATE.PENDING

\&#x20;   ) {



\&#x20;       alert(

\&#x20;           "Pembayaran tidak valid"

\&#x20;       );



\&#x20;       return;

\&#x20;   }



\&#x20;   try {



\&#x20;       const response = await fetch(

\&#x20;           "../../backend/routes/transaksi.php?action=bayar",

\&#x20;           {

\&#x20;               method: "POST"

\&#x20;           }

\&#x20;       );



\&#x20;       const data =

\&#x20;           await response.json();



\&#x20;       if (!data.success) {



\&#x20;           throw new Error(

\&#x20;               data.message

\&#x20;           );

\&#x20;       }



\&#x20;       transactionState =

\&#x20;           TRANSACTION\\\_STATE.COMPLETED;



\&#x20;       renderTransactionState();



\&#x20;       alert(data.message);



\&#x20;       window.location.reload();



\&#x20;   } catch (error) {



\&#x20;       alert(error.message);

\&#x20;   }

}



/\\\*

|--------------------------------------------------------------------------

| CANCEL

|--------------------------------------------------------------------------

\\\*/



async function cancelTransaction() {



\&#x20;   await fetch(

\&#x20;       "../../backend/routes/transaksi.php?action=batal"

\&#x20;   );



\&#x20;   transactionState =

\&#x20;       TRANSACTION\\\_STATE.CANCELLED;



\&#x20;   renderTransactionState();



\&#x20;   window.location.reload();

}



/\\\*

|--------------------------------------------------------------------------

| RENDER

|--------------------------------------------------------------------------

\\\*/



function renderTransactionState() {



\&#x20;   const stateBox =

\&#x20;       document.getElementById("trxState");



\&#x20;   if (!stateBox) return;



\&#x20;   stateBox.innerText =

\&#x20;       transactionState;

}



\*\*\*/frontend/pages dashboard.php\*\*\*
<?php

if (session\\\_status() === PHP\\\_SESSION\\\_NONE) {

\&#x20;   session\\\_start();

}



if (!isset($\\\_SESSION\\\['login'])) {

\&#x20;   header("Location: login.php");

\&#x20;   exit;

}



require\\\_once \\\_\\\_DIR\\\_\\\_ . '/../../backend/models/Obat.php';



$obatModel = new Obat();



// Handle search

$keyword = $\\\_GET\\\['keyword'] ?? '';

if (!empty($keyword)) {

\&#x20;   $data = $obatModel->search($keyword);

} else {

\&#x20;   $data = $obatModel->getAll();

}



// ⬇️ TAMBAHKAN INI UNTUK PASTIKAN $data SELALU ADA ⬇️

if (!isset($data) || $data === false) {

\&#x20;   $data = \\\[];

}

// ⬆️ TAMBAHKAN INI ⬆️






<!DOCTYPE html>

<html lang="id">

<head>

&#x20;   <meta charset="UTF-8">

&#x20;   <meta name="viewport" content="width=device-width, initial-scale=1.0">

&#x20;   <title>Dashboard</title>

&#x20;   <script src="https://cdn.tailwindcss.com"></script>

&#x20;   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700\\\&display=swap" rel="stylesheet">

</head>

<body class="bg-slate-50 text-slate-800">



<!-- NAVBAR -->

<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm">

&#x20;   <div class="max-w-6xl mx-auto flex justify-between items-center">

&#x20;       <div class="flex items-center gap-3">

&#x20;           <div class="bg-emerald-600 p-1.5 rounded-lg">

&#x20;               <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

&#x20;                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

&#x20;                       d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />

&#x20;               </svg>

&#x20;           </div>

&#x20;           <span class="text-emerald-600 font-black text-xl uppercase tracking-tighter">

&#x20;               Apotek<span class="text-slate-800">Sehat</span>

&#x20;           </span>

&#x20;       </div>

&#x20;       <div class="flex items-center gap-6">

&#x20;           <a href="dashboard.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">

&#x20;               Dashboard

&#x20;           </a>

&#x20;           <a href="transaksi.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">

&#x20;               Transaksi

&#x20;           </a>

&#x20;           <a href="../../backend/routes/logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">

&#x20;               Logout

&#x20;           </a>

&#x20;       </div>

&#x20;   </div>

</nav>



<!-- MAIN -->

<main class="max-w-6xl mx-auto px-6 mt-10">

&#x20;   <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 mb-10">

&#x20;       <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">

&#x20;           <div>

&#x20;               <h2 class="text-2xl font-bold text-slate-800">Inventaris Obat</h2>

&#x20;               <p class="text-slate-500">Cari dan kelola ketersediaan stok obat.</p>

&#x20;           </div>

&#x20;           <div class="mt-4 md:mt-0 px-4 py-1 bg-slate-100 text-slate-500 rounded-full text-\\\[10px] font-black uppercase tracking-\\\[0.2em]">

&#x20;               <?= isset($data) ? count($data) : 0; ?> DATA

&#x20;           </div>

&#x20;       </div>



&#x20;       <!-- SEARCH FORM -->

&#x20;       <form method="GET" action="">

&#x20;           <div class="flex flex-col md:flex-row gap-4">

&#x20;               <input type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? '') ?>"

&#x20;                   class="flex-1 px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none"

&#x20;                   placeholder="Masukkan nama obat...">

&#x20;               <button type="submit" class="bg-emerald-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-emerald-700 transition-all">

&#x20;                   Cari Obat

&#x20;               </button>

&#x20;               <button type="button" onclick="openModal()" class="bg-indigo-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all">

&#x20;                   + Tambah

&#x20;               </button>

&#x20;           </div>

&#x20;       </form>

&#x20;   </div>



&#x20;   <!-- CARD GRID -->

&#x20;   <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

&#x20;       <?php if (isset($data) \\\&\\\& count($data) > 0): ?>

&#x20;           <?php foreach ($data as $obat): ?>

&#x20;               <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all">

&#x20;                   <?php if (!empty($obat\\\['gambar'])): ?>

&#x20;                       <img src="../uploads/<?= $obat\\\['gambar']; ?>" class="w-full h-48 object-cover rounded-2xl mb-5">

&#x20;                   <?php endif; ?>

&#x20;                   <div class="flex justify-between items-start mb-5">

&#x20;                       <div>

&#x20;                           <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($obat\\\['nama\\\_obat']); ?></h3>

&#x20;                           <p class="text-slate-400 text-sm mt-1"><?= htmlspecialchars($obat\\\['kategori']); ?></p>

&#x20;                       </div>

&#x20;                       <div class="bg-emerald-100 text-emerald-700 px-4 py-1 rounded-full text-xs font-black uppercase">

&#x20;                           Stok <?= $obat\\\['stok']; ?>

&#x20;                       </div>

&#x20;                   </div>

&#x20;                   <div class="mb-6">

&#x20;                       <p class="text-slate-400 text-sm mb-1">Harga</p>

&#x20;                       <h2 class="text-3xl font-black text-emerald-600">Rp <?= number\\\_format($obat\\\['harga']); ?></h2>

&#x20;                   </div>

&#x20;                   <div class="flex gap-3">

&#x20;                       <button onclick="openEditModal('<?= $obat\\\['id']; ?>', '<?= addslashes($obat\\\['nama\\\_obat']); ?>', '<?= addslashes($obat\\\['kategori']); ?>', '<?= $obat\\\['stok']; ?>', '<?= $obat\\\['harga']; ?>')"

&#x20;                           class="flex-1 text-center bg-indigo-50 text-indigo-600 py-3 rounded-2xl font-bold hover:bg-indigo-100 transition-all">

&#x20;                           Edit

&#x20;                       </button>

&#x20;                       <a href="../../backend/routes/obat.php?action=delete\&id=<?= $obat\\\['id']; ?>" onclick="return confirm('Yakin hapus obat?')"

&#x20;                           class="flex-1 text-center bg-red-50 text-red-600 py-3 rounded-2xl font-bold hover:bg-red-100 transition-all">

&#x20;                           Hapus

&#x20;                       </a>

&#x20;                   </div>

&#x20;               </div>

&#x20;           <?php endforeach; ?>

&#x20;       <?php else: ?>

&#x20;           <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-200">

&#x20;               <p class="text-slate-400 text-lg">Tidak ada data obat</p>

&#x20;           </div>

&#x20;       <?php endif; ?>

&#x20;   </div>

</main>



<!-- MODAL TAMBAH -->

<div id="modal" class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">

&#x20;   <div class="bg-white w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl">

&#x20;       <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">

&#x20;           <div>

&#x20;               <h2 class="text-2xl font-black">Tambah Obat</h2>

&#x20;               <p class="text-emerald-100 text-sm">Tambahkan data obat baru</p>

&#x20;           </div>

&#x20;           <button onclick="closeModal()" class="text-3xl">×</button>

&#x20;       </div>

&#x20;       <form method="POST" action="../../backend/routes/obat.php?action=create" enctype="multipart/form-data" class="p-6 space-y-5">

&#x20;           <div>

&#x20;               <label class="block text-sm font-bold mb-2">Gambar Obat</label>

&#x20;               <input type="file" name="gambar" accept="image/\\\*" class="w-full">

&#x20;           </div>

&#x20;           <div>

&#x20;               <label class="block text-sm font-bold mb-2">Nama Obat</label>

&#x20;               <input type="text" name="nama\\\_obat" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;           </div>

&#x20;           <div>

&#x20;               <label class="block text-sm font-bold mb-2">Kategori</label>

&#x20;               <input type="text" name="kategori" class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;           </div>

&#x20;           <div class="grid grid-cols-2 gap-4">

&#x20;               <div>

&#x20;                   <label class="block text-sm font-bold mb-2">Stok</label>

&#x20;                   <input type="number" name="stok" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;               </div>

&#x20;               <div>

&#x20;                   <label class="block text-sm font-bold mb-2">Harga</label>

&#x20;                   <input type="number" name="harga" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;               </div>

&#x20;           </div>

&#x20;           <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold">Simpan Obat</button>

&#x20;       </form>

&#x20;   </div>

</div>



<!-- MODAL EDIT -->

<div id="editModal" class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">

&#x20;   <div class="bg-white w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl">

&#x20;       <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">

&#x20;           <div>

&#x20;               <h2 class="text-2xl font-black">Edit Obat</h2>

&#x20;               <p class="text-emerald-100 text-sm">Ubah data obat</p>

&#x20;           </div>

&#x20;           <button onclick="closeEditModal()" class="text-3xl">×</button>

&#x20;       </div>

&#x20;       <form method="POST" action="../../backend/routes/obat.php?action=edit" enctype="multipart/form-data" class="p-6 space-y-5">

&#x20;           <input type="hidden" name="id" id="edit\\\_id">

&#x20;           <div>

&#x20;               <label class="block text-sm font-bold mb-2">Gambar Baru</label>

&#x20;               <input type="file" name="gambar" accept="image/\\\*" class="w-full">

&#x20;           </div>

&#x20;           <div>

&#x20;               <label class="block text-sm font-bold mb-2">Nama Obat</label>

&#x20;               <input type="text" name="nama\\\_obat" id="edit\\\_nama" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;           </div>

&#x20;           <div>

&#x20;               <label class="block text-sm font-bold mb-2">Kategori</label>

&#x20;               <input type="text" name="kategori" id="edit\\\_kategori" class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;           </div>

&#x20;           <div class="grid grid-cols-2 gap-4">

&#x20;               <div>

&#x20;                   <label class="block text-sm font-bold mb-2">Stok</label>

&#x20;                   <input type="number" name="stok" id="edit\\\_stok" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;               </div>

&#x20;               <div>

&#x20;                   <label class="block text-sm font-bold mb-2">Harga</label>

&#x20;                   <input type="number" name="harga" id="edit\\\_harga" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">

&#x20;               </div>

&#x20;           </div>

&#x20;           <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold">Update Obat</button>

&#x20;       </form>

&#x20;   </div>

</div>



<script>

function openModal() {

\&#x20;   document.getElementById('modal').classList.remove('hidden');

}

function closeModal() {

\&#x20;   document.getElementById('modal').classList.add('hidden');

}

function openEditModal(id, nama, kategori, stok, harga) {

\&#x20;   document.getElementById('edit\\\_id').value = id;

\&#x20;   document.getElementById('edit\\\_nama').value = nama;

\&#x20;   document.getElementById('edit\\\_kategori').value = kategori;

\&#x20;   document.getElementById('edit\\\_stok').value = stok;

\&#x20;   document.getElementById('edit\\\_harga').value = harga;

\&#x20;   document.getElementById('editModal').classList.remove('hidden');

}

function closeEditModal() {

\&#x20;   document.getElementById('editModal').classList.add('hidden');

}


</body>

</html>



***/frontend/pages login.php***

<?php



if (session\\\_status() === PHP\\\_SESSION\\\_NONE) {

\&#x20;   session\\\_start();

}



if (isset($\\\_SESSION\\\['login'])) {



\&#x20;   header("Location: dashboard.php");

\&#x20;   exit;

}




<!DOCTYPE html>

<html lang="id">

<head>

&#x20;   <meta charset="UTF-8">

&#x20;   <meta name="viewport" content="width=device-width, initial-scale=1.0">



&#x20;   <title>Login Apotek</title>



&#x20;   <script src="https://cdn.tailwindcss.com"></script>



&#x20;   <link rel="stylesheet" href="../css/style.css">



&#x20;   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700\\\&display=swap" rel="stylesheet">

</head>



<body class="bg-slate-50 text-slate-800">



<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-emerald-500 to-teal-700">



&#x20;   <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">



&#x20;       <div class="text-center mb-8">



&#x20;           <div class="bg-emerald-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 rotate-3">



&#x20;               <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">



&#x20;                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

&#x20;                       d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />



&#x20;               </svg>



&#x20;           </div>



&#x20;           <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">

&#x20;               Login

&#x20;           </h1>



&#x20;           <p class="text-slate-500 mt-2">

&#x20;               Apotek Sehat - Management System

&#x20;           </p>



&#x20;       </div>



&#x20;       <form onsubmit="handleLogin(event)">



&#x20;   <div class="space-y-5">



&#x20;       <div>

&#x20;           <label class="block text-sm font-semibold text-slate-700 mb-1.5">

&#x20;               Username

&#x20;           </label>



&#x20;           <input

&#x20;               type="text"

&#x20;               id="username"

&#x20;               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl"

&#x20;               placeholder="Masukkan username"

&#x20;               required

&#x20;           >

&#x20;       </div>



&#x20;       <div>

&#x20;           <label class="block text-sm font-semibold text-slate-700 mb-1.5">

&#x20;               Password

&#x20;           </label>



&#x20;           <input

&#x20;               type="password"

&#x20;               id="password"

&#x20;               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl"

&#x20;               placeholder="Masukkan password"

&#x20;               required

&#x20;           >

&#x20;       </div>



&#x20;       <button

&#x20;           type="submit"

&#x20;           id="loginBtn"

&#x20;           class="w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl"

&#x20;       >

&#x20;           Masuk ke Sistem

&#x20;       </button>



&#x20;       <p

&#x20;           id="message"

&#x20;           class="text-center text-sm font-medium text-red-500"

&#x20;       ></p>



&#x20;   </div>



</form>



&#x20;   </div>



</div>

<script src="../js/login.js"></script>

</body>

</html>

***/frontend/pages transaksi.php***

<?php

if (session\\\_status() === PHP\\\_SESSION\\\_NONE) {

\&#x20;   session\\\_start();

}



if (!isset($\\\_SESSION\\\['login'])) {

\&#x20;   header("Location: login.php");

\&#x20;   exit;

}



// Koneksi database untuk ambil data obat

$conn = mysqli\\\_connect("localhost", "root", "", "apotek\\\_db");



// Proses TAMBAH ke keranjang

if (isset($\\\_POST\\\['tambah'])) {

\&#x20;   $id\\\_obat = $\\\_POST\\\['id\\\_obat'];

\&#x20;   $jumlah = (int)$\\\_POST\\\['jumlah'];

\&#x20;   

\&#x20;   // Ambil data obat

\&#x20;   $result = mysqli\\\_query($conn, "SELECT \\\* FROM obat WHERE id = $id\\\_obat");

\&#x20;   $obat = mysqli\\\_fetch\\\_assoc($result);

\&#x20;   

\&#x20;   if ($obat \\\&\\\& $jumlah <= $obat\\\['stok']) {

\&#x20;       $item = \\\[

\&#x20;           'id' => $obat\\\['id'],

\&#x20;           'nama' => $obat\\\['nama\\\_obat'],

\&#x20;           'harga' => $obat\\\['harga'],

\&#x20;           'jumlah' => $jumlah,

\&#x20;           'subtotal' => $obat\\\['harga'] \\\* $jumlah

\&#x20;       ];

\&#x20;       

\&#x20;       if (!isset($\\\_SESSION\\\['cart'])) {

\&#x20;           $\\\_SESSION\\\['cart'] = \\\[];

\&#x20;       }

\&#x20;       $\\\_SESSION\\\['cart']\\\[] = $item;

\&#x20;   }

}



// Proses BAYAR

if (isset($\\\_POST\\\['bayar']) \\\&\\\& !empty($\\\_SESSION\\\['cart'])) {

\&#x20;   foreach ($\\\_SESSION\\\['cart'] as $item) {

\&#x20;       // Kurangi stok

\&#x20;       mysqli\\\_query($conn, "UPDATE obat SET stok = stok - {$item\\\['jumlah']} WHERE id = {$item\\\['id']}");

\&#x20;   }

\&#x20;   unset($\\\_SESSION\\\['cart']);

\&#x20;   echo "<script>alert('Pembayaran berhasil!'); window.location.href='transaksi.php';</script>";

\&#x20;   exit;

}



// Proses BATAL

if (isset($\\\_GET\\\['batal'])) {

\&#x20;   unset($\\\_SESSION\\\['cart']);

\&#x20;   echo "<script>alert('Transaksi dibatalkan'); window.location.href='transaksi.php';</script>";

\&#x20;   exit;

}



// Ambil data obat untuk dropdown

$query = mysqli\\\_query($conn, "SELECT \\\* FROM obat ORDER BY nama\\\_obat ASC");



// Hitung total

$total = 0;

if (!empty($\\\_SESSION\\\['cart'])) {

\&#x20;   foreach ($\\\_SESSION\\\['cart'] as $item) {

\&#x20;       $total += $item\\\['subtotal'];

\&#x20;   }

}




<!DOCTYPE html>

<html lang="id">

<head>

&#x20;   <meta charset="UTF-8">

&#x20;   <meta name="viewport" content="width=device-width, initial-scale=1.0">

&#x20;   <title>Transaksi</title>

&#x20;   <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-slate-50 text-slate-800">



<!-- NAVBAR -->

<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm">

&#x20;   <div class="max-w-6xl mx-auto flex justify-between items-center">

&#x20;       <div class="flex items-center gap-3">

&#x20;           <div class="bg-emerald-600 p-1.5 rounded-lg">

&#x20;               <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

&#x20;                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

&#x20;                       d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />

&#x20;               </svg>

&#x20;           </div>

&#x20;           <span class="text-emerald-600 font-black text-xl uppercase tracking-tighter">

&#x20;               Apotek<span class="text-slate-800">Sehat</span>

&#x20;           </span>

&#x20;       </div>

&#x20;       <div class="flex items-center gap-6">

&#x20;           <a href="dashboard.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">

&#x20;               Dashboard

&#x20;           </a>

&#x20;           <a href="transaksi.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">

&#x20;               Transaksi

&#x20;           </a>

&#x20;           <a href="../../backend/routes/logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">

&#x20;               Logout

&#x20;           </a>

&#x20;       </div>

&#x20;   </div>

</nav>



<!-- MAIN CONTENT -->

<main class="max-w-5xl mx-auto px-6 mt-10">

&#x20;   <div class="bg-white p-10 rounded-3xl shadow-xl border border-slate-200">

&#x20;

&#x20;       <!-- HEADER -->

&#x20;       <div class="flex justify-between items-center mb-10 pb-6 border-b border-slate-100">

&#x20;           <div>

&#x20;               <h1 class="text-3xl font-black text-slate-800">E-Kasir</h1>

&#x20;               <p class="text-slate-400 mt-1">Kelola transaksi pelanggan dengan efisien.</p>

&#x20;           </div>

&#x20;           <div class="text-right">

&#x20;               <span class="text-\\\[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">

&#x20;                   Status Transaksi

&#x20;               </span>

&#x20;               <div class="px-6 py-2 rounded-2xl text-sm font-black uppercase tracking-wider <?= empty($\\\_SESSION\\\['cart']) ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700'; ?>">

&#x20;                   <?= empty($\\\_SESSION\\\['cart']) ? 'DRAFT' : 'ACTIVE'; ?>

&#x20;               </div>

&#x20;           </div>

&#x20;       </div>



&#x20;       <!-- FORM TAMBAH -->

&#x20;       <form method="POST" class="grid md:grid-cols-3 gap-4 mb-8">

&#x20;           <select name="id\\\_obat" required class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">

&#x20;               <option value="">Pilih Obat</option>

&#x20;               <?php while ($obat = mysqli\\\_fetch\\\_assoc($query)): ?>

&#x20;                   <option value="<?= $obat\\\['id']; ?>">

&#x20;                       <?= $obat\\\['nama\\\_obat']; ?> - Stok <?= $obat\\\['stok']; ?> - Rp <?= number\\\_format($obat\\\['harga']); ?>

&#x20;                   </option>

&#x20;               <?php endwhile; ?>

&#x20;           </select>

&#x20;           <input type="number" name="jumlah" min="1" placeholder="Jumlah" required class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">

&#x20;           <button type="submit" name="tambah" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black transition-all">

&#x20;               + Tambah

&#x20;           </button>

&#x20;       </form>



&#x20;       <!-- LIST TRANSAKSI -->

&#x20;       <div class="space-y-5 mb-10">

&#x20;           <?php if (!empty($\\\_SESSION\\\['cart'])): ?>

&#x20;               <?php foreach ($\\\_SESSION\\\['cart'] as $index => $item): ?>

&#x20;                   <div class="flex justify-between items-center p-4 rounded-2xl bg-slate-50 border border-slate-100">

&#x20;                       <div class="flex gap-4 items-center">

&#x20;                           <div class="bg-white p-3 rounded-xl shadow-sm">💊</div>

&#x20;                           <div>

&#x20;                               <h3 class="font-bold text-slate-800"><?= $item\\\['nama']; ?></h3>

&#x20;                               <p class="text-sm text-slate-500"><?= $item\\\['jumlah']; ?> x Rp <?= number\\\_format($item\\\['harga']); ?></p>

&#x20;                           </div>

&#x20;                       </div>

&#x20;                       <span class="font-bold text-slate-700">Rp <?= number\\\_format($item\\\['subtotal']); ?></span>

&#x20;                   </div>

&#x20;               <?php endforeach; ?>

&#x20;           <?php else: ?>

&#x20;               <div class="text-center py-16 bg-slate-50 rounded-3xl">

&#x20;                   <p class="text-slate-400 font-medium">Belum ada transaksi.</p>

&#x20;               </div>

&#x20;           <?php endif; ?>

&#x20;       </div>



&#x20;       <!-- TOTAL -->

&#x20;       <div class="bg-slate-50 rounded-3xl p-6 mb-8 border border-slate-200">

&#x20;           <div class="flex justify-between items-center">

&#x20;               <span class="text-slate-500 font-medium">Total Pembayaran</span>

&#x20;               <h2 class="text-4xl font-black text-emerald-600">Rp <?= number\\\_format($total); ?></h2>

&#x20;           </div>

&#x20;       </div>



&#x20;       <!-- BUTTONS -->

&#x20;       <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

&#x20;           <?php if (empty($\\\_SESSION\\\['cart'])): ?>

&#x20;               <button type="button" onclick="showWarning()" class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all bg-slate-200 text-slate-400 cursor-not-allowed">

&#x20;                   Bayar Lunas

&#x20;               </button>

&#x20;           <?php else: ?>

&#x20;               <form method="POST">

&#x20;                   <button type="submit" name="bayar" class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-100">

&#x20;                       Bayar Lunas

&#x20;                   </button>

&#x20;               </form>

&#x20;           <?php endif; ?>

&#x20;

&#x20;           <a href="<?= empty($\\\_SESSION\\\['cart']) ? '#' : 'transaksi.php?batal=1'; ?>"

&#x20;              <?= empty($\\\_SESSION\\\['cart']) ? 'onclick="showWarning()"' : ''; ?>

&#x20;              class="w-full flex items-center justify-center py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all <?= empty($\\\_SESSION\\\['cart']) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-red-50 text-red-600 hover:bg-red-100'; ?>">

&#x20;               Batalkan

&#x20;           </a>

&#x20;       </div>



&#x20;       <!-- WARNING BOX -->

&#x20;       <div id="warningBox" class="hidden mt-5 bg-amber-50 border border-amber-200 text-amber-700 px-5 py-4 rounded-2xl text-sm font-medium">

&#x20;           ⚠️ Belum ada transaksi yang bisa diproses.

&#x20;       </div>

&#x20;   </div>

</main>



<script>

function showWarning() {

\&#x20;   const box = document.getElementById('warningBox');

\&#x20;   box.classList.remove('hidden');

\&#x20;   setTimeout(() => {

\&#x20;       box.classList.add('hidden');

\&#x20;   }, 3500);

}


</body>

</html>



***/frontend/pages Update.php***

<?php



$conn = mysqli\_connect(

&#x20;   "localhost",

&#x20;   "root",

&#x20;   "",

&#x20;   "apotek\_db"

);



$id         = $\_POST\['id'];

$nama\_obat  = $\_POST\['nama\_obat'];

$kategori   = $\_POST\['kategori'];

$stok       = $\_POST\['stok'];

$harga      = $\_POST\['harga'];



mysqli\_query(

&#x20;   $conn,

&#x20;   "UPDATE obat SET

&#x20;   nama\_obat='$nama\_obat',

&#x20;   kategori='$kategori',

&#x20;   stok='$stok',

&#x20;   harga='$harga'

&#x20;   WHERE id='$id'"

);



header("Location: dashboard.php");

exit;

?>




hash.php
index.php

