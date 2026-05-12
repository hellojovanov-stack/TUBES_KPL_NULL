Hafizh Arqamilandri Wakhyudi   103122400044
Steven Taufik Fajar            103122400068
Zakaria                        103122400042
Jovanov Alvin Bertram          103122400045
Fathan Ghani                   103122400053


Struktur Folder
TUBES_KPL_NULL/
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
│   │   ├── login.php
│   │   ├── logout.php
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
│       ├── AuthTest.php
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
│   └── apotek_db.sql
│
├── index.php
│
├── hash.php
│


/backend/config Database.php
```
<?php

class Database {

  private $host = "localhost";

  private $db\_name = "apotek\_db";

  private $username = "root";

  private $password = "";



  private $connection;



  public function connect() {



      if ($this->connection === null) {



          try {



              $this->connection = new PDO(

                  "mysql:host={$this->host};dbname={$this->db\_name};charset=utf8",

                  $this->username,

                  $this->password

              );



              $this->connection->setAttribute(

                  PDO::ATTR\_ERRMODE,

                  PDO::ERRMODE\_EXCEPTION

              );



              $this->connection->setAttribute(

                  PDO::ATTR\_DEFAULT\_FETCH\_MODE,

                  PDO::FETCH\_ASSOC

              );



          } catch (PDOException $e) {



              die("Koneksi database gagal : " . $e->getMessage());

          }

      }



      return $this->connection;

  }

}
```

***/backend/controllers AuthController.php***
```
<?php





require\_once \_\_DIR\_\_ . '/../models/User.php';

require\_once \_\_DIR\_\_ . '/../helpers/Validator.php';

require\_once \_\_DIR\_\_ . '/../helpers/Response.php';

require\_once \_\_DIR\_\_ . '/../helpers/Performance.php';



class AuthController {



  private $userModel;



  public function \_\_construct() {

      

      if (session\_status() === PHP\_SESSION\_NONE) {

          session\_start();

      }

      $this->userModel = new User();

  }



  public function login() {

      try {

          

          error\_log("Login attempt: " . print\_r($\_POST, true));

          if ($\_SERVER\['REQUEST\_METHOD'] !== 'POST') {

              return Response::json(false, "Method tidak diizinkan");

          }

          

          if (empty($\_POST\['username']) || empty($\_POST\['password'])) {

              return Response::json(false, "Username dan password wajib diisi");

          }

          

          Validator::required($\_POST\['username'] ?? '', 'Username');

          Validator::required($\_POST\['password'] ?? '', 'Password');



          $user = $this->userModel->login(

              $\_POST\['username'],

              $\_POST\['password']

          );



          if (!$user) {

              error\_log("Login failed for user: " . $\_POST\['username']);

              return Response::json(false, "Username / Password salah");

          }



          session\_regenerate\_id(true);

          

          $\_SESSION\['login'] = true;

          $\_SESSION\['username'] = $user\['username'];

          $\_SESSION\['user\_id'] = $user\['id'] ?? 1;

          

          error\_log("Login success for user: " . $user\['username']);



          return Response::json(true, "Login berhasil");



      } catch (Exception $e) {

          error\_log("Login error: " . $e->getMessage());

          return Response::json(false, $e->getMessage());

      }

  }



  public function logout() {

      if (session\_status() === PHP\_SESSION\_NONE) {

          session\_start();

      }

      



      $\_SESSION = \[];

      

      if (ini\_get("session.use\_cookies")) {

          $params = session\_get\_cookie\_params();

          setcookie(session\_name(), '', time() - 42000,

              $params\["path"], $params\["domain"],

              $params\["secure"], $params\["httponly"]

          );

      }

      

      session\_destroy();

      

      if (!empty($\_SERVER\['HTTP\_X\_REQUESTED\_WITH']) \&\& strtolower($\_SERVER\['HTTP\_X\_REQUESTED\_WITH']) == 'xmlhttprequest') {

          return Response::json(true, "Logout berhasil");

      } else {

          header("Location: ../../frontend/pages/login.php");

          exit;

      }

  }

}
```

***/backend/controllers ObatController.php***
<?php

require\_once \_\_DIR\_\_ . '/../models/Obat.php';



class ObatController {

  private $obatModel;



  public function \_\_construct() {

      $this->obatModel = new Obat();

  }



  public function index() {

      $data = $this->obatModel->getAll();

      require\_once \_\_DIR\_\_ . '/../../frontend/pages/dashboard.php';

  }



  public function search() {

      $keyword = $\_GET\['keyword'] ?? '';

      if (empty($keyword)) {

          $data = $this->obatModel->getAll();

      } else {

          $data = $this->obatModel->search($keyword);

      }

      header('Content-Type: application/json');

      echo json\_encode($data);

      exit;

  }



  public function store() {

      $nama = $\_POST\['nama\_obat'];

      $kategori = $\_POST\['kategori'];

      $stok = $\_POST\['stok'];

      $harga = $\_POST\['harga'];

      

      $gambar = "";

      if (isset($\_FILES\['gambar']) \&\& $\_FILES\['gambar']\['error'] == 0) {

          $targetDir = "../../frontend/uploads/";

          if (!file\_exists($targetDir)) {

              mkdir($targetDir, 0777, true);

          }

          $gambar = time() . "\_" . $\_FILES\['gambar']\['name'];

          move\_uploaded\_file($\_FILES\['gambar']\['tmp\_name'], $targetDir . $gambar);

      }



      $this->obatModel->create($nama, $kategori, $stok, $harga, $gambar);

      header("Location: ../../frontend/pages/dashboard.php");

      exit;

  }



  public function edit() {

      $id = $\_POST\['id'];

      $nama = $\_POST\['nama\_obat'];

      $kategori = $\_POST\['kategori'];

      $stok = $\_POST\['stok'];

      $harga = $\_POST\['harga'];

      

      $obatLama = $this->obatModel->getById($id);

      $gambar = $obatLama\['gambar'];



      if (isset($\_FILES\['gambar']) \&\& $\_FILES\['gambar']\['error'] == 0) {

          $targetDir = "../../frontend/uploads/";

          if (!file\_exists($targetDir)) {

              mkdir($targetDir, 0777, true);

          }

          $gambar = time() . "\_" . $\_FILES\['gambar']\['name'];

          move\_uploaded\_file($\_FILES\['gambar']\['tmp\_name'], $targetDir . $gambar);

      }



      $this->obatModel->update($id, $nama, $kategori, $stok, $harga, $gambar);

      header("Location: ../../frontend/pages/dashboard.php");

      exit;

  }



