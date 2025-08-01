// Fungsi untuk mengambil data kabupaten
async function getKabupaten() {
  const javaProvinces = [31, 32, 33, 34, 35, 36]; // Kode provinsi di Pulau Jawa
  let allKabupaten = [];

  try {
    // Mengambil data kabupaten dari semua provinsi di Jawa
    for (const provinceId of javaProvinces) {
      const response = await fetch(
        `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`
      );
      const kabupaten = await response.json();
      allKabupaten = [...allKabupaten, ...kabupaten];
    }

    // Mengurutkan kabupaten berdasarkan nama
    allKabupaten.sort((a, b) => a.name.localeCompare(b.name));

    let kabupatenSelect = document.getElementById("kabupaten");
    kabupatenSelect.innerHTML =
      '<option value="">Pilih Kabupaten/Kota</option>';

    allKabupaten.forEach((kab) => {
      let option = document.createElement("option");
      let formattedText;

      // Cek apakah nama dimulai dengan "KOTA"
      if (kab.name.toUpperCase().startsWith("KOTA")) {
        formattedText =
          "Kota " +
          kab.name
            .replace(/KOTA /i, "")
            .toLowerCase()
            .split(" ")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(" ");
      } else {
        formattedText = kab.name
          .replace(/KABUPATEN /i, "")
          .toLowerCase()
          .split(" ")
          .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
          .join(" ");
      }

      option.value = formattedText;
      option.textContent = formattedText;
      option.setAttribute("data-id", kab.id);
      kabupatenSelect.appendChild(option);
    });
  } catch (error) {
    console.error("Error fetching kabupaten:", error);
  }
}

// Fungsi untuk mengambil data kecamatan berdasarkan kabupaten
function getKecamatan(kabupatenId) {
  fetch(
    `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${kabupatenId}.json`
  )
    .then((response) => response.json())
    .then((kecamatan) => {
      let kecamatanSelect = document.getElementById("kecamatan");
      kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';

      kecamatan.forEach((kec) => {
        let option = document.createElement("option");
        let formattedText = kec.name
          .toLowerCase()
          .split(" ")
          .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
          .join(" ");
        option.value = formattedText;
        option.textContent = formattedText;
        kecamatanSelect.appendChild(option);
      });
    });
}

// Load kabupaten saat halaman dimuat
document.addEventListener("DOMContentLoaded", getKabupaten);

// Event listener untuk perubahan kabupaten
document.getElementById("kabupaten").addEventListener("change", function () {
  if (this.value) {
    const selectedOption = this.options[this.selectedIndex];
    const kabupatenId = selectedOption.getAttribute("data-id");
    getKecamatan(kabupatenId);
  }
});
