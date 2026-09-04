<?php

class Task {
    private string $id;
    private string $title;
    private string $note;
    private bool $completed;

    public function __construct(string $title, string $note = '', bool $completed = false, ?string $id = null) {
        $this->id = $id ?? uniqid();
        $this->title = $title;
        $this->note = $note;
        $this->completed = $completed;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getNote(): string {
        return $this->note;
    }

    public function isCompleted(): bool {
        return $this->completed;
    }

    public function toggle(): void {
        $this->completed = !$this->completed;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'note' => $this->note,
            'completed' => $this->completed
        ];
    }
}