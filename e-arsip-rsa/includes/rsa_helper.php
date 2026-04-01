<?php

/**
 * Fungsi untuk Generate Sepasang Kunci RSA
 * Menghasilkan Array berisi Kunci Privat & Kunci Publik.
 */
function rsa_generate_keys()
{
    $config = [
        "config" => "C:/xampp/php/extras/ssl/openssl.cnf",
        "digest_alg" => "sha512",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    // Buat resource kunci baru
    $res = openssl_pkey_new($config);
    if ($res === false) {
        return false;
    }

    // Ekstrak Kunci Privat
    openssl_pkey_export($res, $private_key, null, $config);

    // Ekstrak Kunci Publik
    $key_details = openssl_pkey_get_details($res);
    $public_key = $key_details["key"];

    return [
        'private_key' => $private_key,
        'public_key' => $public_key
    ];
}

/**
 * Fungsi Mengenkripsi File besar dengan RSA secara langsung secara potong-memotong (Chunking)
 * Peringatan: Sangat memakan resource cpu jika file besar!
 * @param string $source_file
 * @param string $dest_file
 * @param string $public_key_string
 */
function rsa_encrypt_file($source_file, $dest_file, $public_key_string)
{
    $public_key = openssl_pkey_get_public($public_key_string);
    if (!$public_key)
        return false;

    $key_details = openssl_pkey_get_details($public_key);
    $bits = $key_details['bits'];

    // Potongan blok (plain data).
    // Batas data PKCS1 padding = max(bit size/8) - 11. 
    $max_length = ($bits / 8) - 11;

    $fp_source = fopen($source_file, 'rb');
    $fp_dest = fopen($dest_file, 'wb');

    if (!$fp_source || !$fp_dest)
        return false;

    while (!feof($fp_source)) {
        $chunk = fread($fp_source, $max_length);
        if ($chunk === false || strlen($chunk) == 0)
            break;

        openssl_public_encrypt($chunk, $encrypted, $public_key);
        fwrite($fp_dest, $encrypted);
    }

    fclose($fp_source);
    fclose($fp_dest);

    return true;
}

/**
 * Fungsi Dekripsi File Enkripsi (.enc) yang telah diurai dengan chunk RSA ke file asli
 * @param string $source_file
 * @param string $dest_file
 * @param string $private_key_string
 */
function rsa_decrypt_file($source_file, $dest_file, $private_key_string)
{
    // Memuat kunci privat
    $private_key = openssl_pkey_get_private($private_key_string);
    if (!$private_key)
        return 'Kunci Privat tidak valid!';

    $key_details = openssl_pkey_get_details($private_key);
    $bits = $key_details['bits'];

    // Jika file menggunakan blok ciphertext 2048-bit, file readnya selalu 256 bytes per siklus
    $chunk_size = ($bits / 8);

    $fp_source = fopen($source_file, 'rb');
    $fp_dest = fopen($dest_file, 'wb');

    if (!$fp_source || !$fp_dest)
        return 'Gagal membuka file sumber/destinasi operasi.';

    while (!feof($fp_source)) {
        $chunk = fread($fp_source, $chunk_size);
        if ($chunk === false || strlen($chunk) == 0)
            break;

        $success = openssl_private_decrypt($chunk, $decrypted, $private_key);
        if (!$success) {
            fclose($fp_source);
            fclose($fp_dest);
            if (file_exists($dest_file))
                unlink($dest_file);
            return 'Dekripsi Gagal! Mungkin salah kunci atau file korup.';
        }

        fwrite($fp_dest, $decrypted);
    }

    fclose($fp_source);
    fclose($fp_dest);

    return true; // Sukses
}
?>