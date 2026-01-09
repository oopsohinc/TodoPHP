# Huong dan flow du an

Tai lieu nay tom tat cach luu thong tin va xu ly request theo kieu MVC trong TodoPHP, giai thich vai tro tung file chinh va cac diem code quan trong.

## Tong quan request
- Trinh duyet goi duong dan (vi du `/tasks/create`) hoac submit form tim kiem.
- Moi request vao [public/index.php](public/index.php); o day khoi tao `Router` va khai bao tat ca URL.
- `Router` map URL sang controller method (GET/POST) va goi thuc thi trong [app/Core/Router.php](app/Core/Router.php).
- Controller xu ly quyen (Session), goi Model doc/ghi DB, chuan bi data va render View qua `Controller::view()` tu [app/Core/Controller.php](app/Core/Controller.php).
- View dung output buffering dua noi dung vao [app/Views/layout.php](app/Views/layout.php), layout chen flash message, sidebar va assets -> tra HTML ve trinh duyet.

## Tom tat Project & Core Features
- Xac thuc nguoi dung: login/register, luu session, bao ve route.
- Quan ly task: xem theo filter (inbox, my-day, important, planned, theo list), tao/sua/xoa, toggle hoan thanh/important.
- Quan ly list: tao/sua/xoa list rieng cua tung user; sidebar hien dem theo tung list/filter.
- Tim kiem: form tren navbar GET den `/tasks/search?q=...`, tra ket qua day du trong view `tasks/index`.
- Ho so & thong ke: trang profile hien thong ke tong, completed/incomplete, important, ngay tao/cap nhat.
- Kien truc MVC don gian: `Router` -> `Controller` -> `Model` -> `View`, su dung PDO MySQL, flash message mot lan, layout chung.

### Du lieu di chuyen User ↔ Database
1. Trinh duyet (UI) tao request:
   - GET hien form (vi du xem tao task tai `/tasks/create`).
   - POST submit form (vi du tao task tu form).
2. [public/index.php](public/index.php) nhan request, `Router` dinh tuyen den controller method phu hop.
3. Controller trong [app/Controllers](app/Controllers) kiem tra session/quyen, doc input, goi Model tu [app/Models](app/Models) de tuong tac DB.
4. Model su dung PDO (khai bao trong [app/Core/Model.php](app/Core/Model.php)) thuc hien query an toan (prepared statements), tra ve du lieu dang associative.
5. Controller chuan bi `$data`, render View tu [app/Core/Controller.php](app/Core/Controller.php) -> [app/Views](app/Views) -> [app/Views/layout.php](app/Views/layout.php) -> HTML ve trinh duyet.

So do don gian: Trinh duyet → Router → Controller → Model (PDO) → DB → Controller → View → Trinh duyet.

### Doan code tuong tac tieu bieu
- Dinh tuyen route trong [public/index.php](public/index.php):

```php
// Khoi tao Router va khai bao mot so route mau
$router->get('/', 'TaskController@index');
$router->get('/tasks/create', 'TaskController@create');
$router->post('/tasks/create', 'TaskController@store');
$router->get('/tasks/search', 'TaskController@search');
```

- Controller goi Model va render View (vi du trong `TaskController`):

```php
public function store() {
  $this->requireAuth();
  $title = trim($_POST['title'] ?? '');
  $listId = $_POST['list_id'] ?? null;
  $dueDate = $_POST['due_date'] ?? null;
  $important = !empty($_POST['is_important']);

  if ($title === '') {
    \App\Core\Session::flash('error', 'Tieu de khong duoc de trong');
    return $this->redirect('/tasks/create');
  }

  $ok = \App\Models\Task::create($this->user['id'], $title, $listId, $dueDate, $important);
  \App\Core\Session::flash($ok ? 'success' : 'error', $ok ? 'Tao task thanh cong' : 'Tao task that bai');
  return $this->redirect('/tasks');
}
```

- Model su dung PDO (trong [app/Models/Task.php](app/Models/Task.php)) de ghi/lay du lieu:

