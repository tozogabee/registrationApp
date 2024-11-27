<?php
class LogManager {
    private static $instance = null; // Hold the single instance
    private $logFile;

    // Private constructor to initialize the log file
    private function __construct($logFile = 'log/backend.log') {
        $this->logFile = $logFile;
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true); // Create directory with permissions
        }
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, ""); // Create an empty log file
        } 
    }

    // Prevent cloning of the instance
    private function __clone() {}


    // Static method to get the single instance
    public static function getInstance($logFile = 'log/backend.log') {
        if (self::$instance === null) {
            self::$instance = new self($logFile);
        }
        return self::$instance;
    }

    public function logMessage($level, $message) {
        // Use debug_backtrace to get file and line of the log call
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1); // Get first trace
        $file = $backtrace[0]['file'] ?? 'unknown file';
        $line = $backtrace[0]['line'] ?? 'unknown line';

        $timestamp = date('Y-m-d H:i:s'); // Add a timestamp
        $formatted_message = "[$timestamp] [$level] [$file:$line] $message\n"; // Include file and line
        error_log($formatted_message, 3, $this->logFile); // Write to the log file
    }
}
?>
