<?php
class RoomController extends Controller
{
    public function index(): void
    {
        requireRole(['admin']);
        $pageTitle = 'Kelola Data Ruangan';
        $result = (new RoomModel())->allWithBuilding();
        $this->view('rooms/index', compact('pageTitle', 'result'));
    }

    public function form(): void
    {
        requireRole(['admin']);

        $roomModel = new RoomModel();
        $pageTitle = 'Form Ruangan';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $buildings = getBuildingOptions();
        $room = [
            'name' => '',
            'building_id' => '',
            'floor' => '',
            'capacity' => '',
            'status' => 'aktif',
            'description' => '',
        ];

        if ($id > 0) {
            $room = $roomModel->find($id) ?: $room;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'building_id' => (int)($_POST['building_id'] ?? 0),
                'floor' => trim($_POST['floor'] ?? ''),
                'capacity' => (int)($_POST['capacity'] ?? 0),
                'status' => $_POST['status'] ?? 'aktif',
                'description' => trim($_POST['description'] ?? ''),
            ];

            if ($data['name'] === '' || $data['building_id'] <= 0 || $data['floor'] === '' || $data['capacity'] <= 0) {
                setFlash('error', 'Nama ruangan, gedung, lantai, dan kapasitas wajib diisi.');
                redirect($id > 0 ? 'room_form.php?id=' . $id : 'room_form.php');
            }

            if ($id > 0) {
                $roomModel->update($id, $data);
            } else {
                $roomModel->create($data);
            }

            setFlash('success', $id > 0 ? 'Data ruangan berhasil diperbarui.' : 'Data ruangan berhasil ditambahkan.');
            redirect('rooms.php');
        }

        $this->view('rooms/form', compact('pageTitle', 'id', 'buildings', 'room'));
    }

    public function delete(): void
    {
        requireRole(['admin']);

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            (new RoomModel())->delete($id);
            setFlash('success', 'Data ruangan berhasil dihapus.');
        }
        redirect('rooms.php');
    }
}