```php
public static function create($userId, $title, $listId, $dueDate, $isImportant) {
  $db = static::getDB(); // from App\Core\Model
  $sql = 'INSERT INTO tasks (user_id, title, list_id, due_date, is_important) VALUES (?, ?, ?, ?, ?)';
  $stmt = $db->prepare($sql);
  return $stmt->execute([$userId, $title, $listId, $dueDate, $isImportant ? 1 : 0]);
}

public static function searchTasks($userId, $q) {
  $db = static::getDB();
  $like = "%$q%";
  $stmt = $db->prepare('SELECT * FROM tasks WHERE user_id = ? AND (title LIKE ? OR description LIKE ?) ORDER BY updated_at DESC');
  $stmt->execute([$userId, $like, $like]);
  return $stmt->fetchAll();
}
```

- Ket noi DB (trich tu [app/Core/Model.php](app/Core/Model.php)):

```php
protected static function getDB() {
  static $db;
  if (!$db) {
    $config = require __DIR__ . '/../../Config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $db = new \PDO($dsn, $config['username'], $config['password'], [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
      \PDO::ATTR_EMULATE_PREPARES => false,
    ]);
  }
  return $db;
}
```

- Render View qua layout (trich luot tu [app/Core/Controller.php](app/Core/Controller.php) va [app/Views/layout.php](app/Views/layout.php)):

```php
// Controller::view('tasks/index', $data)
ob_start();
require __DIR__ . '/../Views/' . $view . '.php';
$content = ob_get_clean();
require __DIR__ . '/../Views/layout.php';
```

- Ho so & thong ke (Profile/Statistics):
  - Route GET `/profile` vao `UserController::profile()`, yeu cau dang nhap.
  - Controller goi `User::findById()` de lay thong tin ca nhan, `TodoList::getListsByUserId()` de lay danh sach custom list cua user, `Task::getTaskCounts()` de dem task theo filter/list, `Task::getStatistics()` de tinh tong/hoan thanh/chua hoan thanh/quan trong/ty le hoan thanh.
  - View [app/Views/user/profile.php](app/Views/user/profile.php) hien thi:
    - Profile header: ten, email, ngay tham gia.
    - Stat cards: tong task, hoan thanh, chua hoan thanh, quan trong, ty le hoan thanh (%), tong so custom list.
    - Progress bar: hien thi thanh progress hoan thanh (%) va so task hoan thanh / tong.
    - Lists section: danh sach custom list voi so task cua moi list, nut "View Tasks", "Edit", "Delete" cho tung list.
  - Code tich hop du lieu:

```php
// UserController::profile() - lay du lieu cho profile
$user = $this->userModel->findById($userId);
$userLists = $this->listModel->getListsByUserId($userId);
$taskCounts = $this->taskModel->getTaskCounts($userId, $userLists);
$stats = $this->taskModel->getStatistics($userId);
// Sau do pass vao view qua $data: ['user' => $user, 'userLists' => $userLists, 'taskCounts' => $taskCounts, 'stats' => $stats]
```

```php
// Task::getStatistics() trong app/Models/Task.php - tinh toan chi so
public static function getStatistics($userId) {
  $db = static::getDB();
  $stmt = $db->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed FROM tasks WHERE user_id = ?');
  $stmt->execute([$userId]);
  $result = $stmt->fetch();
  $total = (int)$result['total'];
  $completed = (int)$result['completed'] ?? 0;
  $incomplete = $total - $completed;
  $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;
  // Tra ve mang chi so
  return [
    'total' => $total,
    'completed' => $completed,
    'incomplete' => $incomplete,
    'completion_rate' => $completionRate,
    'important' => /* ... so task quan trong ... */,
  ];
}
```

```php
// profile.php - render stat cards va lists
<?php foreach ($userLists as $list): ?>
  <?php $listCount = $taskCounts['lists'][$list['id']] ?? 0; ?>
  <div class="list-card">
    <h3><?= htmlspecialchars($list['name']) ?></h3>
    <span><?= $listCount ?> tasks</span>
    <a href="/tasks?list=<?= $list['id'] ?>">View Tasks</a>
  </div>
<?php endforeach; ?>
```

## Flow chi tiet: Tasks
- Route chinh (trong [public/index.php](public/index.php)):
  - GET `/` va `/tasks` -> `TaskController@index`
  - GET `/tasks/create` -> `TaskController@create`; POST `/tasks/create` -> `TaskController@store`
  - GET `/tasks/edit` -> `TaskController@edit`; POST `/tasks/edit` -> `TaskController@update`
  - GET `/tasks/delete` -> `TaskController@delete`
  - POST `/tasks/toggle` -> `TaskController@toggleComplete`; POST `/tasks/star` -> `TaskController@toggleImportant`
  - GET `/tasks/search` -> `TaskController@search`

