<?php

require_once 'Task.php';

class TaskView {

    public static function renderExportButton(): string {
        return <<<HTML
        <form method="POST" action="index.php" style="margin: 0;">
            <input type="hidden" name="action" value="export">
            <button type="submit" class="btn-action" title="Pobierz listę jako plik tekstowy">
                💾 Zapisz do pliku (.txt)
            </button>
        </form>
HTML;
    }

    public static function renderThemeToggle(): string {
        return <<<HTML
        <button id="themeToggle" class="btn-action" type="button" aria-label="Zmień motyw">
            <span id="themeIcon">🌙</span>
            <span id="themeText">Ciemny</span>
        </button>
HTML;
    }

    public static function renderStats(array $stats): string {
        if ($stats['total'] === 0) {
            return '';
        }

        return <<<HTML
        <div class="task-stats">
            <div class="task-stats__header">
                <span class="task-stats__label">Postęp</span>
                <span class="task-stats__count">{$stats['completed']} z {$stats['total']} ukończone ({$stats['percentage']}%)</span>
            </div>
            <div class="task-stats__bar-track">
                <div class="task-stats__bar-fill" style="width: {$stats['percentage']}%;"></div>
            </div>
        </div>
HTML;
    }
    public static function render(Task $task): string {
        $isDone = $task->isCompleted();
        $id = htmlspecialchars($task->getId());
        $title = htmlspecialchars($task->getTitle());
        $note = htmlspecialchars($task->getNote());

        $itemClass = 'task-card' . ($isDone ? ' is-completed' : '');
        $checkedAttr = $isDone ? 'checked' : '';
        $noteHtml = !empty($note) ? sprintf('<p class="task-card__note">%s</p>', nl2br($note)) : '';

        return <<<HTML
        <li class="{$itemClass}">
            <div class="task-card__body">
                <form method="POST" action="index.php" class="task-card__toggle-form">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="{$id}">
                    <input type="checkbox" class="task-card__checkbox" onChange="this.form.submit()" {$checkedAttr}>
                </form>

                <div class="task-card__content">
                    <h4 class="task-card__title">{$title}</h4>
                    {$noteHtml}
                </div>
            </div>

            <form method="POST" action="index.php" class="task-card__delete-form">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="{$id}">
                <button type="submit" class="task-card__btn-delete" title="Usuń zadanie">✕</button>
            </form>
        </li>
HTML;
    }
}