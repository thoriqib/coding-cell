<?php

namespace App\Gateway;

use Illuminate\Database\ConnectionInterface;

class GadgetGateway
{
    /**
     * @var ConnectionInterface
     */
    private $db;
    private $dataGadget =
    [
        "xiaomi" =>
        [
            [
                "nama" => "Redmi Note 9 Pro",
                "harga" => 3099000,
                "deskripsi" => "Redmi Note 9 Pro mengusung layar 6,67 inci (resolusi FHD Plus), kamera selfie 16 MP, chipset Snapdragon 720G, konfigurasi RAM dan media penyimpanan 6 GB/64 GB atau 8 GB/128 GB, baterai jumbo dengan kapasitas 5.020 mAh (fast charging 30W), serta fitur NFC.",
                "image" => "https://selular.id/wp-content/uploads/2020/03/Redmi-Note-9-Pro-Max.jpg"
            ],
            [
                "nama" => "Poco F2 Pro",
                "harga" => 6690000,
                "deskripsi" => "Xiaomi POCO F2 Pro ini memiliki layar 6.67 inci dengan resolusi 1080 x 2400 pixels, Quad-Camera 64MP, RAM 6GB/8GB dengan prosesor Snapdragon 865 dan baterai 4.700 mAh.",
                "image" => "https://d2pa5gi5n2e1an.cloudfront.net/global/images/product/mobilephones/Xiaomi_POCO_F2_Pro/Xiaomi_POCO_F2_Pro_L_1.jpg"
            ]
        ],
        "samsung" =>
        [
            [
                "nama" => "Galaxy A51",
                "harga" => 4299000,
                "deskripsi" => "Samsung Galaxy A51 merupakan handphone HP dengan kapasitas 4000mAh dan layar 6.5 inci yang dilengkapi dengan kamera belakang 48 + 12 + 5 + 5MP dengan tingkat densitas piksel sebesar 405ppi dan tampilan resolusi sebesar 1080 x 2400pixels. Dengan berat sebesar 172g, handphone HP ini memiliki prosesor Octa Core.",
                "image" => "https://askcell.co.id/image/cache/gambar/produk/Samsung/galaxy_a51_blue_1_1-1200x1200.jpg"
            ],
            [
                "nama" => "Galaxy M51",
                "harga" => 5500000,
                "deskripsi" => "Samsung Galaxy M51 ini memiliki layar 6.7 inci dengan resolusi FHD+ 1080 x 2340 pixel, quad kamera 64MP, RAM 6/8GB dengan Snapdragon 730G dan baterai Li-Po 7.000 mAh.",
                "image" => "https://d2pa5gi5n2e1an.cloudfront.net/global/images/product/mobilephones/Samsung_Galaxy_M51/Samsung_Galaxy_M51_L_1.jpg"
            ]
        ],
        "realme" =>
        [
            [
                "nama" => "Narzo 20 Pro",
                "harga" => 3199000,
                "deskripsi" => "Realme narzo 20 Pro hadir dengan layar punch-hole 6,5 inci, kapasitas baterai rendah dibanding narzo 20 series lainnya tetapi mendukung fast charging 65W SuperDart, SoC MediaTek Helio G95, quad kamera belakang 48MP dan kamera selfie 16MP.",
                "image" => "https://d2pa5gi5n2e1an.cloudfront.net/webp/global/images/product/mobilephones/Realme_narzo_20_Pro_ph/Realme_narzo_20_Pro_ph_L_1.jpg"
            ],
            [
                "nama" => "Realme 7",
                "harga" => 325000,
                "deskripsi" => "Realme 7 hadir dengan layar IPS LCD 6,5 inci dengan resolusi Full HD+ refresh rate 90Hz, chipset MediaTek Helio G95, baterai berkapasitas 5.000 mAh dan Dart Charge 30W.",
                "image" => "https://d2pa5gi5n2e1an.cloudfront.net/webp/global/images/product/mobilephones/Realme_narzo_20_Pro_ph/Realme_narzo_20_Pro_ph_L_1.jpg"
            ],
        ]


    ];

    public function __construct($dataGadget)
    {
        $this->db = app('db');
        $this->dataGadget = $dataGadget;
    }

    // Gadget
    function getGadget($nama)
    {
        $gadget = $this->dataGadget[$nama];

        if ($gadget) {
            return $gadget;
        }

        return null;
    }
}