- Controller ([app/Controllers/TaskController.php](app/Controllers/TaskController.php)):
  - `index()`: requireAuth, doc `filter|list` tu query, goi `Task::getTasksByUserId()` lay ds theo filter, lay lists + `Task::getTaskCounts()`, render `tasks/index`.
  - `create()` / `store()`: tai lists, validate title, goi `Task::create()` (user_id, list_id, due_date, important), flash & redirect.
  - `edit()` / `update()`: check quyen qua `Task::findById()` (user_id), update title/description/list/due_date/important.
  - `delete()`: goi `Task::delete($taskId, $userId)`, flash & redirect theo context.
  - `toggleComplete()` / `toggleImportant()`: POST, goi model toggle, redirect giu filter/list.
  - `search()`: GET `q`, goi `Task::searchTasks($userId, $q)`, reuse view `tasks/index` hien ket qua full page.

- Model ([app/Models/Task.php](app/Models/Task.php)):
  - `getTasksByUserId($userId, $filter, $listId)`: tra ds task theo filter inbox/my-day/important/planned hoac listId.
  - `create()`, `update()`, `delete()`: prepared statements kem `user_id` trong WHERE de tranh truy cap cheo.
  - `toggleComplete()`, `toggleImportant()`: UPDATE voi dieu kien user_id.
  - `getTaskCounts($userId, $lists)`: dem so task cho sidebar (inbox, special filters, tung list).
  - `searchTasks($userId, $q)`: LIKE `%q%` tren title/description.

- View ([app/Views/tasks/index.php](app/Views/tasks/index.php)):
  - Render danh sach task theo filter/list/search, nut toggle complete/important, edit, delete.
  - Sidebar su dung `$taskCounts` de hien badge; form search o navbar GET `/tasks/search`.

Doan code mau:

```php
// TaskController@index - rut gon
$tasks = Task::getTasksByUserId($this->user['id'], $_GET['filter'] ?? null, $_GET['list'] ?? null);
$lists = TodoList::getListsByUserId($this->user['id']);
$taskCounts = Task::getTaskCounts($this->user['id'], $lists);
return $this->view('tasks/index', compact('tasks', 'lists', 'taskCounts'));
```

```php
// Task::toggleComplete
public static function toggleComplete($taskId, $userId) {
  $db = static::getDB();
  $stmt = $db->prepare('UPDATE tasks SET is_completed = NOT is_completed WHERE id = ? AND user_id = ?');
  return $stmt->execute([$taskId, $userId]);
}
```

## Flow chi tiet: Lists
- Route (public/index.php):
  - GET `/lists/create` -> `ListController@create`; POST `/lists/create` -> `ListController@store`
  - GET `/lists/edit` -> `ListController@edit`; POST `/lists/edit` -> `ListController@update`
  - GET `/lists/delete` -> `ListController@delete`

- Controller ([app/Controllers/ListController.php](app/Controllers/ListController.php)):
  - `create()/store()`: requireAuth, validate name, goi `TodoList::create($userId, $name)`, flash & redirect home.
  - `edit()/update()`: check so huu qua `TodoList::findById($id, $userId)`, cap nhat ten list.
  - `delete()`: xac minh so huu, goi `TodoList::delete($id, $userId)`, flash & redirect.
  - Luon nap lists + `Task::getTaskCounts()` de sidebar dong bo.

- Model ([app/Models/TodoList.php](app/Models/TodoList.php)):
  - CRUD list kem dieu kien `user_id` trong WHERE.
  - `getListsByUserId($userId)`: tra ve ds list cua user de render sidebar/profile.

- View:
  - [app/Views/lists/create.php](app/Views/lists/create.php) va [app/Views/lists/edit.php](app/Views/lists/edit.php) hien form name, submit POST.
  - Sidebar trong layout su dung lists + counts; profile page liet ke lists kem actions.

Doan code mau:

