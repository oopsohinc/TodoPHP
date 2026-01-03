<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Task;
use App\Models\TodoList;

/**
 * TaskController
 * 
 * Handles all task-related operations (CRUD):
 * - List tasks (index)
 * - Create new task
 * - Edit existing task
 * - Delete task
 */
class TaskController extends Controller
{
    private $taskModel;
    private $listModel;

    public function __construct()
    {
        $this->taskModel = new Task();
        $this->listModel = new TodoList();
    }

    /**
     * Show all tasks (home page)
     */
    public function index()
    {
        // Require user to be logged in
        $this->requireAuth();

        // Get current user's ID from session
        $userId = Session::get('user_id');

        $filter = $_GET['list'] ?? ($_GET['filter'] ?? 'inbox');

        $title = 'My Tasks';
        $currentList = null;

        if ($filter === 'important') {
            $title = 'Important Tasks';
        } elseif ($filter === 'my-day') {
            $title = 'My Day';
        } elseif ($filter === 'planned') {
            $title = 'Planned Tasks';
        } elseif (is_numeric($filter)) {
            // Custom list
            $currentList = $this->listModel->findById($filter, $userId);
            if ($currentList) {
                $title = $currentList['name'];
            } else {
                Session::flash('error', 'List not found');
                $this->redirect('/');
                return;
            }
        }

        // Get all tasks for this user from database
        $tasks = $this->taskModel->getTasksByUserId($userId, $filter);
        // Get all lists for sidebar (if needed)
        $userLists = $this->listModel->getListsByUserId($userId);
        // Get task counts for all filters and lists
        $taskCounts = $this->taskModel->getTaskCounts($userId, $userLists);

        // Load view and pass tasks data
        $this->view('tasks/index', [
            'title' => $title,
            'tasks' => $tasks,
            'userLists' => $userLists,
            'active_filter' => $filter,
            'currentList' => $currentList,
            'taskCounts' => $taskCounts
        ]);
    }

    /**
     * Show create task form
     */
    public function create()
    {
        // Require user to be logged in
        $this->requireAuth();

        // Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            return;
        }

        // Show create form (GET request)
        $userId = Session::get('user_id');
        $userLists = $this->listModel->getListsByUserId($userId);
        $taskCounts = $this->taskModel->getTaskCounts($userId, $userLists);
        $preSelectedListId = $_GET['list'] ?? null;
        
        $this->view('tasks/create', [
            'title' => 'Create New Task',
            'userLists' => $userLists,
            'preSelectedListId' => $preSelectedListId,
            'active_filter' => $preSelectedListId,
            'taskCounts' => $taskCounts
        ]);
    }

    /**
     * Process create task form submission
     */
    private function handleCreate()
    {
        $userId = Session::get('user_id');
        
        // Get form data
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $listId = !empty($_POST['list_id']) ? $_POST['list_id'] : null;
        $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $isImportant = !empty($_POST['is_important']) ? 1 : 0;

        // Validate input
        if (empty($title)) {
            Session::flash('error', 'Title is required');
            $this->redirect('/tasks/create');
            return;
        }

        // Create task in database
        $result = $this->taskModel->create([
            'user_id' => $userId,
            'list_id' => $listId,
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
            'is_important' => $isImportant
        ]);

        if ($result) {
            Session::flash('success', 'Task created successfully!');
            $this->redirect('/');
        } else {
            Session::flash('error', 'Failed to create task');
            $this->redirect('/tasks/create');
        }
    }

    /**
     * Show edit task form
     */
    public function edit()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        // Get task ID from URL query string
        $taskId = $_GET['id'] ?? null;

        if (!$taskId) {
            Session::flash('error', 'Task not found');
            $this->redirect('/');
            return;
        }

        // Get task from database
        $task = $this->taskModel->findById($taskId, $userId);

        if (!$task) {
            Session::flash('error', 'Task not found or access denied');
            $this->redirect('/');
            return;
        }

        // Handle POST request (form submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($taskId, $task);
            return;
        }

        // Show edit form
        $userLists = $this->listModel->getListsByUserId($userId);
        $taskCounts = $this->taskModel->getTaskCounts($userId, $userLists);
        
        $this->view('tasks/edit', [
            'title' => 'Edit Task',
            'task' => $task,
            'userLists' => $userLists,
            'taskCounts' => $taskCounts
        ]);
    }

    /**
     * Process edit task form submission
     */
    private function handleEdit($taskId, $currentTask)
    {
        $userId = Session::get('user_id');

        // Get form data
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $listId = !empty($_POST['list_id']) ? $_POST['list_id'] : null;
        $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $isImportant = !empty($_POST['is_important']) ? 1 : 0;

        // Validate
        if (empty($title)) {
            Session::flash('error', 'Title is required');
            $this->redirect('/tasks/edit?id=' . $taskId);
            return;
        }

        // Update task in database
        $result = $this->taskModel->update($taskId, [
            'title' => $title,
            'description' => $description,
            'list_id' => $listId,
            'due_date' => $dueDate,
            'is_important' => $isImportant
        ], $userId);

        if ($result) {
            Session::flash('success', 'Task updated successfully!');
            $this->redirect('/');
        } else {
            Session::flash('error', 'Failed to update task');
            $this->redirect('/tasks/edit?id=' . $taskId);
        }
    }

    /**
     * Delete a task
     */
    public function delete()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        // Get task ID from URL
        $taskId = $_GET['id'] ?? null;

        if (!$taskId) {
            Session::flash('error', 'Task not found');
            $this->redirect('/');
            return;
        }

        // Delete from database
        $result = $this->taskModel->delete($taskId, $userId);

        if ($result) {
            Session::flash('success', 'Task deleted successfully!');
        } else {
            Session::flash('error', 'Failed to delete task');
        }

        $this->redirect('/');
    }

    /**
     * Toggle task completion status
     * 
     * This action toggles a task between completed and pending states
     * Demonstrates a simple state toggle pattern in MVC
     */
    public function toggle()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        // Get task ID from URL
        $taskId = $_GET['id'] ?? null;

        if (!$taskId) {
            Session::flash('error', 'Task not found');
            $this->redirect('/');
            return;
        }

        // Toggle completion status
        $result = $this->taskModel->toggleComplete($taskId, $userId);

        if ($result) {
            Session::flash('success', 'Task status updated!');
        } else {
            Session::flash('error', 'Failed to update task status');
        }

        // Determine redirect URL to maintain current view
        $filter = $_GET['filter'] ?? ($_GET['list'] ?? 'inbox');
        if (is_numeric($filter)) {
            $this->redirect('/?list=' . $filter);
        } else {
            $this->redirect('/?filter=' . $filter);
        }
    }

    /**
     * Mark a task as important or remove important status
     * 
     * Toggles the is_important flag for a task
     * Redirects back to the current view (preserving filter/list)
     */
    public function star()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        // Get task ID from URL
        $taskId = $_GET['id'] ?? null;

        if (!$taskId) {
            Session::flash('error', 'Task not found');
            $this->redirect('/');
            return;
        }

        // Toggle important status
        $result = $this->taskModel->toggleImportant($taskId, $userId);

        if ($result) {
            Session::flash('success', 'Task importance updated!');
        } else {
            Session::flash('error', 'Failed to update task');
        }

        // Determine redirect URL to maintain current view
        $filter = $_GET['filter'] ?? ($_GET['list'] ?? 'inbox');
        if (is_numeric($filter)) {
            $this->redirect('/?list=' . $filter);
        } else {
            $this->redirect('/?filter=' . $filter);
        }
    }
}