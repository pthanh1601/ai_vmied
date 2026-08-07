<?php
namespace Neo\Core;

class Queue
{
    protected $db;
    protected $table = 'jobs';

    public function __construct(Database $db)
    {
        $this->db = $db;
        // Auto-create table for Demo purposes
        $this->ensureTable();
    }

    protected function ensureTable()
    {
        // Simple Medoo create if not exists check
        // Note: Medoo's create() syntax is powerful but raw query is safer for specific schema
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            handler TEXT NOT NULL,
            payload TEXT NOT NULL,
            available_at INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        // Execute raw PDO
        $this->db->pdo->exec($sql);
    }

    public function push($job, $delay = 0)
    {
        $payload = serialize($job);

        return $this->db->insert($this->table, [
            'handler' => get_class($job),
            'payload' => $payload,
            'available_at' => time() + $delay
        ]);
    }

    public function pop()
    {
        // Simple naive queue pop (First In First Out, ready)
        // Transaction to ensure atomic pop is ideal, but for SQLite/Demo standard select-delete is okay-ish
        // Better: Select 1 where available_at <= now, then delete

        $job = $this->db->get($this->table, '*', [
            'available_at[<=]' => time(),
            'ORDER' => ['id' => 'ASC']
        ]);

        if ($job) {
            // Remove from queue (or move to failed/processing)
            // Here we just delete to claim it. 
            // Warning: If crash happens between select and delete (rare here) or execution fails, job is lost.
            // Production systems use 'reserved_at' and timeouts.
            $this->db->delete($this->table, ['id' => $job['id']]);

            // Unserialize
            $instance = unserialize($job['payload']);
            return $instance;
        }

        return null;
    }

    public function work()
    {
        echo "Starting Queue Worker for table: {$this->table}\n";
        while (true) {
            $job = $this->pop();
            if ($job) {
                echo "Processing Job: " . get_class($job) . "\n";
                try {
                    if (method_exists($job, 'handle')) {
                        $job->handle();
                    } else {
                        echo "Error: Job missing handle() method.\n";
                    }
                    echo "Processed.\n";
                } catch (\Throwable $e) {
                    echo "Job Failed: " . $e->getMessage() . "\n";
                }
            } else {
                // Sleep to safe CPU
                sleep(1);
            }
        }
    }
}