```php
// ListController@store
$name = trim($_POST['name'] ?? '');
if ($name === '') {
  Session::flash('error', 'List name khong duoc de trong');
  return $this->redirect('/lists/create');
}
$ok = TodoList::create($this->user['id'], $name);
Session::flash($ok ? 'success' : 'error', $ok ? 'Tao list thanh cong' : 'Tao list that bai');
return $this->redirect('/');
```

## Flow chi tiet: Auth (Login/Register/Logout)
- Route (public/index.php):
  - GET `/login` -> `AuthController@showLogin`; POST `/login` -> `AuthController@login`
  - GET `/register` -> `AuthController@showRegister`; POST `/register` -> `AuthController@register`
  - GET `/logout` -> `AuthController@logout`

- Controller ([app/Controllers/AuthController.php](app/Controllers/AuthController.php)):
  - `showLogin()` / `showRegister()`: requireGuest, render form.
  - `login()`: requireGuest, doc email/password, `User::verify($email, $password)`, set session `user_id`, flash, redirect home.
  - `register()`: validate name/email/password, check email ton tai, `User::create(...)` (hash bcrypt), set session, flash, redirect home.
  - `logout()`: destroy session, flash, redirect login.

- Model ([app/Models/User.php](app/Models/User.php)):
  - `findByEmail()`, `findById()`, `create()` (password_hash), `verify()` (password_verify + tra user record).

- View:
  - [app/Views/auth/login.php](app/Views/auth/login.php) va [app/Views/auth/register.php](app/Views/auth/register.php) form POST, hien flash error/success tu Session.

Doan code mau:

```php
// AuthController@login - rut gon
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$user = User::verify($email, $password);
if ($user) {
  Session::set('user_id', $user['id']);
  Session::flash('success', 'Dang nhap thanh cong');
  return $this->redirect('/');
}
Session::flash('error', 'Email hoac mat khau khong dung');
return $this->redirect('/login');
```

## Ban do file chinh
- Entry point & route map: [public/index.php](public/index.php) — khai bao route auth, tasks, lists, profile, search va goi `$router->dispatch()`.
- Core layer:
  - [app/Core/Router.php](app/Core/Router.php) — luu danh sach route GET/POST, `dispatch()` tim controller + method tu URI, tra 404 neu khong khop.
  - [app/Core/Controller.php](app/Core/Controller.php) — helper `view()`, `redirect()`, `requireAuth()`, `requireGuest()` dung chung cho controllers.
  - [app/Core/Session.php](app/Core/Session.php) — dong goi session start/get/set/flash, dung cho thong bao mot lan va auth.
  - [app/Core/Model.php](app/Core/Model.php) — tao ket noi PDO (MySQL, utf8mb4, ERRMODE_EXCEPTION, disable emulate prepares), cache singleton DB.
- Cau hinh: [Config/database.php](Config/database.php) — host/dbname/username/password dung cho `Model`.
- Controllers:
  - [app/Controllers/AuthController.php](app/Controllers/AuthController.php) — form login/register, validate, flash error/success, tao/destroy session user.
  - [app/Controllers/TaskController.php](app/Controllers/TaskController.php) — trang chu tasks, filter inbox/my-day/important/planned/listId, CRUD, toggle complete/important, flash + redirect giu filter hien tai, method `search()` tim kiem va hien thi ket qua ra view `tasks/index`.
  - [app/Controllers/ListController.php](app/Controllers/ListController.php) — tao/sua/xoa custom list, tai sidebar data (lists, taskCounts) de render.
  - [app/Controllers/UserController.php](app/Controllers/UserController.php) — trang profile, gom thong ke task.
- Models:
  - [app/Models/Task.php](app/Models/Task.php) — truy van tasks theo user + filter, CRUD, `toggleComplete()`, `toggleImportant()`, dem task theo filter/list, thong ke, method `searchTasks()` su dung `LIKE` de tim kiem tuong doi theo title/description.
  - [app/Models/TodoList.php](app/Models/TodoList.php) — CRUD list theo user (co check user_id o WHERE).
  - [app/Models/User.php](app/Models/User.php) — tim theo email/id, tao user (hash bcrypt), verify dang nhap.
