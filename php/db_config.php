<?php
	// Load environment variables and configurations
	require_once __DIR__ . '/functions.php';
	// To prevent direct access to this file
	preventDirectAccess();

	require_once 'init.php';
	
	$envFile = dirname(__DIR__) . '/.env';
	// error_log("Loaded ENV from: $envFile");
	// if (file_exists($envFile)) {
	// 	error_log("ENV file exists");
	// } else {
	// 	error_log("ENV file missing");
	// }

	$envFile = dirname(__DIR__) . '/.env';
	if (file_exists($envFile)) {
		$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			if (strpos(trim($line), '#') === 0)
				continue;
			list($name, $value) = explode('=', $line, 2);
			$name = trim($name);
			$value = trim($value);

			putenv("$name=$value");
			$_ENV[$name] = $value;
			$_SERVER[$name] = $value;
		}
	}

	// Database constants
	if (!defined('DB_HOST'))
		define("DB_HOST", $_ENV['DB_HOST'] ?? 'localhost');
	if (!defined('DB_USER'))
		define("DB_USER", $_ENV['DB_USER'] ?? 'root');
	if (!defined('DB_PASS'))
		define("DB_PASS", $_ENV['DB_PASS'] ?? '');
	if (!defined('DB_NAME'))
		define("DB_NAME", $_ENV['DB_NAME'] ?? 'db_procurement');

	function determineRedirectURL($permissions): string
	{
		if (!$permissions)
			return 'index.php';

		if (!empty($permissions['can_manage_budget']) || !empty($permissions['can_approve_ppmp'])) {
			return 'Admin/index.php';
		} elseif (!empty($permissions['can_create_ppmp'])) {
			return 'Staff/index.php';
		}
		return 'index.php';
	}

	class db_connect
	{
		private $host;
		private $user;
		private $pass;
		private $name;
		protected $conn;
		public $error;

		public function __construct()
		{
			// error_log("CONST CHECK: HOST=" . (defined('DB_HOST') ? DB_HOST : 'NOT SET') .
			// 	" USER=" . (defined('DB_USER') ? DB_USER : 'NOT SET') .
			// 	" PASS=" . (defined('DB_PASS') ? DB_PASS : 'NOT SET') .
			// 	" NAME=" . (defined('DB_NAME') ? DB_NAME : 'NOT SET'));
			$this->host = DB_HOST;
			$this->user = DB_USER;
			$this->pass = DB_PASS;
			$this->name = DB_NAME;
		}

		public function connect(): bool
		{
			$this->conn = new mysqli(
				$_ENV['DB_HOST'] ?? 'localhost',
				$_ENV['DB_USER'] ?? 'root',
				$_ENV['DB_PASS'] ?? '',
				$_ENV['DB_NAME'] ?? ''
			);

			mysqli_query($this->conn, "SET time_zone = '+08:00'");
			// error_log("DB creds: host={$_ENV['DB_HOST']} user={$_ENV['DB_USER']} pass={$_ENV['DB_PASS']} name={$_ENV['DB_NAME']}");

			if ($this->conn->connect_error) {
				error_log(
					"Database connection error: " . $this->conn->connect_error,
					3,
					__DIR__ . '/logs/db_errors.log'
				);
				$this->error = "Fatal Error: Can't connect to database.";
				return false;
			}
			return true;
		}

		public function __destruct()
		{
			if ($this->conn) {
				$this->conn->close();
			}
		}
	}
