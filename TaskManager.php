<?php

require_once 'Task.php';

class TaskManager {
    private string $filePath;
    /** @var Task[] */
    private array $tasks = [];

    public function __construct(string $filePath = 'tasks.json') {
        $this->filePath = $filePath;
        $this->loadFromFile();
    }

    public function addTask(string $title, string $note = ''): void {
        $title = trim($title);
        if (!empty($title)) {
            $this->tasks[] = new Task($title, trim($note));
            $this->saveToFile();
        }
    }

    public function toggleTask(string $id): void {
        foreach ($this->tasks as $task) {
            if ($task->getId() === $id) {
                $task->toggle();
                $this->saveToFile();
                break;
            }
        }
    }

    public function deleteTask(string $id): void {
        $this->tasks = array_filter($this->tasks, function(Task $task) use ($id) {
            return $task->getId() !== $id;
        });
        $this->saveToFile();
    }

    public function getAllTasks(): array {
        return $this->tasks;
    }

    public function getStats(): array {
        $total = count($this->tasks);
        $completed = 0;

        foreach ($this->tasks as $task) {
            if ($task->isCompleted()) {
                $completed++;
            }
        }

        $percentage = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage
        ];
    }

    public function exportToTextFile(): void {
        $content = "=== MOJA LISTA ZADAŃ I NOTATEK ===\n";
        $content .= "Wygenerowano: " . date('Y-m-d H:i:s') . "\n\n";

        if (empty($this->tasks)) {
            $content .= "Brak zadań na liście.\n";
        } else {
            foreach ($this->tasks as $index => $task) {
                $status = $task->isCompleted() ? "[X]" : "[ ]";
                $nr = $index + 1;
                $content .= "{$nr}. {$status} {$task->getTitle()}\n";
                if (!empty($task->getNote())) {
                    $noteFormatted = str_replace("\n", "\n       ", $task->getNote());
                    $content .= "   -> Notatka: {$noteFormatted}\n";
                }
                $content .= "\n";
            }
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="zadania_' . date('Y-m-d') . '.txt"');
        header('Content-Length: ' . strlen($content));

        echo $content;
        exit;
    }

    private function loadFromFile(): void {
        if (!file_exists($this->filePath)) {
            return;
        }

        $json = file_get_contents($this->filePath);
        $data = json_decode($json, true);

        if (is_array($data)) {
            foreach ($data as $item) {
                $this->tasks[] = new Task(
                    $item['title'],
                    $item['note'] ?? '',
                    $item['completed'] ?? false,
                    $item['id'] ?? null
                );
            }
        }
    }

    private function saveToFile(): void {
        $data = array_map(function(Task $task) {
            return $task->toArray();
        }, $this->tasks);

        file_put_contents($this->filePath, json_encode(array_values($data), JSON_PRETTY_PRINT));
    }
}