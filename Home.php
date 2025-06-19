<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CHEFER - Chef Website Template</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Emblema+One&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- Spinner End -->

    <!-- Header Start -->
    <?php
    session_start();
    include 'koneksi.php';

    // Proses form jika disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_menu'])) {
        // Jika belum ada transaksi aktif, buat transaksi baru
        if (empty($_SESSION['current_transaction']['id'])) {
            mysqli_query($koneksi, "INSERT INTO transaksi(total) VALUES (0)");
            $newId = mysqli_insert_id($koneksi);
            $_SESSION['current_transaction'] = [
                'id' => $newId,
                'items' => []
            ];
        }

        $idTransaksi = $_SESSION['current_transaction']['id'];
        $idMenu = (int) $_POST['id_menu'];
        $jumlah = (int) $_POST['jumlah'];

        // Ambil data menu
        $q = mysqli_query($koneksi, "SELECT nama_menu, harga FROM menu WHERE id_menu = $idMenu");
        $menu = mysqli_fetch_assoc($q);
        $subtotal = $menu['harga'] * $jumlah;

        // Simpan ke database detail transaksi
        mysqli_query($koneksi, "INSERT INTO detail_transaksi(id_transaksi,id_menu,jumlah,subtotal)
                            VALUES ($idTransaksi,$idMenu,$jumlah,$subtotal)");

        // Simpan ke session
        $_SESSION['current_transaction']['items'][] = [
            'nama_menu' => $menu['nama_menu'],
            'harga' => $menu['harga'],
            'jumlah' => $jumlah,
            'subtotal' => $subtotal
        ];
    }

    // Proses simpan total transaksi
    if (isset($_POST['simpan_transaksi'])) {
        if (!empty($_SESSION['current_transaction']['items'])) {
            $idTransaksi = $_SESSION['current_transaction']['id'];
            $total = array_sum(array_column($_SESSION['current_transaction']['items'], 'subtotal'));
            mysqli_query($koneksi, "UPDATE transaksi SET total = $total WHERE id_transaksi = $idTransaksi");
            unset($_SESSION['current_transaction']);
            header("Location: input-transaksi.php?success=1");
            exit;
        }
    }

    // Data untuk HTML
    $menuList = mysqli_query($koneksi, "SELECT * FROM menu");
    $menuOptions = "";
    while ($row = mysqli_fetch_assoc($menuList)) {
        $menuOptions .= "<option value='{$row['id_menu']}' data-harga='{$row['harga']}'>" .
            "{$row['nama_menu']} (Rp " . number_format($row['harga'], 0, ',', '.') . ")</option>";
    }

    // Output sebagai variabel
    $transactionId = $_SESSION['current_transaction']['id'] ?? 'Belum dibuat';
    $transactionItems = $_SESSION['current_transaction']['items'] ?? [];
    $totalTransaksi = number_format(array_sum(array_column($transactionItems, 'subtotal')), 0, ',', '.');
    ?>

    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Input Transaksi</title>
        <style>


            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            h1,
            h2 {
                color: #333;
                border-bottom: 2px solid #007bff;
                padding-bottom: 10px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #555;
            }

            select,
            input[type="number"],
            input[type="text"] {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
            }

            button {
                background-color: #007bff;
                color: white;
                padding: 12px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                margin-right: 10px;
            }

            button:hover {
                background-color: #0056b3;
            }

            .btn-success {
                background-color: #28a745;
            }

            .btn-success:hover {
                background-color: #218838;
            }

            .transaction-info {
                background-color: #e9ecef;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            .items-table th,
            .items-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
            }

            .items-table th {
                background-color: #f8f9fa;
                font-weight: bold;
            }

            .total-row {
                font-weight: bold;
                background-color: #e9ecef;
            }

            .success-message {
                background-color: #d4edda;
                color: #155724;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
                border: 1px solid #c3e6cb;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>Input Transaksi</h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    ✓ Transaksi berhasil disimpan!
                </div>
            <?php endif; ?>

            <div class="transaction-info">
                <strong>ID Transaksi:</strong> <?php echo $transactionId; ?>
            </div>

            <form method="POST" action="">
                <h2>Tambah Item</h2>

                <div class="form-group">
                    <label for="id_menu">Pilih Menu:</label>
                    <select name="id_menu" id="id_menu" required>
                        <option value="">-- Pilih Menu --</option>
                        <?php echo $menuOptions; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah:</label>
                    <input type="number" name="jumlah" id="jumlah" min="1" value="1" required>
                </div>

                <div class="form-group">
                    <label for="subtotal">Subtotal:</label>
                    <input type="text" id="subtotal" readonly placeholder="Rp 0">
                </div>

                <button type="submit">Tambah ke Transaksi</button>
            </form>

            <?php if (!empty($transactionItems)): ?>
                <h2>Item dalam Transaksi</h2>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactionItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nama_menu']); ?></td>
                                <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $item['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong>Rp <?php echo $totalTransaksi; ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <form method="POST" action="" style="margin-top: 20px;">
                    <button type="submit" name="simpan_transaksi" class="btn-success">
                        Simpan Transaksi
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const select = document.getElementById('id_menu');
                const jumlah = document.getElementById('jumlah');
                const subtotal = document.getElementById('subtotal');

                function updateSubtotal() {
                    const selectedOption = select.selectedOptions[0];
                    const harga = parseInt(selectedOption?.dataset.harga) || 0;
                    const qty = parseInt(jumlah.value) || 0;
                    const total = harga * qty;
                    subtotal.value = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : 'Rp 0';
                }

                select.addEventListener('change', updateSubtotal);
                jumlah.addEventListener('input', updateSubtotal);
                updateSubtotal();
            });
        </script>
    </body>

    </html>
    <!-- Hero End -->

    <!-- Menu Start -->
    <div class="container-fluid menu py-5 px-0">
        <div class="mb-5 text-center wow fadeIn" data-wow-delay="0.1s" style="max-width: 700px; margin: auto;">
            <h5 class="section-title">Our Menu</h5>
            <h1 class="display-3 mb-0">Hands Craft More Than Meals</h1>
        </div>
        <div class="tab-class text-center">
            <ul class="nav nav-pills d-inline-flex justify-content-center bg-dark text-uppercase rounded-pill mb-5 wow fadeIn" data-wow-delay="0.2s">
                <li class="nav-item">
                    <a class="nav-link rounded-pill text-white active" data-bs-toggle="pill" href="#tab-1">Breakfast</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill text-white" data-bs-toggle="pill" href="#tab-2">Launch</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill text-white" data-bs-toggle="pill" href="#tab-3">Dinner</a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="tab-1" class="tab-pane fade show p-0 active">
                    <div class="row g-0">
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.1s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-1.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.2s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-2.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.3s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-3.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.4s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-4.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.5s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-5.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.6s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-6.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.7s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-7.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 wow fadeIn" data-wow-delay="0.8s">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-8.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="tab-2" class="tab-pane fade p-0">
                    <div class="row g-0">
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-2.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-3.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-4.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-5.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-6.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-7.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-8.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-1.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="tab-3" class="tab-pane fade p-0">
                    <div class="row g-0">
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-3.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-4.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-5.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-6.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-7.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-8.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-1.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="position-relative">
                                <img class="img-fluid" src="img/menu-2.jpg" alt="">
                                <div class="position-absolute bottom-0 end-0 mb-4 me-4 py-1 px-3 bg-dark rounded-pill text-primary">
                                    BBQ Chicken</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Menu End -->

    <!-- Instagram Start -->
    <div class="container-fluid position-relative instagram p-0 mt-5">
        <a href="" class="d-flex align-items-center justify-content-center position-absolute top-50 start-50 translate-middle bg-white rounded-circle" style="width: 100px; height: 100px; z-index: 1;">
            <i class="fab fa-instagram fa-2x text-secondary"></i>
        </a>
        <div class="row g-0">
            <div class="col-lg-2 col-md-3 col-sm-4 wow fadeIn" data-wow-delay="0.1s">
                <img class="img-fluid" src="img/menu-2.jpg" alt="">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 wow fadeIn" data-wow-delay="0.2s">
                <img class="img-fluid" src="img/menu-3.jpg" alt="">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 wow fadeIn" data-wow-delay="0.3s">
                <img class="img-fluid" src="img/menu-4.jpg" alt="">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 wow fadeIn" data-wow-delay="0.4s">
                <img class="img-fluid" src="img/menu-5.jpg" alt="">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 wow fadeIn" data-wow-delay="0.5s">
                <img class="img-fluid" src="img/menu-6.jpg" alt="">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 wow fadeIn" data-wow-delay="0.6s">
                <img class="img-fluid" src="img/menu-7.jpg" alt="">
            </div>
        </div>
    </div>
    <!-- Instagram End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-secondary px-5">
        <div class="row gx-5 wow fadeIn" data-wow-delay="0.1s">
            <div class="col-lg-8 col-md-6">
                <div class="row gx-5">
                    <div class="col-lg-4 col-md-12 pt-5 mb-5">
                        <h3 class="text-light mb-4">Get In Touch</h3>
                        <div class="d-flex mb-2">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <p class="mb-0">123 Street, New York, USA</p>
                        </div>
                        <div class="d-flex mb-2">
                            <i class="bi bi-envelope-open text-primary me-2"></i>
                            <p class="mb-0">info@example.com</p>
                        </div>
                        <div class="d-flex mb-2">
                            <i class="bi bi-telephone text-primary me-2"></i>
                            <p class="mb-0">+012 345 67890</p>
                        </div>
                        <div class="d-flex mt-4">
                            <a class="btn btn-primary btn-square rounded-circle me-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-primary btn-square rounded-circle me-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-primary btn-square rounded-circle me-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-primary btn-square rounded-circle" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 pt-0 pt-lg-5 mb-5">
                        <h3 class="text-light mb-4">Quick Links</h3>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Home</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>About Us</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Food Menu</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Our Chefs</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Latest Blog</a>
                            <a class="text-secondary" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Contact Us</a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 pt-0 pt-lg-5 mb-5">
                        <h3 class="text-light mb-4">More Links</h3>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Home</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>About Us</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Food Menu</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Our Chefs</a>
                            <a class="text-secondary mb-2" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Latest Blog</a>
                            <a class="text-secondary" href="#"><i class="bi bi-arrow-right text-primary me-2"></i>Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 p-5" style="background: #111111;">
                    <h3 class="text-white mb-4">Newsletter</h3>
                    <h6 class="text-uppercase text-light mb-2">Subscribe Our Newsletter</h6>
                    <p class="small text-secondary">Amet justo diam dolor rebum lorem sit stet sea justo kasd</p>
                    <form action="">
                        <div class="input-group">
                            <input type="text" class="form-control border-white p-3" placeholder="Your Email">
                            <button class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4 py-lg-0 px-5" style="background: #111111;">
        <div class="row gx-5">
            <div class="col-lg-8">
                <div class="py-lg-4 text-center">
                    <p class="text-secondary mb-0">&copy; <a class="text-light fw-bold" href="#">Your Site Name</a>. All Rights Reserved. Distributed by <a class="text-light fw-bold" href="https://themewagon.com">ThemeWagon</a></p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="py-lg-4 text-center credit">
                    <p class="text-light mb-0">Designed by <a class="text-light fw-bold" target="_blank" href="https://htmlcodex.com">HTML Codex</a></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-dark py-3 fs-4 back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>