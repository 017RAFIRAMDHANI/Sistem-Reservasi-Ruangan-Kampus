<?php
class UserController extends Controller
{
    public function index(): void
    {
        requireRole(['admin']);
        $pageTitle = 'Kelola Akun Pengguna';
        $result = (new UserModel())->allWithRole();
        $this->view('users/index', compact('pageTitle', 'result'));
    }

    public function form(): void
    {
        requireRole(['admin']);

        $userModel = new UserModel();
        $pageTitle = 'Form Pengguna';
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $roles = getRoleOptions();
        $departments = getDepartmentOptions();
        $userData = [
            'name' => '',
            'email' => '',
            'role_id' => 3,
            'phone' => '',
            'nim_nidn' => '',
            'department_id' => '',
        ];

        if ($id > 0) {
            $userData = $userModel->find($id) ?: $userData;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'role_id' => (int) ($_POST['role_id'] ?? 0),
                'phone' => trim($_POST['phone'] ?? ''),
                'nim_nidn' => trim($_POST['nim_nidn'] ?? ''),
                'department_id' => (int) ($_POST['department_id'] ?? 0),
            ];
            $password = $_POST['password'] ?? '';

            if ($form['name'] === '' || $form['email'] === '' || $form['role_id'] <= 0 || $form['department_id'] <= 0) {
                setFlash('error', 'Nama, email, role, dan prodi atau unit wajib diisi.');
                redirect($id > 0 ? 'user_form.php?id=' . $id : 'user_form.php');
            }

            if ($id > 0) {
                $hashed = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
                $userModel->update($id, $form, $hashed);
                setFlash('success', 'Akun pengguna berhasil diperbarui.');
            } else {
                if ($password === '') {
                    setFlash('error', 'Password wajib diisi untuk akun baru.');
                    redirect('user_form.php');
                }

                $form['password'] = password_hash($password, PASSWORD_DEFAULT);
                $userModel->create($form);
                setFlash('success', 'Akun pengguna berhasil ditambahkan.');
            }

            redirect('users.php');
        }

        $this->view('users/form', compact('pageTitle', 'id', 'roles', 'departments', 'userData'));
    }

    public function delete(): void
    {
        requireRole(['admin']);

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $current = currentUser();

        if ($id > 0 && $id !== (int) $current['id']) {
            (new UserModel())->delete($id);
            setFlash('success', 'Akun berhasil dihapus.');
        } else {
            setFlash('error', 'Akun ini tidak dapat dihapus.');
        }

        redirect('users.php');
    }
}
