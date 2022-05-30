<?php
session_start();
$conn=mysqli_connect("localhost","root","","towahsweb");

//function query
function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

//function upload
function upload(){
	$namaFile = $_FILES['foto'] ['name'];
	$ukuranFile = $_FILES['foto'] ['size'];
	$error = $_FILES['foto'] ['error'];
	$tmpName = $_FILES['foto'] ['tmp_name'];



	$ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
	$ekstensiGambar = explode('.', $namaFile);
	$ekstensiGambar = strtolower(end($ekstensiGambar));

	if(!in_array($ekstensiGambar, $ekstensiGambarValid)){
		echo"
		<script>
		alert('yang anda upload bukan gambar!');
		</script>
		";
		return false;
	}

	if($ukuranFile > 1000000){
		echo"
		alert('file gambar terlalu besar!');
		</script>
		";
		return false;
	}
	$namaFileBaru = uniqid();
	$namaFileBaru .= '.';
	$namaFileBaru .=$ekstensiGambar;
	move_uploaded_file($tmpName, 'foto/'.$namaFileBaru);

	return $namaFileBaru;
}

//Menambah Barang Baru
if(isset($_POST['addnewbarang'])){
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];

    //gambar
    $allowed_extension = array('png','jpg');
    $nama = $_FILES['file']['name']; //ngambil nama gambar
    $dot = explode('.',$nama);
    $ekstensi = strtolower(End($dot)); //ngambil ekstensinya
    $ukuran = $_FILES['file']['size']; //ngtambil size filenya
    $file_tmp = $_FILES['file']['tmp_name']; //ngambil lokasi filenya

    //penamaan file --> enkripsi
    $image = md5(uniqid($nama, true) . time()).'.'.$ekstensi; //menggabungkan nama file dgn ekstensinya

    //proses upload gambar
    if(in_array($ekstensi, $allowed_extension) === true){
        //validasi ukuran file
        if($ukuran < 10000000){
            move_uploaded_file($file_tmp, 'images/'.$image);
            $addtotable = mysqli_query($conn, "insert into stokbrg (namabarang, deskripsi, stok, image) values('$namabarang','$deskripsi','$stok','$image')");
            if($addtotable){
                header('location:index.php');
            } else {
                echo 'gagal';
                header('location:index.php');
            }
    } else {
        //kalau filenya lebih dari 10mb
        echo '
        <script>
            alert("Ukuran terlalu besar");
            windows.location.href="index.php;
        </script>
        ';
    }
    } else {
        //kalau filenya tidak png/jpg
        echo '
        <script>
            alert("File harus png/jpg");
            windows.location.href="index.php;
        </script>
        ';
    }
};

