<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\Task;
use App\Models\TodoList;

/**
 * UserController
 * 
 * Handles user profile and account information
 */
class UserController extends Controller
{
    private $userModel;
    private $taskModel;
    private $listModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->taskModel = new Task();
        $this->listModel = new TodoList();
    }

    /**
     * Show user profile page with statistics
     */
    public function profile()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        // Get user info
        $user = $this->userModel->findById($userId);
        
        // Get all user lists
        $userLists = $this->listModel->getListsByUserId($userId);
        $taskCounts = $this->taskModel->getTaskCounts($userId, $userLists);

        // Get statistics
        $stats = $this->taskModel->getStatistics($userId);

        $this->view('user/profile', [
            'title' => 'My Account',
            'user' => $user,
            'userLists' => $userLists,
            'taskCounts' => $taskCounts,
            'stats' => $stats
        ]);
    }
}