  public function delete() {

      if (isset($\_GET\['id'])) {

          $this->obatModel->delete($\_GET\['id']);

      }

      header("Location: ../../frontend/pages/dashboard.php");

      exit;

  }

}
```
***/backend/controllers PerformanceTestController.php***
<?php



session\_start();



require\_once \_\_DIR\_\_ . '/../models/Obat.php';

require\_once \_\_DIR\_\_ . '/../models/User.php';

require\_once \_\_DIR\_\_ . '/../helpers/PerformanceLogger.php';



class PerformanceTestController {

  

  private $obatModel;

  private $userModel;

  

  public function \_\_construct() {

      $this->obatModel = new Obat();

      $this->userModel = new User();

  }

  

  // Test database connection performance

  public function testDatabaseConnection() {

      PerformanceLogger::start('db\_connection');

      

      $db = new Database();

      $conn = $db->connect();

      

      PerformanceLogger::end('db\_connection');

      

      return PerformanceLogger::getLog('db\_connection');

  }

  

  // Test getAll performance with different limits

  public function testGetAllPerformance() {

      $results = \[];

      

      // Test multiple times to get average

      for ($i = 1; $i <= 5; $i++) {

          $start = PerformanceLogger::start("getAll\_run\_{$i}");

          $data = $this->obatModel->getAll();

          PerformanceLogger::end("getAll\_run\_{$i}");

          $results\[$i] = count($data);

      }

      

      return PerformanceLogger::getLogs();

  }

  

  // Test login performance

  public function testLoginPerformance() {

      $testCredentials = \[

          \['admin', 'admin123'],

          \['wrong', 'password'],

          \['admin', 'wrongpass']

      ];

      

      foreach ($testCredentials as $cred) {

          $start = PerformanceLogger::start("login\_{$cred\[0]}");

          $result = $this->userModel->login($cred\[0], $cred\[1]);

          PerformanceLogger::end("login\_{$cred\[0]}");

      }

      

      return PerformanceLogger::getLogs();

  }

  

  // Comprehensive performance report

  public function runAllTests() {

      PerformanceLogger::enable();

      

      // Clear previous logs

      $testResults = \[];

      

      // 1. Test database connection

      $testResults\['db\_connection'] = $this->testDatabaseConnection();

      

      // 2. Test getAll

      $testResults\['getAll'] = $this->testGetAllPerformance();

      

      // 3. Test login

      $testResults\['login'] = $this->testLoginPerformance();

      

      // 4. Test search

      $testResults\['search'] = $this->obatModel->searchPerformanceTest();

      

      // Generate report

      return \[

          'timestamp' => date('Y-m-d H:i:s'),

          'results' => $testResults,

          'summary' => $this->generateSummary(),

          'recommendations' => $this->generateRecommendations()

      ];

  }

  

  private function generateSummary() {

      $logs = PerformanceLogger::getLogs();

      $summary = \[

          'total\_tests' => count($logs),

          'avg\_execution\_time' => 0,

          'avg\_memory\_usage' => 0,

          'fastest\_test' => \['name' => '', 'time' => PHP\_FLOAT\_MAX],

          'slowest\_test' => \['name' => '', 'time' => 0]

      ];

      

      $totalTime = 0;

      foreach ($logs as $name => $log) {

          $totalTime += $log\['execution\_time'];

          if ($log\['execution\_time'] < $summary\['fastest\_test']\['time']) {

              $summary\['fastest\_test'] = \['name' => $name, 'time' => $log\['execution\_time']];

          }

          if ($log\['execution\_time'] > $summary\['slowest\_test']\['time']) {

              $summary\['slowest\_test'] = \['name' => $name, 'time' => $log\['execution\_time']];

          }

      }

      

      $summary\['avg\_execution\_time'] = $totalTime / $summary\['total\_tests'];

      

      return $summary;

  }

  

  private function generateRecommendations() {

      $logs = PerformanceLogger::getLogs();

      $recommendations = \[];

      

      foreach ($logs as $name => $log) {

          $timeMs = $log\['execution\_time'] \* 1000;

          

          if ($timeMs > 500) {

              $recommendations\[] = "⚠️ {$name} terlalu lambat ({$timeMs}ms). Pertimbangkan optimasi query.";

          } elseif ($timeMs > 100) {

              $recommendations\[] = "📝 {$name} cukup lambat ({$timeMs}ms). Bisa dioptimasi.";

          } else {

              $recommendations\[] = "✅ {$name} performa baik ({$timeMs}ms).";

          }

          

          if ($log\['memory\_used'] > 5 \* 1024 \* 1024) { // > 5MB

              $recommendations\[] = "💾 {$name} menggunakan memory besar (" . round($log\['memory\_used'] / 1024 / 1024, 2) . "MB)";

          }

      }

      

      return $recommendations;

  }

}
```

***/backend/controllers TransaksiController.php***
```
<?php



session\_start();



require\_once \_\_DIR\_\_ . '/../models/Transaksi.php';

require\_once \_\_DIR\_\_ . '/../models/Obat.php';



class TransaksiController {



  private $transaksiModel;

  private $obatModel;



  public function \_\_construct() {



      $this->transaksiModel = new Transaksi();

      $this->obatModel      = new Obat();



      if (!isset($\_SESSION\['cart'])) {



          $\_SESSION\['cart'] = \[];

      }

  }



  /\*

  |--------------------------------------------------------------------------

  | ADD TO CART

  |--------------------------------------------------------------------------

  \*/



  public function tambah() {



      $id\_obat = (int) ($\_POST\['id\_obat'] ?? 0);

      $jumlah  = (int) ($\_POST\['jumlah'] ?? 0);



      if ($id\_obat <= 0 || $jumlah <= 0) {



          return \[

              "success" => false,

              "message" => "Input transaksi tidak valid"

          ];

      }



      $obat = $this->obatModel->getById($id\_obat);



      if (!$obat) {



          return \[

              "success" => false,

              "message" => "Obat tidak ditemukan"

          ];

      }



      if ($jumlah > $obat\['stok']) {



          return \[

              "success" => false,

              "message" => "Stok tidak mencukupi"

          ];

      }



      $\_SESSION\['cart']\[] = \[



          "id"       => $obat\['id'],

          "nama"     => $obat\['nama\_obat'],

          "harga"    => $obat\['harga'],

          "jumlah"   => $jumlah,

          "subtotal" => $obat\['harga'] \* $jumlah

      ];



      return \[

          "success" => true,

          "message" => "Berhasil tambah ke keranjang"

      ];

  }



  /\*

  |--------------------------------------------------------------------------

  | CHECKOUT

  |--------------------------------------------------------------------------

  \*/



  public function bayar() {



      if (empty($\_SESSION\['cart'])) {



          return \[

              "success" => false,

              "message" => "Keranjang kosong"

          ];

      }



      foreach ($\_SESSION\['cart'] as $item) {



          $this->transaksiModel->create(

              $item\['id'],

              $item\['jumlah'],

              $item\['subtotal']

          );



          $this->obatModel->reduceStock(

              $item\['id'],

              $item\['jumlah']

          );

      }



      unset($\_SESSION\['cart']);



      return \[

          "success" => true,

          "message" => "Pembayaran berhasil"

      ];

  }



  /\*

  |--------------------------------------------------------------------------

  | CANCEL

  |--------------------------------------------------------------------------

  \*/



  public function batal() {



      unset($\_SESSION\['cart']);



      return \[

          "success" => true,

          "message" => "Transaksi dibatalkan"

      ];

  }

}
```

***/backend/helpers performance.php***
```
<?php



class Performance {



  public static function start() {

      return microtime(true);

  }



  public static function end($startTime) {

      $endTime = microtime(true);

      return round(($endTime - $startTime), 5);

  }

  

  // Tambahan: hitung waktu eksekusi dalam milliseconds

  public static function measure($callback) {

      $start = self::start();

      $result = $callback();

      $time = self::end($start);

      

      return \[

          'result' => $result,

          'time\_seconds' => $time,

          'time\_ms' => $time \* 1000

      ];

  }

  

  // Tambahan: benchmark multiple runs

  public static function benchmark($callback, $runs = 10) {

      $times = \[];

      

      for ($i = 0; $i < $runs; $i++) {

          $start = self::start();

          $callback();

          $times\[] = self::end($start);

      }

      

      return \[

          'min' => min($times) \* 1000,

          'max' => max($times) \* 1000,

          'avg' => (array\_sum($times) / $runs) \* 1000,

          'runs' => $runs

      ];

  }

}
*/**backend/helpers*** <b>performanceLogger.php</b>
<?php



class PerformanceLogger {

  

  private static $logs = \[];

  private static $isEnabled = true;

  

  // Enable/disable performance logging

  public static function enable() {

      self::$isEnabled = true;

  }

  

  public static function disable() {

      self::$isEnabled = false;

  }

  

  // Start measuring time and memory

  public static function start($operationName) {

      if (!self::$isEnabled) return null;

      

      self::$logs\[$operationName] = \[

          'start\_time' => microtime(true),

          'start\_memory' => memory\_get\_usage(),

          'end\_time' => null,

          'end\_memory' => null,

          'execution\_time' => null,

          'memory\_used' => null

      ];

      

      return $operationName;

  }

  

  // End measuring

  public static function end($operationName) {

      if (!self::$isEnabled || !isset(self::$logs\[$operationName])) return null;

      

      self::$logs\[$operationName]\['end\_time'] = microtime(true);

      self::$logs\[$operationName]\['end\_memory'] = memory\_get\_usage();

      self::$logs\[$operationName]\['execution\_time'] = 

          self::$logs\[$operationName]\['end\_time'] - self::$logs\[$operationName]\['start\_time'];

      self::$logs\[$operationName]\['memory\_used'] = 

          self::$logs\[$operationName]\['end\_memory'] - self::$logs\[$operationName]\['start\_memory'];

      

      return self::$logs\[$operationName];

  }

  

  // Get all logs

  public static function getLogs() {

      return self::$logs;

  }

  

  // Get specific log

  public static function getLog($operationName) {

      return self::$logs\[$operationName] ?? null;

  }

  

  // Display formatted report

  public static function displayReport() {

      echo "<pre>";

      echo "+==================================================================+\\n";

      echo "|                    PERFORMANCE TESTING REPORT                    |\\n";

      echo "+==================================================================+\\n";

      

      foreach (self::$logs as $name => $log) {

          $timeMs = round($log\['execution\_time'] \* 1000, 2);

          $memoryKb = round($log\['memory\_used'] / 1024, 2);

          $memoryMb = round($memoryKb / 1024, 2);

          

          $status = $timeMs < 100 ? "✅" : ($timeMs < 500 ? "⚠️" : "❌");

          

          echo sprintf(

              "║ %s %-30s : %8.2f ms  |  %8.2f KB  (%5.2f MB) ║\\n",

              $status,

              $name,

              $timeMs,

              $memoryKb,

              $memoryMb

          );

      }

      echo "+==================================================================+\\n";

      echo "</pre>";

  }

  

  // Save logs to file

  public static function saveToFile($filename = "performance\_log.json") {

      $data = \[

          'timestamp' => date('Y-m-d H:i:s'),

          'logs' => self::$logs

      ];

      file\_put\_contents(\_\_DIR\_\_ . '/../../logs/' . $filename, json\_encode($data, JSON\_PRETTY\_PRINT));

  }

}
```

