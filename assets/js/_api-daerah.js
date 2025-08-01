// Fungsi untuk mengambil data kabupaten
function getKabupaten() {
  fetch("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/32.json")
    .then((response) => response.json())
    .then((kabupaten) => {
      let kabupatenSelect = document.getElementById("kabupaten");
      kabupatenSelect.innerHTML =
        '<option value="">Pilih Kabupaten/Kota</option>';

      kabupaten.forEach((kab) => {
        let option = document.createElement("option");
        option.value = kab.name;
        option.textContent = kab.name;
        option.setAttribute("data-id", kab.id);
        kabupatenSelect.appendChild(option);
      });
    });
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
        option.value = kec.name;
        option.textContent = kec.name;
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
