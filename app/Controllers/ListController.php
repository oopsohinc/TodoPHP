<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\TodoList;
use App\Models\Task;

class ListController extends Controller
{
    private $listModel;
    private $taskModel;

    public function __construct()
    {
        $this->listModel = new TodoList();
        $this->taskModel = new Task();
    }

    // 1. FORM TẠO LIST MỚI
    public function create()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');

        // Xử lý POST (Lưu)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                Session::flash('error', 'List name is required');
                $this->redirect('/lists/create');
                return;
            }

            $this->listModel->create($userId, $name);
            Session::flash('success', 'List created successfully');
            $this->redirect('/'); // Về trang chủ
            return;
        }

        // Xử lý GET (Hiện form)
        // Vẫn cần lấy danh sách sidebar để hiển thị layout
        $userLists = $this->listModel->getListsByUserId($userId);
        $taskCounts = $this->taskModel->getTaskCounts($userId, $userLists);

        $this->view('lists/create', [
            'title' => 'New List',
            'userLists' => $userLists,
            'taskCounts' => $taskCounts
        ]);
    }

    // 2. FORM SỬA LIST
    public function edit()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');
        $id = $_GET['id'] ?? null;

        $list = $this->listModel->findById($id, $userId);

        if (!$list) {
            Session::flash('error', 'List not found');
            $this->redirect('/');
            return;
        }

        // Xử lý POST (Cập nhật)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (!empty($name)) {
                $this->listModel->update($id, $userId, $name);
                Session::flash('success', 'List updated');
                $this->redirect('/tasks?list=' . $id); // Quay lại trang danh sách đó
            }
            return;
        }

        // Xử lý GET (Hiện form)
        $userLists = $this->listModel->getListsByUserId($userId);
        $taskCounts = $this->taskModel->getTaskCounts($userId, $userLists);

        $this->view('lists/edit', [
            'title' => 'Edit List',
            'list' => $list,
            'userLists' => $userLists,
            'taskCounts' => $taskCounts
        ]);
    }

    // 3. XÓA LIST
    public function delete()
    {
        $this->requireAuth();
        $userId = Session::get('user_id');
        $id = $_GET['id'] ?? null;

        if ($this->listModel->delete($id, $userId)) {
            Session::flash('success', 'List deleted');
            $this->redirect('/');
        } else {
            Session::flash('error', 'Failed to delete list');
            $this->redirect('/');
        }
    }
}