***/backend/helpers response.php***
```
<?php



class Response {



  public static function json(

      $success,

      $message,

      $data = \[]

  ) {



      header('Content-Type: application/json');



      echo json\_encode(\[



          "success" => $success,

          "message" => $message,

          "data"    => $data



      ]);



      exit;

  }

}
```

***/backend/helpers validator.php***
```
<?php



class Validator {



  /\*

  |--------------------------------------------------------------------------

  | REQUIRED

  |--------------------------------------------------------------------------

  \*/



  public static function required($value, $fieldName) {



      if(empty(trim($value))) {



          throw new Exception(

              $fieldName . " wajib diisi"

          );

      }

  }



  /\*

  |--------------------------------------------------------------------------

  | NUMERIC

  |--------------------------------------------------------------------------

  \*/



  public static function numeric($value, $fieldName) {



      if(!is\_numeric($value)) {



          throw new Exception(

              $fieldName . " harus berupa angka"

          );

      }

  }



  /\*

  |--------------------------------------------------------------------------

  | MIN VALUE

  |--------------------------------------------------------------------------

  \*/



  public static function min($value, $min, $fieldName) {



      if($value < $min) {



          throw new Exception(

              $fieldName .

              " minimal " .

              $min

          );

      }

  }



  /\*

  |--------------------------------------------------------------------------

  | MAX LENGTH

  |--------------------------------------------------------------------------

  \*/



  public static function maxLength($value, $max, $fieldName) {



      if(strlen($value) > $max) {



          throw new Exception(

              $fieldName .

              " maksimal " .

              $max .

              " karakter"

          );

      }

  }

}
```

***/backend/middleware Authmiddleware.php***
```
<?php



class AuthMiddleware {



  public static function check() {



      session\_start();



      if(!isset($\_SESSION\['login'])) {



          header("Location: ../frontend/pages/login.php");

          exit;

      }

  }

}
```

***/backend/models Obat.php***
```
<?php

require\_once \_\_DIR\_\_ . '/../config/Database.php';



class Obat {

  private $conn;



  public function \_\_construct() {

      $database = new Database();

      $this->conn = $database->connect();

  }



  public function getAll() {

      $query = "SELECT \* FROM obat ORDER BY id DESC";

      $stmt = $this->conn->prepare($query);

      $stmt->execute();

      return $stmt->fetchAll();

  }



  public function search($keyword) {

      $query = "SELECT \* FROM obat WHERE nama\_obat LIKE :keyword OR kategori LIKE :keyword ORDER BY id DESC";

      $stmt = $this->conn->prepare($query);

      $searchTerm = "%{$keyword}%";

      $stmt->bindParam(':keyword', $searchTerm);

      $stmt->execute();

      return $stmt->fetchAll();

  }





  public function getById($id) {

      $query = "SELECT \* FROM obat WHERE id = :id";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':id', $id);

      $stmt->execute();

      return $stmt->fetch();

  }



  public function create($nama, $kategori, $stok, $harga, $gambar) {

      $query = "INSERT INTO obat (nama\_obat, kategori, stok, harga, gambar) 

                VALUES (:nama, :kategori, :stok, :harga, :gambar)";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':nama', $nama);

      $stmt->bindParam(':kategori', $kategori);

      $stmt->bindParam(':stok', $stok);

      $stmt->bindParam(':harga', $harga);

      $stmt->bindParam(':gambar', $gambar);

      return $stmt->execute();

  }



  public function update($id, $nama, $kategori, $stok, $harga, $gambar) {

      $query = "UPDATE obat SET nama\_obat=:nama, kategori=:kategori, stok=:stok, harga=:harga, gambar=:gambar WHERE id=:id";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':id', $id);

      $stmt->bindParam(':nama', $nama);

      $stmt->bindParam(':kategori', $kategori);

      $stmt->bindParam(':stok', $stok);

      $stmt->bindParam(':harga', $harga);

      $stmt->bindParam(':gambar', $gambar);

      return $stmt->execute();

  }



  public function delete($id) {

      $query = "DELETE FROM obat WHERE id = :id";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':id', $id);

      return $stmt->execute();

  }



  public function reduceStock($id, $jumlah) {

      $query = "UPDATE obat SET stok = stok - :jumlah WHERE id = :id AND stok >= :jumlah";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':id', $id);

      $stmt->bindParam(':jumlah', $jumlah);

      return $stmt->execute();

  }

}
```

***/backend/models transaksi.php***
```
<?php



require\_once \_\_DIR\_\_ . "/../config/Database.php";



class Transaksi {



  private $conn;



  public function \_\_construct() {

      $database = new Database();

      $this->conn = $database->connect();

  }



  public function create($id\_obat, $jumlah, $total) {

      $query = "INSERT INTO transaksi (id\_obat, jumlah, total, tanggal) 

                VALUES (:id\_obat, :jumlah, :total, NOW())";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':id\_obat', $id\_obat);

      $stmt->bindParam(':jumlah', $jumlah);

      $stmt->bindParam(':total', $total);

      return $stmt->execute();

  }



  public function getAll() {

      $query = "SELECT t.\*, o.nama\_obat FROM transaksi t 

                JOIN obat o ON t.id\_obat = o.id 

                ORDER BY t.id DESC";

      $stmt = $this->conn->prepare($query);

      $stmt->execute();

      return $stmt->fetchAll();

  }

}
```

***/backend/models user.php***
```
<?php



require\_once \_\_DIR\_\_ . "/../config/Database.php";



class User {



  private $conn;



  public function \_\_construct() {

      $database = new Database();

      $this->conn = $database->connect();

  }



  public function login($username, $password) {

      if (empty($username) || empty($password)) {

          error\_log("Login: username or password empty");

          return false;

      }



      $query = "SELECT \* FROM users WHERE username = :username LIMIT 1";



      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(":username", $username);

      $stmt->execute();



      $user = $stmt->fetch();



      if (!$user) {

          error\_log("Login: user not found - " . $username);

          return false;

      }



      if (!password\_verify($password, $user\['password'])) {

          error\_log("Login: password wrong for user - " . $username);

          return false;

      }



      error\_log("Login: success for user - " . $username);

      return $user;

  }

  

  public function createUser($username, $password) {

      $hashedPassword = password\_hash($password, PASSWORD\_DEFAULT);

      $query = "INSERT INTO users (username, password) VALUES (:username, :password)";

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':username', $username);

      $stmt->bindParam(':password', $hashedPassword);

      return $stmt->execute();

  }

}
```

***/backend/routes auth.php***
```
<?php



require\_once "../controllers/AuthController.php";



$controller = new AuthController();



$action = $\_GET\['action'] ?? '';



switch ($action) {

  case 'login':

      $controller->login();

      break;

  case 'logout':

      $controller->logout();  // SEKARANG SUDAH ADA

      break;

  default:

      header('Content-Type: application/json');

      echo json\_encode(\[

          "success" => false,

          "message" => "Route tidak ditemukan"

      ]);

}
```

***/backend/routes login.php***
```
<?php



header("Content-Type: application/json");



error\_reporting(E\_ALL);

ini\_set('display\_errors', 1);



require\_once \_\_DIR\_\_ . "/../controllers/AuthController.php";



$auth = new AuthController();



try {

  $auth->login();

} catch (Exception $e) {

  http\_response\_code(500);

  echo json\_encode(\[

      "success" => false,

      "message" => "Server error: " . $e->getMessage()

  ]);

}

```
***/backend/routes logout.php***
```
<?php

if (session\_status() === PHP\_SESSION\_NONE) {

  session\_start();

}



$\_SESSION = \[];



session\_destroy();



// arahkan langsung ke login frontend

header("Location: ../../frontend/pages/login.php");

exit;
```

