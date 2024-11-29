<?php
class LogManager {
    private static $instance = null;
    private $logFile;

    private function __construct($logFile = 'log/backend.log') {
        $this->logFile = $logFile;
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, "");
        } 
    }

    private function __clone() {}

    public static function getInstance($logFile = 'log/backend.log') {
        if (self::$instance === null) {
            self::$instance = new self($logFile);
        }
        return self::$instance;
    }

    public function logMessage($level, $message) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'unknown file';
        $line = $backtrace[0]['line'] ?? 'unknown line';

        $timestamp = date('Y-m-d H:i:s'); // Add a timestamp
        $formatted_message = "[$timestamp] [$level] [$file:$line] $message\n";
        error_log($formatted_message, 3, $this->logFile);
    }
}
?>
