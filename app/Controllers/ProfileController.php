<?php
class ProfileController extends Controller
{
    public function index(): void
    {
        requireLogin();

        $userModel = new UserModel();
        $pageTitle = 'Kelola Profil';
        $user = currentUser();
        $departments = getDepartmentOptions();
        $currentData = $userModel->currentDepartmentId((int)$user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'nim_nidn' => trim($_POST['nim_nidn'] ?? ''),
                'department_id' => (int)($_POST['department_id'] ?? 0),
            ];
            $password = $_POST['password'] ?? '';

            if ($data['name'] === '' || $data['email'] === '' || $data['department_id'] <= 0) {
                setFlash('error', 'Nama, email, dan prodi atau unit wajib diisi.');
                redirect('profile.php');
            }

            $hashed = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
            $userModel->updateProfile((int)$user['id'], $data, $hashed);
            refreshSessionUser((int)$user['id']);
            setFlash('success', 'Profil berhasil diperbarui.');
            redirect('profile.php');
        }

        $this->view('profile/index', compact('pageTitle', 'user', 'departments', 'currentData'));
    }
}