***/backend/routes obat.php***
```
<?php

require\_once '../controllers/ObatController.php';



$obat = new ObatController();

$action = $\_GET\['action'] ?? '';



switch($action) {

  case 'create':

      $obat->store();

      break;

  case 'edit':

      $obat->edit();

      break;

  case 'delete':

      $obat->delete();

      break;

  case 'search':        

      $obat->search();

      break;

  default:

      $obat->index();

      break;

}
```
***/backend/routes performance.php***
```
<?php



require\_once \_\_DIR\_\_ . '/../controllers/PerformanceTestController.php';

require\_once \_\_DIR\_\_ . '/../helpers/PerformanceLogger.php';



$controller = new PerformanceTestController();

$action = $\_GET\['action'] ?? 'report';



switch ($action) {

  case 'run':

      // Run all tests and return JSON

      header('Content-Type: application/json');

      $results = $controller->runAllTests();

      echo json\_encode($results, JSON\_PRETTY\_PRINT);

      break;

      

  case 'report':

      // Display HTML report

      $controller->runAllTests();

        <!DOCTYPE html>
        <html>
        <head>
            <title>Performance Testing Report</title>
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    background: #1e1e2e;
                    color: #cdd6f4;
                    padding: 20px;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                }
                .header {
                    background: #313244;
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
                .test-card {
                    background: #181825;
                    border-radius: 10px;
                    margin-bottom: 15px;
                    overflow: hidden;
                }
                .test-header {
                    background: #45475a;
                    padding: 12px 20px;
                    font-weight: bold;
                    cursor: pointer;
                }
                .test-body {
                    padding: 20px;
                    display: none;
                }
                .test-body.show {
                    display: block;
                }
                .metric {
                    display: inline-block;
                    background: #313244;
                    padding: 5px 10px;
                    border-radius: 5px;
                    margin: 5px;
                    font-size: 12px;
                }
                .good { color: #a6e3a1; }
                .warning { color: #f9e2af; }
                .bad { color: #f38ba8; }
                pre {
                    background: #11111b;
                    padding: 15px;
                    border-radius: 8px;
                    overflow-x: auto;
                }
                button {
                    background: #89b4fa;
                    color: #1e1e2e;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: bold;
                    margin-right: 10px;
                }
                button:hover {
                    background: #b4befe;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⚡ Performance Testing Report</h1>
                    <p>Generated: <?= date('Y-m-d H:i:s') ?></p>
                    <button onclick="runTests()">🔄 Run Tests Again</button>
                    <button onclick="exportJSON()">📥 Export JSON</button>
                </div>
 
                <div id="reportContent">
                    <?php PerformanceLogger::displayReport(); ?>
                </div>
 
                <div id="detailedReport"></div>
            </div>
 
            <script>
                async function runTests() {
                    document.getElementById('reportContent').innerHTML = '<div style="text-align:center">Running tests... ⏳</div>';
 
                    const response = await fetch('?action=run');
                    const data = await response.json();
 
                    displayDetailedReport(data);
                }
 
                function displayDetailedReport(data) {
                    let html = '<h2>📊 Detailed Analysis</h2>';
 
                    for (const [testName, result] of Object.entries(data.results)) {
                        html += `
                            <div class="test-card">
                                <div class="test-header" onclick="toggleTest(this)">
                                    📈 ${testName}
                                </div>
                                <div class="test-body">
                                    <pre>${JSON.stringify(result, null, 2)}</pre>
                                </div>
                            </div>
                        `;
                    }
 
                    html += `
                        <div class="test-card">
                            <div class="test-header" onclick="toggleTest(this)">
                                📋 Summary
                            </div>
                            <div class="test-body">
                                <pre>${JSON.stringify(data.summary, null, 2)}</pre>
                            </div>
                        </div>
                        <div class="test-card">
                            <div class="test-header" onclick="toggleTest(this)">
                                💡 Recommendations
                            </div>
                            <div class="test-body">
                                <ul>
                                    ${data.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
 
                    document.getElementById('detailedReport').innerHTML = html;
                }
 
                function toggleTest(element) {
                    const body = element.nextElementSibling;
                    body.classList.toggle('show');
                }
 
                function exportJSON() {
                    fetch('?action=run')
                        .then(res => res.json())
                        .then(data => {
                            const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `performance\_test\_${Date.now()}.json`;
                            a.click();
                            URL.revokeObjectURL(url);
                        });
                }
            </script>
        </body>
        </html>
        <?php
        break;
 
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
}
```
/backend/routes Transaksi.php
```
<?php



require\_once '../controllers/TransactionController.php';



$transaction = new TransactionController();



$action = $\_GET\['action'] ?? '';



switch($action) {



  case 'add':

      $transaction->addToCart();

      break;



  case 'pay':

      $transaction->pay();

      break;



  case 'cancel':

      $transaction->cancel();

      break;



  default:

      $transaction->index();

}
```
***/backend/tests AuthTest.php***
```
<?php



require\_once \_\_DIR\_\_ . "/../models/User.php";



echo "UNIT TEST LOGIN\\n";



$userModel = new User();



/\*

|--------------------------------------------------------------------------

| TEST 1

|--------------------------------------------------------------------------

\*/



$result1 = $userModel->login("admin", "admin123");



if ($result1) {



  echo "TEST LOGIN VALID = BERHASIL\\n";



} else {



  echo "TEST LOGIN VALID = GAGAL\\n";

}



/\*

|--------------------------------------------------------------------------

| TEST 2

|--------------------------------------------------------------------------

\*/



$result2 = $userModel->login("salah", "123");



if (!$result2) {



  echo "TEST LOGIN INVALID = BERHASIL\\n";



} else {



  echo "TEST LOGIN INVALID = GAGAL\\n";

}
```
***/backend/tests LoginTests.php***
```
<?php



require\_once \_\_DIR\_\_ . "/../config/Database.php";



class User {



  private $conn;



  public function \_\_construct() {



      $database = new Database();



      $this->conn = $database->connect();

  }



  public function login($username, $password) {



      /\*

      |--------------------------------------------------------------------------

      | Defensive Programming

      |--------------------------------------------------------------------------

      \*/



      if (empty($username) || empty($password)) {



          return false;

      }



      $query = "SELECT \* FROM users WHERE username = :username";



      $stmt = $this->conn->prepare($query);



      $stmt->bindParam(':username', $username);



      $stmt->execute();



      $user = $stmt->fetch(PDO::FETCH\_ASSOC);



      if ($user \&\& password\_verify($password, $user\['password'])) {



          return $user;

      }



      return false;

  }

}
```
***/backend/tests ObatTests.php***
```
<?php



require\_once \_\_DIR\_\_ . "/../models/Obat.php";



echo "UNIT TEST OBAT\\n";



$obatModel = new Obat();



/\*

|--------------------------------------------------------------------------

| TEST GET ALL

|--------------------------------------------------------------------------

\*/



$data = $obatModel->getAll();



if (is\_array($data)) {



  echo "TEST GET ALL = BERHASIL\\n";



} else {



  echo "TEST GET ALL = GAGAL\\n";

}



/\*

|--------------------------------------------------------------------------

| TEST CREATE

|--------------------------------------------------------------------------

\*/



$create = $obatModel->create(

  "Paracetamol Test",

  "Tablet",

  10,

  5000,

  ""

);



if ($create) {



  echo "TEST CREATE = BERHASIL\\n";



} else {



  echo "TEST CREATE = GAGAL\\n";

}



/\*

|--------------------------------------------------------------------------

| TEST SEARCH

|--------------------------------------------------------------------------

\*/



$search = $obatModel->search("Paracetamol");



if (is\_array($search)) {



  echo "TEST SEARCH = BERHASIL\\n";



} else {



  echo "TEST SEARCH = GAGAL\\n";

}
```
***/backend/tests PerformanceTest.php***
```
<?php



require\_once \_\_DIR\_\_ . '/../helpers/Performance.php';

require\_once \_\_DIR\_\_ . '/../helpers/PerformanceLogger.php';

require\_once \_\_DIR\_\_ . '/../models/Obat.php';

require\_once \_\_DIR\_\_ . '/../config/Database.php';