- Views:
  - Layout: [app/Views/layout.php](app/Views/layout.php) — navbar co form search (`.search-box`) method GET, sidebar, flash, chen `$content` tu view con, tai CSS/JS.
  - Tasks: [app/Views/tasks/index.php](app/Views/tasks/index.php) render danh sach, nut toggle/important/delete, nut add task, search result; [app/Views/tasks/create.php](app/Views/tasks/create.php) form tao task (chon list, due date, important).
  - Lists: [app/Views/lists/create.php](app/Views/lists/create.php), [app/Views/lists/edit.php](app/Views/lists/edit.php) (khong doc o day nhưng layout tuong tu), User: [app/Views/user/profile.php](app/Views/user/profile.php) hien thong ke.
- Assets: [public/css](public/css) cho layout/UI, [public/js/main.js](public/js/main.js) (xu ly UI), uploads anh trong [public/uploads](public/uploads).

## Flow chuc nang chinh
- Dang ky/Dang nhap:
  - Route `/login` va `/register` vao `AuthController`. GET: hien form; POST: lay form data, validate, goi `User::verify()` hoac `User::create()`, set flash + session user, redirect.
- Xem danh sach task (home `/` hay `/tasks`):
  - `TaskController::index()` yeu cau dang nhap, doc `$_GET['filter'|'list']`, dat tieu de, goi `Task::getTasksByUserId()` voi filter, lay lists + dem task (`Task::getTaskCounts()`), render `tasks/index.php`.
- Tao task:
  - GET `/tasks/create`: tai lists + counts, pre-select list tu query, render form create view.
  - POST `/tasks/create`: validate tieu de, goi `Task::create()` (gan user_id, list_id, due_date, is_important), flash success/fail, redirect ve list dang xem.
- Sua/Xoa task:
  - `edit`: check task thuoc user qua `Task::findById()`, POST update thong tin, giu list/important/due_date.
  - `delete`: goi `Task::delete()` voi user_id, flash ket qua, redirect.
- Tim kiem (Search):
  - Nguoi dung nhap tu khoa vao o Search tren Navbar va nhan Enter (submit form).
  - Trinh duyet gui request GET den `/tasks/search?q=keyword`.
  - `TaskController::search()` lay tu khoa, goi `Task::searchTasks()` (query SQL `LIKE %keyword%`) de tim kiem.
  - Controller tra ve view `tasks/index.php` (tai su dung giao dien trang chu) de hien thi danh sach ket qua day du (Full Page)
- Toggle trang thai va important:
  - `toggle` va `star` route goi `Task::toggleComplete()` / `Task::toggleImportant()`; sau do redirect keo theo `filter`/`list` de khong mat context.
- List tuy chinh:
  - `ListController::create/edit/delete` kiem tra session, check so huu list qua `TodoList::findById()`, cap nhat DB va quay ve trang chu hoac list vua sua.
- Profile/thong ke:
  - `UserController::profile()` lay user, lists, counts va `Task::getStatistics()` (tong, completed, incomplete, important, ngay tao/cap nhat) de render profile view.

## Nut code de chu y
- Routing tap trung va 404 fallback trong `Router::dispatch()` giup tranh logic phan tan.
- Bao ve quyen truy cap: tat ca controller thao tac data goi `requireAuth()`; cac truy van Model luon kem `user_id` trong WHERE (ngan cross-user access).
- Reuse View: `tasks/index.php` duoc tai su dung cho ca trang chu, cac trang filter (My Day, Important) va trang ket qua tim kiem.
- SQL Search: Su dung toan tu `LIKE` voi wildcard `%` de tim kiem tuong doi.
- Flash message mot lan trong [app/Core/Session.php](app/Core/Session.php) giup thong bao sau redirect.
- Filter task linh dong trong `Task::getTasksByUserId()` xu ly ca inbox (NULL/0), special list (important/my-day/planned), va custom list id.
- Dem so luong task cho sidebar bang `Task::getTaskCounts()` de hien badge theo tung filter/list.



## Ghi chu van hanh
- Khi them route moi: khai bao tai [public/index.php](public/index.php) va tao method tuong ung trong controller.
- Khi them model moi: ke thua `App\Core\Model` de co san ket noi PDO va quy uoc fetch associative.
- Khi render view: goi `$this->view('path', $data)` tu controller, view con su dung `ob_start()` -> gan `$content` -> include layout.
- De thay doi UI: sua CSS trong [public/css](public/css) va layout trong [app/Views/layout.php](app/Views/layout.php).
- De thay doi UI Search: sua CSS phan `.search-box` trong `alter_style.css` hoac `style.css`.


