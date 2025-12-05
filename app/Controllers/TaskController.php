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
 * 
 * MVC Flow Example (Create Task):
 * 1. User visits /tasks/create
 * 2. Router dispatches to TaskController->create()
 * 3. Controller shows form View
 * 4. User fills form and uploads image
 * 5. Controller processes $_FILES, saves image
 * 6. Controller uses Task Model to save to database
 * 7. Controller redirects to task list
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

        // Load view and pass tasks data
        $this->view('tasks/index', [
            'title' => $title,
            'tasks' => $tasks,
            'userLists' => $userLists,
            'active_filter' => $filter,
            'currentList' => $currentList
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
        $this->view('tasks/create', [
            'title' => 'Create New Task',
            'userLists' => $this->listModel->getListsByUserId(Session::get('user_id')),
            'active_filter' => $_GET['list'] ?? null
        ]);
    }

    /**
     * Process create task form submission
     * This includes handling file upload!
     */
    private function handleCreate()
    {
        $userId = Session::get('user_id');
        
        // Get form data
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $listId = !empty($_POST['list_id']) ? $_POST['list_id'] : null;

        // Validate input
        if (empty($title)) {
            Session::flash('error', 'Title is required');
            $this->redirect('/tasks/create');
            return;
        }

        // Handle file upload
        $imageName = $this->handleFileUpload();

        // Create task in database
        $result = $this->taskModel->create([
            'user_id' => $userId,
            'list_id' => $listId,
            'title' => $title,
            'description' => $description,
            'image' => $imageName
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
        $this->view('tasks/edit', [
            'title' => 'Edit Task',
            'task' => $task,
            'userLists' => $this->listModel->getListsByUserId($userId)
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

        // Validate
        if (empty($title)) {
            Session::flash('error', 'Title is required');
            $this->redirect('/tasks/edit?id=' . $taskId);
            return;
        }

        // Handle file upload (if new file uploaded)
        $imageName = $this->handleFileUpload();
        
        // If no new image, keep the old one
        if (!$imageName) {
            $imageName = $currentTask['image'];
        }

        // Update task in database
        $result = $this->taskModel->update($taskId, [
            'title' => $title,
            'description' => $description,
            'list_id' => $listId,
            'image' => $imageName
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

        $this->redirect('/');
    }

    /**
     * Handle file upload
     * 
     * This is the key part for file upload!
     * $_FILES is a PHP superglobal that contains uploaded file information
     * 
     * @return string|null Uploaded filename or null
     */
    private function handleFileUpload()
    {
        // Check if file was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;  // No file uploaded
        }

        // Check for upload errors
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'File upload error');
            return null;
        }

        // Validate file type (only images)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            Session::flash('error', 'Only image files are allowed');
            return null;
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024;  // 5MB in bytes
        if ($_FILES['image']['size'] > $maxSize) {
            Session::flash('error', 'File size must be less than 5MB');
            return null;
        }

        // Generate unique filename to avoid conflicts
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '_' . time() . '.' . $extension;

        // Define upload directory
        $uploadDir = __DIR__ . '/../../public/uploads/';
        $uploadPath = $uploadDir . $uniqueName;

        // Move uploaded file from temporary location to our uploads folder
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            return $uniqueName;  // Return filename to save in database
        } else {
            Session::flash('error', 'Failed to save uploaded file');
            return null;
        }
    }
}