class PerformanceTest {

  

  private $obatModel;

  private $testResults = \[];

  private $db;

  

  public function \_\_construct() {

      $this->obatModel = new Obat();

      $database = new Database();

      $this->db = $database->connect();

  }

  

  // Test response time under 100ms

  public function testResponseTime() {

      echo "\\n📈 TEST 1: RESPONSE TIME (< 100ms)\\n";

      echo str\_repeat("-", 50) . "\\n";

      

      $start = Performance::start();

      $data = $this->obatModel->getAll();

      $executionTime = Performance::end($start);

      

      $status = $executionTime < 0.1 ? "✅ PASS" : "❌ FAIL";

      $timeMs = $executionTime \* 1000;

      

      echo "GetAll execution time: " . round($timeMs, 2) . " ms\\n";

      echo "Status: {$status}\\n";

      echo "Threshold: 100ms\\n\\n";

      

      $this->testResults\['response\_time'] = \[

          'passed' => $executionTime < 0.1,

          'time\_ms' => round($timeMs, 2),

          'threshold\_ms' => 100

      ];

  }

  

  // Test memory usage under 2MB

  public function testMemoryUsage() {

      echo "\\n💾 TEST 2: MEMORY USAGE (< 2MB)\\n";

      echo str\_repeat("-", 50) . "\\n";

      

      $startMemory = memory\_get\_usage();

      $data = $this->obatModel->getAll();

      $endMemory = memory\_get\_usage();

      

      $memoryUsed = $endMemory - $startMemory;

      $memoryKb = round($memoryUsed / 1024, 2);

      

      $status = $memoryUsed < 2 \* 1024 \* 1024 ? "✅ PASS" : "❌ FAIL";

      

      echo "Memory used: {$memoryKb} KB\\n";

      echo "Status: {$status}\\n";

      echo "Threshold: 2048 KB\\n\\n";

      

      $this->testResults\['memory\_usage'] = \[

          'passed' => $memoryUsed < 2 \* 1024 \* 1024,

          'memory\_kb' => $memoryKb,

          'threshold\_kb' => 2048

      ];

  }

  

  // Test concurrent requests simulation

  public function testConcurrentRequests($requests = 10) {

      echo "\\n🔄 TEST 3: CONCURRENT REQUESTS ({$requests} requests)\\n";

      echo str\_repeat("-", 50) . "\\n";

      

      $start = Performance::start();

      

      for ($i = 0; $i < $requests; $i++) {

          $this->obatModel->getAll();

      }

      

      $totalTime = Performance::end($start);

      $avgTime = $totalTime / $requests;

      $avgTimeMs = $avgTime \* 1000;

      

      $status = $avgTime < 0.05 ? "✅ EXCELLENT" : ($avgTime < 0.1 ? "⚠️ GOOD" : "❌ SLOW");

      

      echo "Total time: " . round($totalTime \* 1000, 2) . " ms\\n";

      echo "Average per request: " . round($avgTimeMs, 2) . " ms\\n";

      echo "Status: {$status}\\n\\n";

      

      $this->testResults\['concurrent\_requests'] = \[

          'total\_requests' => $requests,

          'total\_time\_ms' => round($totalTime \* 1000, 2),

          'avg\_time\_ms' => round($avgTimeMs, 2),

          'status' => $status

      ];

  }

  

  // Test database query performance

  public function testQueryPerformance() {

      echo "\\n TEST 4: DATABASE QUERY PERFORMANCE\\n";

      echo str\_repeat("-", 50) . "\\n";

      

      $queries = \[

          'SELECT \* FROM obat' => "SELECT \* FROM obat LIMIT 10",

          'SELECT with WHERE' => "SELECT \* FROM obat WHERE id = 1",

          'SELECT with LIKE' => "SELECT \* FROM obat WHERE nama\_obat LIKE '%a%' LIMIT 10"

      ];

      

      foreach ($queries as $name => $query) {

          $start = Performance::start();

          $stmt = $this->db->prepare($query);

          $stmt->execute();

          $results = $stmt->fetchAll();

          $time = Performance::end($start);

          $timeMs = $time \* 1000;

          

          $status = $timeMs < 50 ? "✅" : ($timeMs < 100 ? "⚠️" : "❌");

          

          echo "{$status} {$name}: " . round($timeMs, 2) . " ms (rows: " . count($results) . ")\\n";

          

          $this->testResults\['queries']\[$name] = \[

              'time\_ms' => round($timeMs, 2),

              'rows\_returned' => count($results),

              'status' => $status

          ];

      }

      

      echo "\\n";

  }

  



  public function testLoadScaling() {

      echo "\\n📊 TEST 5: LOAD SCALING\\n";

      echo str\_repeat("-", 50) . "\\n";

      

      $testSizes = \[10, 50, 100];

      

      foreach ($testSizes as $size) {

          $start = Performance::start();

          

          // Simulate loading $size records dengan SELECT langsung

          for ($i = 0; $i < $size; $i++) {

              $query = "SELECT \* FROM obat WHERE id = 1";

              $stmt = $this->db->prepare($query);

              $stmt->execute();

              $stmt->fetch();

          }

          

          $time = Performance::end($start);

          $timeMs = $time \* 1000;

          

          echo "Size {$size}: " . round($timeMs, 2) . " ms (avg " . round($timeMs / $size, 2) . " ms/record)\\n";

          

          $this->testResults\['load\_scaling']\[$size] = \[

              'total\_time\_ms' => round($timeMs, 2),

              'avg\_per\_record\_ms' => round($timeMs / $size, 2)

          ];

      }

      

      echo "\\n";

  }

  

  // Test API endpoint performance

  public function testApiPerformance() {

      echo "\\n🌐 TEST 6: API ENDPOINT PERFORMANCE\\n";

      echo str\_repeat("-", 50) . "\\n";

      

      $endpoints = \[

          'GET obat' => '../../backend/routes/obat.php?action=search\&keyword=a',

          'GET transaksi' => '../../backend/routes/transaksi.php'

      ];

      

      foreach ($endpoints as $name => $url) {

          $start = Performance::start();

          

          // Simulate API call

          $ch = curl\_init("http://localhost{$url}");

          curl\_setopt($ch, CURLOPT\_RETURNTRANSFER, true);

          curl\_setopt($ch, CURLOPT\_TIMEOUT, 5);

          $response = curl\_exec($ch);

          $httpCode = curl\_getinfo($ch, CURLINFO\_HTTP\_CODE);

          curl\_close($ch);

          

          $time = Performance::end($start);

          $timeMs = $time \* 1000;

          

          $status = $timeMs < 100 ? "✅" : ($timeMs < 500 ? "⚠️" : "❌");

          

          echo "{$status} {$name}: " . round($timeMs, 2) . " ms (HTTP {$httpCode})\\n";

          

          $this->testResults\['api\_performance']\[$name] = \[

              'time\_ms' => round($timeMs, 2),

              'http\_code' => $httpCode,

              'status' => $status

          ];

      }

      

      echo "\\n";

  }

  

  // Generate final report

