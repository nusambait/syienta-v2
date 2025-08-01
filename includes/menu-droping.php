<div class="card-body">
    <div class="row justify-content-start g-3">
        <?php
        // Mendapatkan nama file saat ini
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <div class="col-md-2">
            <a href="<?php echo $base_url; ?>administrasi/edit-droping.php?noreg=<?php echo $noreg; ?>"
                class="btn <?php echo ($current_page == 'edit-droping.php') ? 'btn-primary active' : 'btn-outline-secondary'; ?> w-100">
                <i class="bi bi-people"></i> Data Droping
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?php echo $base_url; ?>administrasi/data-pendamping.php?noreg=<?php echo $noreg; ?>"
                class="btn <?php echo ($current_page == 'data-pendamping.php') ? 'btn-primary active' : 'btn-outline-secondary'; ?> w-100">
                <i class="bi bi-people"></i> Data Pendamping
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?php echo $base_url; ?>administrasi/data-penjamin.php?noreg=<?php echo $noreg; ?>"
                class="btn <?php echo ($current_page == 'data-penjamin.php') ? 'btn-primary active' : 'btn-outline-secondary'; ?> w-100">
                <i class="bi bi-person-check"></i> Data Penjamin
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?php echo $base_url; ?>administrasi/data-jaminan.php?noreg=<?php echo $noreg; ?>"
                class="btn <?php echo ($current_page == 'data-jaminan.php') ? 'btn-primary active' : 'btn-outline-secondary'; ?> w-100">
                <i class="bi bi-shield-check"></i> Data Jaminan
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?php echo $base_url; ?>administrasi/data-dokumen.php?noreg=<?php echo $noreg; ?>"
                class="btn <?php echo ($current_page == 'data-dokumen.php') ? 'btn-primary active' : 'btn-outline-secondary'; ?> w-100">
                <i class="bi bi-file-earmark-text"></i> Data Dokumen
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?php echo $base_url; ?>administrasi/data-komite.php?noreg=<?php echo $noreg; ?>"
                class="btn <?php echo ($current_page == 'data-komite.php') ? 'btn-primary active' : 'btn-outline-secondary'; ?> w-100">
                <i class="bi bi-check2-square"></i> Data Komite
            </a>
        </div>
    </div>
</div>