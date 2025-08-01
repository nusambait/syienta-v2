<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

header('Content-Type: application/json');

try {
    // Ambil semua data dari database
    $query = mysqli_query($connect, "SELECT * FROM komite");
    if (!$query) {
        throw new Exception("Gagal mengambil data dari database: " . mysqli_error($connect));
    }

    $total_files = 0;
    $skipped_files = 0;
    $file_types = ['analisa', 'slik', 'lainnya'];

    while ($row = mysqli_fetch_assoc($query)) {
        foreach ($file_types as $type) {
            if ($row[$type]) {
                $old_file = $row[$type];
                $old_path = '../../../nusamba2/assets/doc/' . $type . '/' . $old_file;

                // Cek apakah file sudah memiliki format yang benar
                // Format yang benar: noreg-tgl-jam-detik-kodeunik.pdf
                if (preg_match('/^' . preg_quote($row['noreg'], '/') . '-\d{8}-\d{6}-[a-f0-9]{8}\.pdf$/', $old_file)) {
                    $skipped_files++;
                    continue;
                }

                if (file_exists($old_path)) {
                    // Generate nama file baru
                    $timestamp = date('Ymd-His');
                    $unique_code = substr(md5(uniqid()), 0, 8);
                    $new_filename = $row['noreg'] . '-' . $timestamp . '-' . $unique_code . '.pdf';
                    $new_path = '../../../nusamba2/assets/doc/' . $type . '/' . $new_filename;

                    // Rename file
                    if (rename($old_path, $new_path)) {
                        // Update database
                        $update_query = mysqli_query($connect, "UPDATE komite SET $type = '$new_filename' WHERE noreg = '{$row['noreg']}'");
                        if (!$update_query) {
                            throw new Exception("Gagal mengupdate database untuk file $old_file: " . mysqli_error($connect));
                        }
                        $total_files++;
                    } else {
                        throw new Exception("Gagal merename file $old_file");
                    }
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'total_files' => $total_files,
        'skipped_files' => $skipped_files,
        'message' => "Berhasil merubah $total_files file. Melewati $skipped_files file yang sudah benar formatnya."
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