  public function generateReport() {

      echo "\\n";

      echo "+==================================================================+\\n";

      echo "║                    FINAL PERFORMANCE TEST REPORT                 ║\\n";

      echo "+==================================================================+\\n";

      

      $totalPassed = 0;

      $totalTests = 0;

      

      foreach ($this->testResults as $category => $result) {

          if (isset($result\['passed'])) {

              $totalTests++;

              if ($result\['passed']) $totalPassed++;

          }

      }

      

      // Check query performance for pass/fail

      if (isset($this->testResults\['queries'])) {

          foreach ($this->testResults\['queries'] as $query) {

              $totalTests++;

              if ($query\['time\_ms'] < 100) $totalPassed++;

          }

      }

      

      $passRate = ($totalPassed / max($totalTests, 1)) \* 100;

      

      echo sprintf("║ %-66s ║\\n", "Overall Pass Rate: " . round($passRate, 1) . "% ({$totalPassed}/{$totalTests})");

      echo "+==================================================================+\\n";

      // Response time result

      if (isset($this->testResults\['response\_time'])) {

          $icon = $this->testResults\['response\_time']\['passed'] ? "✅" : "❌";

          $timeMs = $this->testResults\['response\_time']\['time\_ms'];

          echo sprintf("║ %s Response Time    : %5.2f ms (threshold: 100ms)        ║\\n", $icon, $timeMs);

      }

      

      // Memory usage result

      if (isset($this->testResults\['memory\_usage'])) {

          $icon = $this->testResults\['memory\_usage']\['passed'] ? "✅" : "❌";

          $memoryKb = $this->testResults\['memory\_usage']\['memory\_kb'];

          echo sprintf("║ %s Memory Usage     : %5.2f KB (threshold: 2048 KB)      ║\\n", $icon, $memoryKb);

      }

      

      // Concurrent requests

      if (isset($this->testResults\['concurrent\_requests'])) {

          $avgMs = $this->testResults\['concurrent\_requests']\['avg\_time\_ms'];

          $statusIcon = $this->testResults\['concurrent\_requests']\['status'] === "✅ EXCELLENT" ? "✅" : "⚠️";

          echo sprintf("║ %s Concurrent (10)   : %5.2f ms avg per request           ║\\n", $statusIcon, $avgMs);

      }

      echo "+==================================================================+\\n";

      

      // Query results summary

      if (isset($this->testResults\['queries'])) {

          echo "║ 📊 Query Performance:                                              ║\\n";

          foreach ($this->testResults\['queries'] as $name => $query) {

              $icon = $query\['status'] === "✅" ? "✅" : ($query\['status'] === "⚠️" ? "⚠️" : "❌");

              $shortName = strlen($name) > 35 ? substr($name, 0, 32) . "..." : $name;

              echo sprintf("║    %s %-35s : %5.2f ms                         ║\\n", $icon, $shortName, $query\['time\_ms']);

          }

      }

      

      echo "+==================================================================+\\n";

      

      // Save to file

      $this->saveReport();

  }

  

  private function saveReport() {

      $report = \[

          'timestamp' => date('Y-m-d H:i:s'),

          'results' => $this->testResults,

          'server\_info' => \[

              'php\_version' => phpversion(),

              'memory\_limit' => ini\_get('memory\_limit'),

              'max\_execution\_time' => ini\_get('max\_execution\_time'),

              'database' => 'MySQL'

          ]

      ];

      

      $dir = \_\_DIR\_\_ . '/../logs';

      if (!file\_exists($dir)) {

          mkdir($dir, 0777, true);

      }

      

      $filename = $dir . '/performance\_report\_' . date('Ymd\_His') . '.json';

      file\_put\_contents($filename, json\_encode($report, JSON\_PRETTY\_PRINT));

      echo "\\n📁 Report saved to: logs/" . basename($filename) . "\\n";

  }

  

  // Run all tests

  public function runAll() {

      echo "\\n";

      echo "====================================================================\\n";

      echo "║              PERFORMANCE TESTING SUITE v2.0                      ║\\n";

      echo "====================================================================\\n";

      

      $this->testResponseTime();

      $this->testMemoryUsage();

      $this->testConcurrentRequests(10);

      $this->testQueryPerformance();

      $this->testLoadScaling();

      $this->testApiPerformance();

      $this->generateReport();

      

      return $this->testResults;

  }

}



// Run the tests if executed directly

if (php\_sapi\_name() === 'cli' || basename($\_SERVER\['PHP\_SELF']) === 'PerformanceTest.php') {

  $test = new PerformanceTest();

  $test->runAll();

}
```
***/backend/tests TransaksiTests.php***
```
<?php



require\_once \_\_DIR\_\_ . "/../models/Transaction.php";



echo "UNIT TEST TRANSACTION\\n";



$transaction = new Transaction();



/\*

|--------------------------------------------------------------------------

| TEST ADD CART

|--------------------------------------------------------------------------

\*/



$cart = \[];



$item = \[



  "id"       => 1,

  "nama"     => "Paracetamol",

  "harga"    => 5000,

  "jumlah"   => 2,

  "subtotal" => 10000

];



$cart\[] = $item;



if (count($cart) > 0) {



  echo "TEST ADD CART = BERHASIL\\n";



} else {



  echo "TEST ADD CART = GAGAL\\n";

}



/\*

|--------------------------------------------------------------------------

| TEST TOTAL

|--------------------------------------------------------------------------

\*/



$total = 0;



foreach ($cart as $c) {



  $total += $c\['subtotal'];

}



if ($total === 10000) {



  echo "TEST TOTAL = BERHASIL\\n";



} else {



  echo "TEST TOTAL = GAGAL\\n";

}



/\*

|--------------------------------------------------------------------------

| TEST CHECKOUT

|--------------------------------------------------------------------------

\*/



$result = $transaction->checkout($cart);



if ($result) {



  echo "TEST CHECKOUT = BERHASIL\\n";



} else {



  echo "TEST CHECKOUT = GAGAL\\n";

}


***/frontend/css style.css

/frontend/js login.js***
function handleLogin(event) {

  event.preventDefault();



  const username = document.getElementById("username").value;

  const password = document.getElementById("password").value;



  fetch("../../backend/routes/auth.php?action=login", {

      method: "POST",

      headers: {

          "Content-Type": "application/x-www-form-urlencoded"

      },

      body: `username=${encodeURIComponent(username)}\&password=${encodeURIComponent(password)}`

  })

  .then(res => res.json())

  .then(data => {



      if (data.success) {

          window.location.href = "../pages/dashboard.php";

      } else {

          document.getElementById("message").innerText = data.message;

      }



  })

  .catch(err => {

      console.log(err);

      document.getElementById("message").innerText = "Server error";

  });

}


***/frontend/js modal.js***
function openModal() {

  document.getElementById("modal").classList.remove("hidden");

}



function closeModal() {

  document.getElementById("modal").classList.add("hidden");

}



***/frontend/js search.js***
const SEARCH\_STATE = {



  IDLE: "IDLE",

  LOADING: "LOADING",

  SUCCESS: "SUCCESS",

  EMPTY: "EMPTY",

  ERROR: "ERROR"

};



let currentSearchState =

  SEARCH\_STATE.IDLE;



/\*

|--------------------------------------------------------------------------

| SEARCH OBAT

|--------------------------------------------------------------------------

\*/



async function searchObat() {



  const keyword =

      document.getElementById("searchInput")

      .value

      .trim();



  if (!keyword) {



      setSearchState(

          SEARCH\_STATE.EMPTY

      );



      renderEmpty();



      return;

  }



  setSearchState(

      SEARCH\_STATE.LOADING

  );



  try {



      const response = await fetch(

          `../../backend/routes/obat.php?action=search\&keyword=${encodeURIComponent(keyword)}`

      );



      const data =

          await response.json();



      if (data.length === 0) {



          setSearchState(

              SEARCH\_STATE.EMPTY

          );



          renderEmpty();



          return;

      }



      setSearchState(

          SEARCH\_STATE.SUCCESS

      );



      renderData(data);



  } catch (error) {



      setSearchState(

          SEARCH\_STATE.ERROR

      );



      renderError();

  }

}



/\*

|--------------------------------------------------------------------------

| FSM STATE

|--------------------------------------------------------------------------

\*/



function setSearchState(state) {



  currentSearchState = state;



  const status =

      document.getElementById("status");



  status.innerText =

      `STATE : ${state}`;

}



/\*

|--------------------------------------------------------------------------

| RENDER DATA

|--------------------------------------------------------------------------

\*/



function renderData(data) {



  const result =

      document.getElementById("result");



  result.innerHTML = "";



  data.forEach(obat => {



      result.innerHTML += `



          <div class="bg-white rounded-2xl p-5 border border-slate-200">



              <h3 class="font-bold text-slate-800 text-lg">

                  ${obat.nama\_obat}

              </h3>



              <p class="text-slate-500 text-sm mt-1">

                  ${obat.kategori}

              </p>



              <div class="mt-3 text-emerald-600 font-bold">

                  Stok : ${obat.stok}

              </div>



          </div>

      `;

  });

}



/\*

|--------------------------------------------------------------------------

| EMPTY

|--------------------------------------------------------------------------

\*/



function renderEmpty() {



  document.getElementById("result")

      .innerHTML =

      `

      <div class="text-slate-400 text-center py-10">

          Data tidak ditemukan

      </div>

      `;

}



/\*

|--------------------------------------------------------------------------

| ERROR

|--------------------------------------------------------------------------

\*/



function renderError() {



  document.getElementById("result")

      .innerHTML =

      `

      <div class="text-red-500 text-center py-10">

          Terjadi kesalahan sistem

      </div>

      `;

}


***/frontend/js transaction.js***
const TRANSACTION\_STATE = {



  DRAFT: "DRAFT",

  PENDING: "PENDING",

  COMPLETED: "COMPLETED",

  CANCELLED: "CANCELLED"

};



