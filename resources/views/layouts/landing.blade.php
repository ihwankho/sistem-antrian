<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelayanan Terpadu Satu Pintu - Pengadilan Negeri Banyuwangi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Poppins', sans-serif; 
        }
        
        :root {
            --primary: #1a3a5f; 
            --secondary: #4CAF50; 
            --accent: #336021;
            --light: #f8f9fa; 
            --white: #ffffff; 
            --text: #333;
        }
        
        body { 
            background: #f8f9fa; 
            color: var(--text); 
            line-height: 1.6; 
        }
        
        /* Header Styles */
        .main-header { 
            background: var(--white); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
        }
        
        .header-top { 
            background: var(--primary); 
            color: var(--white); 
            padding: 12px 0; 
            font-size: 14px; 
        }
        
        .header-bottom { 
            padding: 15px 0; 
        }
        
        .logo { 
            width: 70px; 
            height: 70px; 
            background: var(--primary); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: var(--white); 
            font-size: 24px; 
            font-weight: bold; 
        }
        
        .logo-text h1 { 
            font-size: 22px; 
            color: var(--primary); 
            margin: 0; 
            font-weight: 700; 
        }
        
        .logo-text p { 
            font-size: 16px; 
            color: var(--accent); 
            margin: 0; 
        }
        
        .header-contact span { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        
        .header-social a { 
            color: var(--white); 
            text-decoration: none; 
            transition: all 0.3s ease; 
            margin-right: 15px;
        }
        
        .header-social a:hover { 
            color: var(--secondary); 
        }
        
        /* Footer Styles */
        .footer { 
            background: var(--primary); 
            color: var(--white); 
            padding: 70px 0 30px; 
        }
        
        .footer-column h3 { 
            font-size: 1.4rem; 
            margin-bottom: 25px; 
            position: relative; 
            padding-bottom: 10px; 
        }
        
        .footer-column h3::after { 
            content: ''; 
            position: absolute; 
            bottom: 0; 
            left: 0; 
            width: 50px; 
            height: 3px; 
            background: var(--secondary); 
        }
        
        .footer-column p, 
        .footer-column a { 
            color: #bdc3c7; 
            margin-bottom: 12px; 
            display: block; 
            text-decoration: none; 
            transition: all 0.3s ease; 
        }
        
        .footer-column a:hover { 
            color: var(--secondary); 
            padding-left: 5px; 
        }
        
        .footer-column i { 
            margin-right: 10px; 
            width: 20px; 
        }
        
        .footer-bottom { 
            text-align: center; 
            padding-top: 30px; 
            border-top: 1px solid rgba(255, 255, 255, 0.1); 
        }
        
        /* Custom spacing to maintain original layout */
        .header-contact-gap {
            gap: 25px;
        }
        
        .logo-gap {
            gap: 15px;
        }
        
        .footer-content-gap {
            gap: 40px;
            margin-bottom: 40px;
        }
        
        /* Footer content grid layout to match original */
        .footer-content {
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
        
        /* Responsive adjustments to maintain original behavior */
        @media (max-width: 768px) {
            .header-contact {
                flex-wrap: wrap;
            }
            
            .logo-text h1 {
                font-size: 18px;
            }
            
            .logo-text p {
                font-size: 14px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="main-header">
        <!-- Header Top Section -->
        <div class="header-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="header-contact d-flex header-contact-gap flex-wrap">
                                <span><i class="fas fa-envelope"></i> pnbanyuwangi@gmail.com</span>
                            </div>
                            <div class="header-social d-flex">
                                <a href="https://www.facebook.com/pn.bwi/"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.instagram.com/pnbanyuwangi/"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Bottom Section -->
        <div class="header-bottom">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center logo-gap">
                                <div class="logo">
                                    <span>PN</span>
                                </div>
                                <div class="logo-text">
                                    <h1>Pengadilan Negeri Banyuwangi</h1>
                                    <p>Sistem Antrean PTSP</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
    
    <footer class="footer">
        <div class="container-fluid">
            <div class="footer-content footer-content-gap">
                <div class="footer-column footer-info">
                    <h3>Pengadilan Negeri Banyuwangi</h3>
                    <p>Pengadilan Negeri Banyuwangi Kelas IB</p>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Adi Sucipto No.26, Taman Baru, Kec. Banyuwangi, Kabupaten Banyuwangi, Jawa Timur 68416</p>
                    <p><i class="fas fa-phone"></i> (0333) 421376</p>
                    <p><i class="fas fa-envelope"></i> pnbanyuwangi@gmail.com</p>
                </div>
                
                <div class="footer-column footer-map">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.199419268808!2d114.35923581539226!3d-8.18241488432313!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd15b001a118183%3A0x8074697a5188159b!2sPengadilan%20Negeri%20Banyuwangi!5e0!3m2!1sen!2sid!4v1680000000000!5m2!1sen!2sid" 
                        width="100%" 
                        height="250" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="footer-bottom">
                        <p class="mb-0">&copy; {{ date('Y') }} Pengadilan Negeri Banyuwangi Kelas IB. Hak Cipta Dilindungi.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>