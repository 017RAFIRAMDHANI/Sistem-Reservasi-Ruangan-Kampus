<?php
class AuthController extends Controller
{
    public function home(): void
    {
        if (isLoggedIn()) {
            redirect('dashboard.php');
        }
        redirect('login.php');
    }

    public function login(): void
    {
        if (isLoggedIn()) {
            redirect('dashboard.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                setFlash('error', 'Email dan password wajib diisi.');
                redirect('login.php');
            }

            $user = (new UserModel())->findByEmailWithRole($email);

            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']);
                $_SESSION['user'] = $user;
                setFlash('success', 'Login berhasil. Selamat datang, ' . $user['name'] . '.');
                redirect('dashboard.php');
            }

            setFlash('error', 'Email atau password salah.');
            redirect('login.php');
        }

        $flash = getFlash();
        $this->view('auth/login', compact('flash'), false);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        setFlash('success', 'Anda berhasil logout.');
        redirect('login.php');
    }
}
