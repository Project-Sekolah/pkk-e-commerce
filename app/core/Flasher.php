<?php

class Flasher
{
  public static function setFlash($pesan, $aksi, $tipe)
  {
    $_SESSION["flash"] = [
      "pesan" => $pesan,
      "aksi" => $aksi,
      "tipe" => $tipe,
    ];
  }

  public static function flash()
  {
    if (isset($_SESSION["flash"])) {
      echo '
      <script>
        Swal.fire({
          title: "' .
        $_SESSION["flash"]["pesan"] .
        '",
          text: "' .
        $_SESSION["flash"]["aksi"] .
        '",
          icon: "' .
        $_SESSION["flash"]["tipe"] .
        '",
          confirmButtonText: "OK"
        });
        </script>
      ';
      unset($_SESSION["flash"]);
    }
  }
}