//Menambah Barang Masuk
if(isset($_POST['barangmasuk'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstokskrg = mysqli_query($conn, "select * from stokbrg where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstokskrg);

    $cekstokskrg = $ambildatanya['stok'];
    $tmbhstokskrgdgnjmlh = $cekstokskrg+$qty;

    $addtotable = mysqli_query($conn, "insert into masuk (idbarang, keterangan, qty) values('$barangnya','$penerima','$qty')");
    $updatestokmasuk = mysqli_query($conn, "update stokbrg set stok='$tmbhstokskrgdgnjmlh' where idbarang='$barangnya'");
    if($addtomasuk&&$updatestokmasuk){
        header('location:masuk.php');
    } else {
        echo 'gagal';
        header('location:masuk.php');
    }
}

//Menambah Barang Keluar
if(isset($_POST['addbarangkeluar'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];

    $cekstokskrg = mysqli_query($conn, "select * from stokbrg where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstokskrg);

    $cekstokskrg = $ambildatanya['stok'];
    $tmbhstokskrgdgnjmlh = $cekstokskrg-$qty;

    $addtokeluar = mysqli_query($conn, "insert into keluar (idbarang, penerima, qty) values('$barangnya','$penerima','$qty')");
    $updatestokmasuk = mysqli_query($conn, "update stokbrg set stok='$tmbhstokskrgdgnjmlh' where idbarang='$barangnya'");
    if($addtokeluar&&$updatestokmasuk){
        header('location:keluar.php');
    } else {
        echo 'gagal';
        header('location:keluar.php');
    }
}

//update info barang
if(isset($_POST['updatebarang'])){
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $image = $_POST['images'];

    $allowed_extension = array('png','jpg');
    $nama = $_FILES['file']['name']; //ngambil nama gambar
    $dot = explode('.',$nama);
    $ekstensi = strtolower(End($dot)); //ngambil ekstensinya
    $ukuran = $_FILES['file']['size']; //ngtambil size filenya
    $file_tmp = $_FILES['file']['tmp_name']; //ngambil lokasi filenya

    $image = md5(uniqid($nama,true) . time()).'.'.$ekstensi; //menggabungkan nama file dgn ekstensinya
    move_uploaded_file($file_tmp, 'images/'.$image);
    $update = mysqli_query($conn, "update stokbrg set namabarang='$namabarang', deskripsi='$deskripsi', image='$image' where idbarang ='$idb'");
    if($update){
        header('location:index.php');
    } else {
    echo 'gagal';
    header('location:index.php');
    }
}


//menghapus barang dari stok
if(isset($_POST['hapusbarang'])){
    $idb = $_POST['idb'];

    $gambar =mysqli_query($conn, "select * from stokbrg where idbarang='$idb'");
    $get = mysqli_fetch_array($gambar);
    $img = 'images/'.$get['image'];
    unlink($img);

    $hapus = mysqli_query($conn, "delete from stokbrg where idbarang ='$idb'");
    if($hapus){
        header('location:index.php');
    } else {
        echo 'gagal';
        header('location:index.php');
    }
}

//mengubah data barang masuk
if(isset($_POST['updatebarangmasuk'])){
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $deskripsi = $_POST['keterangan'];
    $qty = $_POST['qty'];
    
    $lihatstok = mysqli_query($conn, "select * from stokbrg where idbarang ='$idb'");
    $stoknya = mysqli_fetch_array($lihatstok);
    $stokskrg = $stoknya['stok'];

    $qtyskrg = mysqli_query($conn, "select * from masuk where idmasuk ='$idm'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty-$qtyskrg;
        $kurangin = $stokskrg - $selisih; 
        $kurangistoknya = mysqli_query($conn, "update stokbrg set stok='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set qty='$qty', keterangan = '$deskripsi' where idmasuk='$idm'");
        if($kurangistoknya&&$updatenya){
            header('location:masuk.php');
        } else {
            echo 'gagal';
            header('location:masuk.php');
        }
    } else {
        $selisih = $qtyskrg-$qty;
        $kurangin = $stokskrg + $selisih; 
        $kurangistoknya = mysqli_query($conn, "update stokbrg set stok='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set qty='$qty', keterangan = '$deskripsi' where idmasuk='$idm'");
        if($kurangistoknya&&$updatenya){
            header('location:masuk.php');
        } else {
            echo 'gagal';
            header('location:masuk.php');
        }
    }
}

//menghapus barang masuk
if(isset($_POST['hapusbarangmasuk'])){
    $idb = $_POST['idb'];
    $qty = $_POST['kty'];
    $idm = $_POST['idm'];

    $getdatastok = mysqli_query($conn, "select * from stokbrg where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastok);
    $stok = $data['stok'];

    $selisih = $stok-$qty;

    $update = mysqli_query($conn," update stokbrg set stok='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from masuk where idm='$idm'");

    if($update&&$hapusdata){
        header('location:masuk.php');
    } else {
        header('location:masuk.php');
    }
}

//mengunah data barang keluar
if(isset($_POST['updatebarangkeluar'])){
    $idb = $_POST['idb'];
    $idk = $_POST['idk'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    
    $lihatstok = mysqli_query($conn, "select * from stokbrg where idbarang ='$idb'");
    $stoknya = mysqli_fetch_array($lihatstok);
    $stokskrg = $stoknya['stok'];

    $qtyskrg = mysqli_query($conn, "select * from keluar where idkeluar ='$idk'");
    $qtynya = mysqli_fetch_array($qtyskrg);
    $qtyskrg = $qtynya['qty'];

    if($qty>$qtyskrg){
        $selisih = $qty-$qtyskrg;
        $kurangin = $stokskrg - $selisih; 
        $kurangistoknya = mysqli_query($conn, "update stokbrg set stok='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima = '$penerima' where idkeluar='$idk'");
        if($kurangistoknya&&$updatenya){
            header('location:keluar.php');
        } else {
            echo 'gagal';
            header('location:keluar.php');
        }
    } else {
        $selisih = $qtyskrg-$qty;
        $kurangin = $stokskrg + $selisih; 
        $kurangistoknya = mysqli_query($conn, "update stokbrg set stok='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set qty='$qty', penerima = '$penerima' where idkeluar='$idk'");
        if($kurangistoknya&&$updatenya){
            header('location:keluar.php');
        } else {
            echo 'gagal';
            header('location:keluar.php');
        }
    }
}

//menghapus barang keluar
if(isset($_POST['hapusbarangkeluar'])){
    $idb = $_POST['idb'];
    $qty = $_POST['kty'];
    $idk = $_POST['idk'];

    $getdatastok = mysqli_query($conn, "select * from stokbrg where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastok);
    $stok = $data['stok'];

    $selisih = $stok+$qty;

    $update = mysqli_query($conn," update stokbrg set stok='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from keluar where idkeluar='$idk'");

    if($update&&$hapusdata){
        header('location:keluar.php');
    } else {
        header('location:keluar.php');
    }
}

?>