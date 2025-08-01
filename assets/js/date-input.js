// Tambahkan event listener untuk semua input dengan class date-input
document.querySelectorAll(".date-input").forEach(function (input) {
  input.addEventListener("input", function (e) {
    let value = e.target.value.replace(/\D/g, ""); // Hapus semua karakter non-digit
    let formattedDate = "";

    if (value.length > 0) {
      // Format dd
      formattedDate = value.substring(0, 2);

      if (value.length > 2) {
        // Format dd-mm
        formattedDate += "-" + value.substring(2, 4);

        if (value.length > 4) {
          // Format dd-mm-yyyy
          formattedDate += "-" + value.substring(4, 8);
        }
      }
    }

    e.target.value = formattedDate;
  });

  // Tambahkan validasi untuk memastikan tanggal yang valid
  input.addEventListener("blur", function (e) {
    let value = e.target.value;
    if (value.length > 0) {
      let parts = value.split("-");
      if (parts.length === 3) {
        let day = parseInt(parts[0]);
        let month = parseInt(parts[1]);
        let year = parseInt(parts[2]);

        // Validasi tanggal tanpa batas atas untuk tahun
        if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900) {
          Swal.fire({
            icon: "error",
            title: "Tanggal Tidak Valid!",
            text: "Gunakan format dd-mm-yyyy dengan tahun minimal 1900",
          });
          e.target.value = "";
        }
      }
    }
  });
});
