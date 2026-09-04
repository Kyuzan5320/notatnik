<?php
require_once 'TaskManager.php';
require_once 'TaskView.php';

$manager = new TaskManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $manager->addTask($_POST['title'] ?? '', $_POST['note'] ?? '');
    } elseif ($_POST['action'] === 'toggle' && isset($_POST['id'])) {
        $manager->toggleTask($_POST['id']);
    } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $manager->deleteTask($_POST['id']);
    } elseif ($_POST['action'] === 'export') {
        $manager->exportToTextFile();
    }

    header('Location: index.php');
    exit;
}

$tasks = $manager->getAllTasks();
$stats = $manager->getStats();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Zadań</title>
    <style>

        :root {
            --bg-page: #f1f5f9;
            --bg-card: #ffffff;
            --bg-input: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --border: #e2e8f0;
            --card-hover-border: #cbd5e1;
            --completed-bg: #f8fafc;
            --completed-border: #edf2f7;
            --completed-text: #94a3b8;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
            --btn-action-bg: #e2e8f0;
            --btn-action-text: #334155;
        }

        body.dark-mode {
            --bg-page: #0f172a;
            --bg-card: #1e293b;
            --bg-input: #0f172a;
            --text-dark: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #818cf8;
            --accent-hover: #6366f1;
            --border: #334155;
            --card-hover-border: #475569;
            --completed-bg: #1e293b;
            --completed-border: #293548;
            --completed-text: #64748b;
            --danger: #f87171;
            --danger-bg: #3f1d24;
            --btn-action-bg: #334155;
            --btn-action-text: #f8fafc;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .app-container {
            width: 100%;
            max-width: 560px;
            background: var(--bg-card);
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

       .app-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap; /* Zapobiega łamaniu do nowej linii */
            gap: 12px;
            margin-bottom: 20px;
        }

        .app-header {
            margin: 0;
            font-size: 20px; /* Nieco mniejszy font, by nagłówek i przyciski zawsze swobodnie mieściły się w jednym rzędzie */
            font-weight: 700;
            white-space: nowrap; /* Tytuł nie łamie się w połowie */
        }

        .app-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0; /* Przyciski nie będą ściskane ani spychane */
        }

        .btn-action {
            background-color: var(--btn-action-bg);
            color: var(--btn-action-text);
            border: 1px solid var(--border);
            padding: 7px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }


        #themeToggle {
            min-width: 90px;
        }

        .btn-action:hover {
            opacity: 0.9;
        }

        .task-stats {
            background-color: var(--bg-input);
            border: 1px solid var(--border);
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
        }

        .task-stats__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .task-stats__bar-track {
            background-color: var(--border);
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
        }

        .task-stats__bar-fill {
            background-color: var(--accent);
            height: 100%;
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        .task-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }

        .task-form__input, .task-form__textarea {
            width: 100%;
            padding: 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
            background: var(--bg-input);
            color: var(--text-dark);
        }

        .task-form__input:focus, .task-form__textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .task-form__textarea {
            resize: vertical;
            min-height: 50px;
        }

        .task-form__submit {
            background-color: var(--accent);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .task-form__submit:hover {
            background-color: var(--accent-hover);
        }

        .tasks-container {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .task-card {
            background: var(--bg-card);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            transition: transform 0.15s ease, border-color 0.15s ease, background-color 0.3s ease;
        }

        .task-card:hover {
            border-color: var(--card-hover-border);
            transform: translateY(-1px);
        }

        .task-card__body {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            flex: 1;
        }

        .task-card__checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border: 2px solid var(--border);
            border-radius: 6px;
            outline: none;
            cursor: pointer;
            display: grid;
            place-content: center;
            flex-shrink: 0;
            margin-top: 1px;
            background: var(--bg-input);
            transition: all 0.2s;
        }

        .task-card__checkbox:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .task-card__checkbox:checked::before {
            content: "✔";
            color: white;
            font-size: 13px;
        }

        .task-card__title {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-dark);
            word-break: break-word;
        }

        .task-card__note {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: var(--text-muted);
            white-space: pre-wrap;
            word-break: break-word;
        }

        .task-card.is-completed {
            background-color: var(--completed-bg);
            border-color: var(--completed-border);
            opacity: 0.75;
        }

        .task-card.is-completed .task-card__title,
        .task-card.is-completed .task-card__note {
            text-decoration: line-through;
            color: var(--completed-text);
        }

        .task-card__btn-delete {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 15px;
            border-radius: 6px;
            padding: 4px 8px;
            transition: all 0.2s;
        }

        .task-card__btn-delete:hover {
            color: var(--danger);
            background-color: var(--danger-bg);
        }

        .empty-state {
            text-align: center;
            color: var(--text-muted);
            padding: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="app-container">
    <div class="app-topbar">
        <h1 class="app-header">📝 Notatki i Zadania</h1>
        <div class="app-actions">
            <?= TaskView::renderExportButton() ?>
            <?= TaskView::renderThemeToggle() ?>
        </div>
    </div>
    
    <?= TaskView::renderStats($stats) ?>

    <form method="POST" action="index.php" class="task-form">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" class="task-form__input" placeholder="Co jest do zrobienia?" required>
        <textarea name="note" class="task-form__textarea" placeholder="Dodatkowa notatka (opcjonalnie)..."></textarea>
        <button type="submit" class="task-form__submit">Dodaj nowe zadanie</button>
    </form>

    <ul class="tasks-container">
        <?php if (empty($tasks)): ?>
            <li class="empty-state">Brak zadań. Dodaj pierwsze zadanie powyżej!</li>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <?= TaskView::render($task) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<script>
    const themeToggleBtn = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');

    function updateThemeUI(isDark) {
        if (isDark) {
            document.body.classList.add('dark-mode');
            themeIcon.textContent = '☀️';
            themeText.textContent = 'Jasny';
        } else {
            document.body.classList.remove('dark-mode');
            themeIcon.textContent = '🌙';
            themeText.textContent = 'Ciemny';
        }
    }

    const savedTheme = localStorage.getItem('app_theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDarkInitial = savedTheme === 'dark' || (!savedTheme && prefersDark);

    updateThemeUI(isDarkInitial);

    themeToggleBtn.addEventListener('click', () => {
        const isCurrentlyDark = document.body.classList.contains('dark-mode');
        const nextThemeIsDark = !isCurrentlyDark;

        updateThemeUI(nextThemeIsDark);
        localStorage.setItem('app_theme', nextThemeIsDark ? 'dark' : 'light');
    });
</script>

</body>
</html>