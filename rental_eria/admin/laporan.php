<?php
session_start();
error_reporting(0);
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');
if(strlen($_SESSION['alogin'])==0)
{
    header('location:index.php');
}
else {
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">

    <title>Rental Mobil | Admin Laporan</title>

    <!-- Font awesome -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- Sandstone Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Bootstrap Datatables -->
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <!-- Admin Style -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap{
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
    </style>
    <script type="text/javascript">
    function valid()
    {
        if(document.laporan.akhir.value < document.laporan.awal.value)
        {
            alert("Tanggal akhir harus lebih besar dari tanggal awal!");
            return false;
        }
        return true;
    }
    </script>
</head>

<body>
    <?php include('includes/header.php'); ?>

    <div class="ts-main-content">
        <?php include('includes/leftbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="page-title">Laporan</h2>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form method="get" name="laporan" onSubmit="return valid();">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label>Tanggal Awal</label>
                                    <input type="date" class="form-control" name="awal" required>
                                </div>
                                <div class="col-sm-4">
                                    <label>Tanggal Akhir</label>
                                    <input type="date" class="form-control" name="akhir" required>
                                </div>
                                <div class="col-sm-4">
                                    <label>&nbsp;</label><br/>
                                    <input type="submit" name="submit" value="Lihat Laporan" class="btn btn-primary">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php
                if(isset($_GET['submit'])) {
                    $no = 0;
                    $mulai = $_GET['awal'];
                    $selesai = $_GET['akhir'];
                    $stt = "Selesai"; // Status yang akan dicari

                    $sqlsewa = "SELECT booking.*, mobil.*, merek.*, users.* 
                                FROM booking 
                                JOIN mobil ON booking.id_mobil = mobil.id_mobil 
                                JOIN merek ON mobil.id_merek = merek.id_merek 
                                JOIN users ON booking.email = users.email 
                                WHERE booking.status = '$stt' 
                                AND booking.tgl_booking BETWEEN '$mulai' AND '$selesai'";

                    $querysewa = mysqli_query($koneksidb, $sqlsewa);

                    if(mysqli_num_rows($querysewa) > 0) {
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">Laporan Sewa Tanggal <?php echo IndonesiaTgl($mulai); ?> sampai <?php echo IndonesiaTgl($selesai); ?></div>
                    <div class="panel-body">
                        <table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Sewa</th>
                                    <th>Tanggal Sewa</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($result = mysqli_fetch_array($querysewa)) {
                                $biayamobil = $result['durasi'] * $result['harga'];
                                $total = $result['driver'] + $biayamobil;
                                $no++;
                            ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo htmlentities($result['kode_booking']); ?></td>
                                    <td><?php echo IndonesiaTgl(htmlentities($result['tgl_booking'])); ?></td>
                                    <td><?php echo format_rupiah($total); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group">
                    <a href="laporan_cetak.php?awal=<?php echo $mulai; ?>&akhir=<?php echo $selesai; ?>" target="_blank" class="btn btn-primary">Cetak</a>
                </div>
                <?php 
                    } else {
                        echo '<div class="alert alert-warning">Tidak ada data yang ditemukan pada rentang tanggal tersebut.</div>';
                    }
                }
                ?>

            </div>
        </div>
    </div>

    <!-- Loading Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
<?php } ?>
