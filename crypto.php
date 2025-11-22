<?php
// crypto.php

const CIPHER_METHOD = 'AES-256-CBC';

// ключ НУЖНО хранить вне репозитория, в .env / отдельном конфиге
const ENCRYPTION_KEY = 'оченнь_длинный_случайный_ключ_32+_символа';

// Шифрование пароля (строка → base64)
function encryptPassword(string $plainPassword): string
{
    $ivLength = openssl_cipher_iv_length(CIPHER_METHOD);
    $iv = random_bytes($ivLength);

    $ciphertext = openssl_encrypt(
        $plainPassword,
        CIPHER_METHOD,
        ENCRYPTION_KEY,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($ciphertext === false) {
        throw new Exception('Ошибка шифрования пароля');
    }

    // Сохраняем IV + шифртекст вместе
    return base64_encode($iv . $ciphertext);
}

// Расшифровка пароля (base64 → строка)
function decryptPassword(?string $stored): string
{
    if (empty($stored)) {
        return '';
    }

    $data = base64_decode($stored, true);
    if ($data === false) {
        return '';
    }

    $ivLength = openssl_cipher_iv_length(CIPHER_METHOD);
    $iv = substr($data, 0, $ivLength);
    $ciphertext = substr($data, $ivLength);

    $plain = openssl_decrypt(
        $ciphertext,
        CIPHER_METHOD,
        ENCRYPTION_KEY,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($plain === false) {
        return '';
    }

    return $plain;
}
