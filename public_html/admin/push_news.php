<?php
/**
 * Send FCM push for a published news item to all tokens in `tokens`.
 * Uses the same legacy HTTP API as admin/notification.php.
 */
function naradmuni_send_news_push($con, $title, $body, $imageFile, $newsurl) {
    global $urlroot, $publicroot;

    if (!$title || !$newsurl) {
        return false;
    }

    $sql = "SELECT `token` FROM `tokens` WHERE `token` IS NOT NULL AND `token` != ''";
    $result = mysqli_query($con, $sql);
    if (!$result || mysqli_num_rows($result) < 1) {
        return false;
    }

    $array = [];
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['token'])) {
            $array[] = $row['token'];
        }
    }
    if (!$array) {
        return false;
    }

    $site = rtrim($publicroot ?: 'https://www.thenaradmuni.com/', '/') . '/';
    $assets = rtrim($urlroot ?: $site, '/') . '/';
    $icon = 'https://www.thenaradmuni.com/images/icon/AppIcon4x.png';
    // Prefer same-origin PWA icon when on localhost Next
    if (strpos($site, 'localhost') !== false || strpos($site, '127.0.0.1') !== false) {
        $icon = rtrim($site, '/') . '/icons/app-icon.png';
    }

    $imageUrl = '';
    if ($imageFile) {
        $imageUrl = $assets . 'images/news/' . $imageFile;
    }

    $msg = [
        'title' => $title,
        'body' => $body ? $body : $title,
        'sound' => 'https://www.thenaradmuni.com/sound/sound.mp3',
        'icon' => $icon,
        'image' => $imageUrl,
        'click_action' => $site . 'news/' . $newsurl,
    ];

    if (!defined('NARADMUNI_FCM_KEY')) {
        define(
            'NARADMUNI_FCM_KEY',
            'AAAAnf23Pf0:APA91bHnaKtqFnWUnu2K0CITgNWFXxWUuT3OVswnRgSJ17XwcGu9TVNClf74cC-APFD2JDw1KjVx7Fbxrqcb62cNv1TLg9nRQqnYkOCz_his7BJs0FSvVUPaRk0ql-xF4HI_o1kRxdZr'
        );
    }

    $chunks = array_chunk($array, 999);
    foreach ($chunks as $registrationIds) {
        $fields = [
            'registration_ids' => $registrationIds,
            'data' => $msg,
            'notification' => $msg,
            'priority' => 'high',
        ];
        $headers = [
            'Authorization: key=' . NARADMUNI_FCM_KEY,
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_exec($ch);
        curl_close($ch);
    }

    // Log in notification table (best-effort)
    $safeTitle = mysqli_real_escape_string($con, $title);
    $safeBody = mysqli_real_escape_string($con, $body ? $body : $title);
    $safeImg = mysqli_real_escape_string($con, $imageFile ? $imageFile : '');
    $safeLink = mysqli_real_escape_string($con, $site . 'news/' . $newsurl);
    $date = date('Y-m-d');
    $time = date('H:i:s');
    @mysqli_query(
        $con,
        "INSERT INTO `notification`(`title`, `description`, `image`, `link`, `date`, `time`)
         VALUES ('$safeTitle','$safeBody','$safeImg','$safeLink','$date','$time')"
    );

    return true;
}
