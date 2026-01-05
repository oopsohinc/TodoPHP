# Huong dan flow du an

Tai lieu nay tom tat cach luu thong tin va xu ly request theo kieu MVC trong TodoPHP, giai thich vai tro tung file chinh va cac diem code quan trong.

## Tong quan request
- Trinh duyet goi duong dan (vi du `/tasks/create`) hoac submit form tim kiem.
- Moi request vao [public/index.php](public/index.php); o day khoi tao `Router` va khai bao tat ca URL.
- `Router` map URL sang controller method (GET/POST) va goi thuc thi trong [app/Core/Router.php](app/Core/Router.php).
- Controller xu ly quyen (Session), goi Model doc/ghi DB, chuan bi data va render View qua `Controller::view()` tu [app/Core/Controller.php](app/Core/Controller.php).
- View dung output buffering dua noi dung vao [app/Views/layout.php](app/Views/layout.php), layout chen flash message, sidebar va assets -> tra HTML ve trinh duyet.

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