let transactionState =

  TRANSACTION\_STATE.DRAFT;



/\*

|--------------------------------------------------------------------------

| ADD TO CART

|--------------------------------------------------------------------------

\*/



function addToCart() {



  if (

      transactionState ===

      TRANSACTION\_STATE.COMPLETED

  ) {



      alert("Transaksi sudah selesai");



      return;

  }



  transactionState =

      TRANSACTION\_STATE.PENDING;



  renderTransactionState();

}



/\*

|--------------------------------------------------------------------------

| CHECKOUT

|--------------------------------------------------------------------------

\*/



async function pay() {



  if (

      transactionState !==

      TRANSACTION\_STATE.PENDING

  ) {



      alert(

          "Pembayaran tidak valid"

      );



      return;

  }



  try {



      const response = await fetch(

          "../../backend/routes/transaksi.php?action=bayar",

          {

              method: "POST"

          }

      );



      const data =

          await response.json();



      if (!data.success) {



          throw new Error(

              data.message

          );

      }



      transactionState =

          TRANSACTION\_STATE.COMPLETED;



      renderTransactionState();



      alert(data.message);



      window.location.reload();



  } catch (error) {



      alert(error.message);

  }

}



/\*

|--------------------------------------------------------------------------

| CANCEL

|--------------------------------------------------------------------------

\*/



async function cancelTransaction() {



  await fetch(

      "../../backend/routes/transaksi.php?action=batal"

  );



  transactionState =

      TRANSACTION\_STATE.CANCELLED;



  renderTransactionState();



  window.location.reload();

}



/\*

|--------------------------------------------------------------------------

| RENDER

|--------------------------------------------------------------------------

\*/



function renderTransactionState() {



  const stateBox =

      document.getElementById("trxState");



  if (!stateBox) return;



  stateBox.innerText =

      transactionState;

}


