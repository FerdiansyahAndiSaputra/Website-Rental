<?php
session_start();
error_reporting(0);
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');

if (strlen($_SESSION['ulogin']) == 0) { 
    header('location:index.php');
    exit();
} else {

    if (isset($_POST['submit'])) {
        $fromdate = $_POST['fromdate'];
        $todate = $_POST['todate'];
        $durasi = $_POST['durasi'];
        $pickup = $_POST['pickup'];
        $vid = $_POST['vid'];
        $email = $_POST['email'];
        $biayadriver = $_POST['biayadriver'];
        $kode = buatKode("booking", "TRX");
        $status = "Menunggu Pembayaran";
        $tgl = date('Y-m-d');

        // Validasi input
        if (empty($fromdate) || empty($todate) || empty($durasi) || empty($vid)) {
            echo "<script>alert('Data tidak lengkap. Harap isi semua kolom.');</script>";
            exit();
        }

        // Insert data booking
        $stmt = $koneksidb->prepare("INSERT INTO booking (kode_booking, id_mobil, tgl_mulai, tgl_selesai, durasi, driver, status, email, pickup, tgl_booking) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $kode, $vid, $fromdate, $todate, $durasi, $biayadriver, $status, $email, $pickup, $tgl);
        $success = $stmt->execute();

        if ($success) {
            $tglmulai = strtotime($fromdate);
            $query_values = [];

            for ($cek = 0; $cek < $durasi; $cek++) {
                $jmlhari = 86400 * $cek;
                $tgl = $tglmulai + $jmlhari;
                $tglhasil = date("Y-m-d", $tgl);
                $query_values[] = "('$kode', '$vid', '$tglhasil', '$status')";
            }

            $sql1 = "INSERT INTO cek_booking (kode_booking, id_mobil, tgl_booking, status) VALUES " . implode(',', $query_values);
            mysqli_query($koneksidb, $sql1);

            echo "<script>alert('Mobil berhasil disewa.');</script>";
            echo "<script type='text/javascript'> document.location = 'booking_detail.php?kode=$kode'; </script>";
        } else {
            echo "<script>alert('Ooops, terjadi kesalahan. Silahkan coba lagi.');</script>";
        }
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mutiara Motor Car Rental Portal</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="assets/css/style.css" type="text/css">
<link rel="stylesheet" href="assets/css/owl.carousel.css" type="text/css">
<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css">
<link href="assets/css/slick.css" rel="stylesheet">
<link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
<link href="assets/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet" id="switcher-css" type="text/css" href="assets/switcher/css/switcher.css" media="all" />
<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/red.css" title="red" media="all" data-default-color="true" />
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
</head>
<body>

<?php include('includes/colorswitcher.php');?>
<?php include('includes/header.php');?>

<div>
    <br/>
    <center><h3>Mobil Tersedia untuk disewa.</h3></center>
    <hr>
</div>
<?php
$email = $_SESSION['ulogin']; 
$vid = $_GET['vid'];
$mulai = $_GET['mulai'];
$selesai = $_GET['selesai'];
$driver = $_GET['driver'];
$pickup = $_GET['pickup'];
$start = new DateTime($mulai);
$finish = new DateTime($selesai);
$int = $start->diff($finish);
$dur = $int->days;
$durasi = $dur + 1;

// Menarik biaya driver dari database
$sqldriver = "SELECT * FROM tblpages WHERE id='0'";
$querydriver = mysqli_query($koneksidb, $sqldriver);
$resultdriver = mysqli_fetch_array($querydriver);
$drive = $resultdriver['detail'];
$drivercharges = ($driver == "1") ? $drive * $durasi : 0;

$sql1 = "SELECT mobil.*,merek.* FROM mobil,merek WHERE merek.id_merek=mobil.id_merek and mobil.id_mobil='$vid'";
$query1 = mysqli_query($koneksidb, $sql1);
$result = mysqli_fetch_array($query1);
$harga = $result['harga'];
$totalmobil = $durasi * $harga;
$totalsewa = $totalmobil + $drivercharges;
?>
<section class="user_profile inner_pages">
<div class="container">
<div class="col-md-6 col-sm-8">
      <div class="product-listing-img"><img src="admin/img/vehicleimages/<?php echo htmlentities($result['image1']);?>" class="img-responsive" alt="Image" /></div>
      <div class="product-listing-content">
        <h5><?php echo htmlentities($result['nama_merek']);?> , <?php echo htmlentities($result['nama_mobil']);?></h5>
        <p class="list-price"><?php echo htmlentities(format_rupiah($result['harga']));?> / Hari</p>
        <ul>
          <li><i class="fa fa-user" aria-hidden="true"></i><?php echo htmlentities($result['seating']);?> Seats</li>
          <li><i class="fa fa-calendar" aria-hidden="true"></i><?php echo htmlentities($result['tahun']);?></li>
          <li><i class="fa fa-car" aria-hidden="true"></i><?php echo htmlentities($result['bb']);?></li>
        </ul>
      </div>    
</div>

<div class="user_profile_info">    
    <div class="col-md-12 col-sm-10">
    <form method="post" name="sewa" onSubmit="return valid();"> 
        <input type="hidden" class="form-control" name="vid" value="<?php echo $vid;?>" required>
        <input type="hidden" class="form-control" name="email" value="<?php echo $email;?>" required>
        <div class="form-group">
        <label>Tanggal Mulai</label>
            <input type="date" class="form-control" name="fromdate" value="<?php echo $mulai;?>" readonly>
        </div>
        <div class="form-group">
        <label>Tanggal Selesai</label>
            <input type="date" class="form-control" name="todate" value="<?php echo $selesai;?>" readonly>
        </div>
        <div class="form-group">
        <label>Durasi</label>
            <input type="text" class="form-control" name="durasi" value="<?php echo $durasi;?> Hari" readonly>
        </div>
        <div class="form-group">
        <label>Metode Pickup</label>
            <input type="text" class="form-control" name="pickup" value="<?php echo $pickup;?>" readonly>
        </div>
        <div class="form-group">
        <label>Biaya Mobil (<?php echo $durasi;?> Hari)</label><br/>
            <input type="text" class="form-control" name="biayamobil" value="<?php echo format_rupiah($totalmobil);?>" readonly>
        </div>
        <div class="form-group">
        <label>Biaya Driver (<?php echo $durasi;?> Hari)</label><br/>
            <input type="hidden" class="form-control" name="biayadriver" value="<?php echo $drivercharges;?>" readonly>
            <input type="text" class="form-control" name="driver" value="<?php echo format_rupiah($drivercharges);?>" readonly>
        </div>
        <div class="form-group">
        <label>Total Biaya Sewa</label><br/>
            <input type="text" class="form-control" name="total" value="<?php echo format_rupiah($totalsewa);?>" readonly>
        </div>
        <br/>            
        <div class="form-group">
            <input type="submit" name="submit" value="Sewa" class="btn btn-block">
        </div>
    </form>
    </div>
    </div>
  </div>
</section>

<?php include('includes/footer.php');?>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script> 
<script src="assets/js/interface.js"></script> 
<script src="assets/switcher/js/switcher.js"></script>
<script src="assets/js/bootstrap-slider.min.js"></script> 
<script src="assets/js/slick.min.js"></script> 
<script src="assets/js/owl.carousel.min.js"></script>
</body>
</html>