```
***/frontend/pages dashboard.php***
<?php

if (session\_status() === PHP\_SESSION\_NONE) {

  session\_start();

}



if (!isset($\_SESSION\['login'])) {

  header("Location: login.php");

  exit;

}



require\_once \_\_DIR\_\_ . '/../../backend/models/Obat.php';



$obatModel = new Obat();



// Handle search

$keyword = $\_GET\['keyword'] ?? '';

if (!empty($keyword)) {

  $data = $obatModel->search($keyword);

} else {

  $data = $obatModel->getAll();

}



// ⬇️ TAMBAHKAN INI UNTUK PASTIKAN $data SELALU ADA ⬇️

if (!isset($data) || $data === false) {

  $data = \[];

}

// ⬆️ TAMBAHKAN INI ⬆️




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700\&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800">

<!-- NAVBAR -->
<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="bg-emerald-600 p-1.5 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />
                </svg>
            </div>
            <span class="text-emerald-600 font-black text-xl uppercase tracking-tighter">
                Apotek<span class="text-slate-800">Sehat</span>
            </span>
        </div>
        <div class="flex items-center gap-6">
            <a href="dashboard.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">
                Dashboard
            </a>
            <a href="transaksi.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Transaksi
            </a>
            <a href="../../backend/routes/logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">
                Logout
            </a>
        </div>
    </div>
</nav>

<!-- MAIN -->
<main class="max-w-6xl mx-auto px-6 mt-10">
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Inventaris Obat</h2>
                <p class="text-slate-500">Cari dan kelola ketersediaan stok obat.</p>
            </div>
            <div class="mt-4 md:mt-0 px-4 py-1 bg-slate-100 text-slate-500 rounded-full text-\[10px] font-black uppercase tracking-\[0.2em]">
                <?= isset($data) ? count($data) : 0; ?> DATA
            </div>
        </div>

        <!-- SEARCH FORM -->
        <form method="GET" action="">
            <div class="flex flex-col md:flex-row gap-4">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? '') ?>"
                    class="flex-1 px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none"
                    placeholder="Masukkan nama obat...">
                <button type="submit" class="bg-emerald-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-emerald-700 transition-all">
                    Cari Obat
                </button>
                <button type="button" onclick="openModal()" class="bg-indigo-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all">
                    + Tambah
                </button>
            </div>
        </form>
    </div>

    <!-- CARD GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (isset($data) \&\& count($data) > 0): ?>
            <?php foreach ($data as $obat): ?>
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all">
                    <?php if (!empty($obat\['gambar'])): ?>
                        <img src="../uploads/<?= $obat\['gambar']; ?>" class="w-full h-48 object-cover rounded-2xl mb-5">
                    <?php endif; ?>
                    <div class="flex justify-between items-start mb-5">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($obat\['nama\_obat']); ?></h3>
                            <p class="text-slate-400 text-sm mt-1"><?= htmlspecialchars($obat\['kategori']); ?></p>
                        </div>
                        <div class="bg-emerald-100 text-emerald-700 px-4 py-1 rounded-full text-xs font-black uppercase">
                            Stok <?= $obat\['stok']; ?>
                        </div>
                    </div>
                    <div class="mb-6">
                        <p class="text-slate-400 text-sm mb-1">Harga</p>
                        <h2 class="text-3xl font-black text-emerald-600">Rp <?= number\_format($obat\['harga']); ?></h2>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="openEditModal('<?= $obat\['id']; ?>', '<?= addslashes($obat\['nama\_obat']); ?>', '<?= addslashes($obat\['kategori']); ?>', '<?= $obat\['stok']; ?>', '<?= $obat\['harga']; ?>')"
                            class="flex-1 text-center bg-indigo-50 text-indigo-600 py-3 rounded-2xl font-bold hover:bg-indigo-100 transition-all">
                            Edit
                        </button>
                        <a href="../../backend/routes/obat.php?action=delete&id=<?= $obat\['id']; ?>" onclick="return confirm('Yakin hapus obat?')"
                            class="flex-1 text-center bg-red-50 text-red-600 py-3 rounded-2xl font-bold hover:bg-red-100 transition-all">
                            Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-200">
                <p class="text-slate-400 text-lg">Tidak ada data obat</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- MODAL TAMBAH -->
<div id="modal" class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black">Tambah Obat</h2>
                <p class="text-emerald-100 text-sm">Tambahkan data obat baru</p>
            </div>
            <button onclick="closeModal()" class="text-3xl">×</button>
        </div>
        <form method="POST" action="../../backend/routes/obat.php?action=create" enctype="multipart/form-data" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-bold mb-2">Gambar Obat</label>
                <input type="file" name="gambar" accept="image/\*" class="w-full">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Nama Obat</label>
                <input type="text" name="nama\_obat" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Kategori</label>
                <input type="text" name="kategori" class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-2">Stok</label>
                    <input type="number" name="stok" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Harga</label>
                    <input type="number" name="harga" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold">Simpan Obat</button>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="editModal" class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black">Edit Obat</h2>
                <p class="text-emerald-100 text-sm">Ubah data obat</p>
            </div>
            <button onclick="closeEditModal()" class="text-3xl">×</button>
        </div>
        <form method="POST" action="../../backend/routes/obat.php?action=edit" enctype="multipart/form-data" class="p-6 space-y-5">
            <input type="hidden" name="id" id="edit\_id">
            <div>
                <label class="block text-sm font-bold mb-2">Gambar Baru</label>
                <input type="file" name="gambar" accept="image/\*" class="w-full">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Nama Obat</label>
                <input type="text" name="nama\_obat" id="edit\_nama" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Kategori</label>
                <input type="text" name="kategori" id="edit\_kategori" class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-2">Stok</label>
                    <input type="number" name="stok" id="edit\_stok" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Harga</label>
                    <input type="number" name="harga" id="edit\_harga" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold">Update Obat</button>
        </form>
    </div>
</div>

<script>

function openModal() {

  document.getElementById('modal').classList.remove('hidden');

}

function closeModal() {

  document.getElementById('modal').classList.add('hidden');

}

function openEditModal(id, nama, kategori, stok, harga) {

  document.getElementById('edit\_id').value = id;

  document.getElementById('edit\_nama').value = nama;

  document.getElementById('edit\_kategori').value = kategori;

  document.getElementById('edit\_stok').value = stok;

  document.getElementById('edit\_harga').value = harga;

  document.getElementById('editModal').classList.remove('hidden');

}

function closeEditModal() {

  document.getElementById('editModal').classList.add('hidden');

}

</body>
</html>
```
/frontend/pages login.php
```
<?php



if (session\_status() === PHP\_SESSION\_NONE) {

  session\_start();

}



if (isset($\_SESSION\['login'])) {



  header("Location: dashboard.php");

  exit;

}


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Apotek</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700\&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 text-slate-800">

<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-emerald-500 to-teal-700">

    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">

        <div class="text-center mb-8">

            <div class="bg-emerald-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 rotate-3">

                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />

                </svg>

            </div>

            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
                Login
            </h1>

            <p class="text-slate-500 mt-2">
                Apotek Sehat - Management System
            </p>

        </div>

        <form onsubmit="handleLogin(event)">

    <div class="space-y-5">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Username
            </label>

            <input
                type="text"
                id="username"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl"
                placeholder="Masukkan username"
                required
            >
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Password
            </label>

            <input
                type="password"
                id="password"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl"
                placeholder="Masukkan password"
                required
            >
        </div>

        <button
            type="submit"
            id="loginBtn"
            class="w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl"
        >
            Masuk ke Sistem
        </button>

        <p
            id="message"
            class="text-center text-sm font-medium text-red-500"
        ></p>

    </div>

</form>

    </div>

</div>
<script src="../js/login.js"></script>
</body>
</html>
```
/frontend/pages transaksi.php
```
<?php

if (session\_status() === PHP\_SESSION\_NONE) {

  session\_start();

}



if (!isset($\_SESSION\['login'])) {

  header("Location: login.php");

  exit;

}



// Koneksi database untuk ambil data obat

$conn = mysqli\_connect("localhost", "root", "", "apotek\_db");



// Proses TAMBAH ke keranjang

if (isset($\_POST\['tambah'])) {

  $id\_obat = $\_POST\['id\_obat'];

  $jumlah = (int)$\_POST\['jumlah'];

  

  // Ambil data obat

  $result = mysqli\_query($conn, "SELECT \* FROM obat WHERE id = $id\_obat");

  $obat = mysqli\_fetch\_assoc($result);

  

  if ($obat \&\& $jumlah <= $obat\['stok']) {

      $item = \[

          'id' => $obat\['id'],

          'nama' => $obat\['nama\_obat'],

          'harga' => $obat\['harga'],

          'jumlah' => $jumlah,

          'subtotal' => $obat\['harga'] \* $jumlah

      ];

      

      if (!isset($\_SESSION\['cart'])) {

          $\_SESSION\['cart'] = \[];

      }

      $\_SESSION\['cart']\[] = $item;

  }

}



// Proses BAYAR

if (isset($\_POST\['bayar']) \&\& !empty($\_SESSION\['cart'])) {

  foreach ($\_SESSION\['cart'] as $item) {

      // Kurangi stok

      mysqli\_query($conn, "UPDATE obat SET stok = stok - {$item\['jumlah']} WHERE id = {$item\['id']}");

  }

  unset($\_SESSION\['cart']);

  echo "<script>alert('Pembayaran berhasil!'); window.location.href='transaksi.php';</script>";

  exit;

}



// Proses BATAL

if (isset($\_GET\['batal'])) {

  unset($\_SESSION\['cart']);

  echo "<script>alert('Transaksi dibatalkan'); window.location.href='transaksi.php';</script>";

  exit;

}



// Ambil data obat untuk dropdown

$query = mysqli\_query($conn, "SELECT \* FROM obat ORDER BY nama\_obat ASC");



// Hitung total

$total = 0;

if (!empty($\_SESSION\['cart'])) {

  foreach ($\_SESSION\['cart'] as $item) {

      $total += $item\['subtotal'];

  }

}


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

<!-- NAVBAR -->
<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="bg-emerald-600 p-1.5 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />
                </svg>
            </div>
            <span class="text-emerald-600 font-black text-xl uppercase tracking-tighter">
                Apotek<span class="text-slate-800">Sehat</span>
            </span>
        </div>
        <div class="flex items-center gap-6">
            <a href="dashboard.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Dashboard
            </a>
            <a href="transaksi.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">
                Transaksi
            </a>
            <a href="../../backend/routes/logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">
                Logout
            </a>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="max-w-5xl mx-auto px-6 mt-10">
    <div class="bg-white p-10 rounded-3xl shadow-xl border border-slate-200">
 
        <!-- HEADER -->
        <div class="flex justify-between items-center mb-10 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-3xl font-black text-slate-800">E-Kasir</h1>
                <p class="text-slate-400 mt-1">Kelola transaksi pelanggan dengan efisien.</p>
            </div>
            <div class="text-right">
                <span class="text-\[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">
                    Status Transaksi
                </span>
                <div class="px-6 py-2 rounded-2xl text-sm font-black uppercase tracking-wider <?= empty($\_SESSION\['cart']) ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700'; ?>">
                    <?= empty($\_SESSION\['cart']) ? 'DRAFT' : 'ACTIVE'; ?>
                </div>
            </div>
        </div>

        <!-- FORM TAMBAH -->
        <form method="POST" class="grid md:grid-cols-3 gap-4 mb-8">
            <select name="id\_obat" required class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="">Pilih Obat</option>
                <?php while ($obat = mysqli\_fetch\_assoc($query)): ?>
                    <option value="<?= $obat\['id']; ?>">
                        <?= $obat\['nama\_obat']; ?> - Stok <?= $obat\['stok']; ?> - Rp <?= number\_format($obat\['harga']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="jumlah" min="1" placeholder="Jumlah" required class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">
            <button type="submit" name="tambah" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black transition-all">
                + Tambah
            </button>
        </form>

        <!-- LIST TRANSAKSI -->
        <div class="space-y-5 mb-10">
            <?php if (!empty($\_SESSION\['cart'])): ?>
                <?php foreach ($\_SESSION\['cart'] as $index => $item): ?>
                    <div class="flex justify-between items-center p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex gap-4 items-center">
                            <div class="bg-white p-3 rounded-xl shadow-sm">💊</div>
                            <div>
                                <h3 class="font-bold text-slate-800"><?= $item\['nama']; ?></h3>
                                <p class="text-sm text-slate-500"><?= $item\['jumlah']; ?> x Rp <?= number\_format($item\['harga']); ?></p>
                            </div>
                        </div>
                        <span class="font-bold text-slate-700">Rp <?= number\_format($item\['subtotal']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-slate-50 rounded-3xl">
                    <p class="text-slate-400 font-medium">Belum ada transaksi.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- TOTAL -->
        <div class="bg-slate-50 rounded-3xl p-6 mb-8 border border-slate-200">
            <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Total Pembayaran</span>
                <h2 class="text-4xl font-black text-emerald-600">Rp <?= number\_format($total); ?></h2>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (empty($\_SESSION\['cart'])): ?>
                <button type="button" onclick="showWarning()" class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all bg-slate-200 text-slate-400 cursor-not-allowed">
                    Bayar Lunas
                </button>
            <?php else: ?>
                <form method="POST">
                    <button type="submit" name="bayar" class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-100">
                        Bayar Lunas
                    </button>
                </form>
            <?php endif; ?>
 
            <a href="<?= empty($\_SESSION\['cart']) ? '#' : 'transaksi.php?batal=1'; ?>"
               <?= empty($\_SESSION\['cart']) ? 'onclick="showWarning()"' : ''; ?>
               class="w-full flex items-center justify-center py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all <?= empty($\_SESSION\['cart']) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-red-50 text-red-600 hover:bg-red-100'; ?>">
                Batalkan
            </a>
        </div>

        <!-- WARNING BOX -->
        <div id="warningBox" class="hidden mt-5 bg-amber-50 border border-amber-200 text-amber-700 px-5 py-4 rounded-2xl text-sm font-medium">
            ⚠️ Belum ada transaksi yang bisa diproses.
        </div>
    </div>
</main>

<script>

function showWarning() {

  const box = document.getElementById('warningBox');

  box.classList.remove('hidden');

  setTimeout(() => {

      box.classList.add('hidden');

  }, 3500);

}

</body>
</html>
```
/frontend/pages Update.php
```
<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "apotek_db"
);

$id         = $_POST['id'];
$nama_obat  = $_POST['nama_obat'];
$kategori   = $_POST['kategori'];
$stok       = $_POST['stok'];
$harga      = $_POST['harga'];

mysqli_query(
    $conn,
    "UPDATE obat SET
    nama_obat='$nama_obat',
    kategori='$kategori',
    stok='$stok',
    harga='$harga'
    WHERE id='$id'"
);

header("Location: dashboard.php");
exit;
?>
```
