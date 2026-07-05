<?php
function detect_video_type($url) {
    if (strpos($url, 'tiktok.com')    !== false) return 'tiktok';
    if (strpos($url, 'youtube.com')   !== false) return 'youtube';
    if (strpos($url, 'youtu.be')      !== false) return 'youtube';
    if (strpos($url, 'instagram.com') !== false) return 'instagram';
    if (strpos($url, 'facebook.com')  !== false) return 'facebook';
    if (strpos($url, 'fb.watch')      !== false) return 'facebook';
    if (strpos($url, 'twitter.com')   !== false) return 'x';
    if (strpos($url, 'x.com')         !== false) return 'x';
    return 'other';
}

function get_embed_url($url) {
    $type = detect_video_type($url);
    switch ($type) {
        case 'tiktok':
            if (preg_match('/\/video\/(\d+)/', $url, $m))
                return 'https://www.tiktok.com/embed/v2/' . $m[1];
            return null;
        case 'youtube':
            if (strpos($url, 'youtu.be/') !== false) {
                $id = explode('?', explode('youtu.be/', $url)[1])[0];
            } elseif (strpos($url, '/shorts/') !== false) {
                preg_match('/\/shorts\/([a-zA-Z0-9_-]+)/', $url, $m);
                $id = $m[1] ?? null;
            } else {
                parse_str(parse_url($url, PHP_URL_QUERY), $p);
                $id = $p['v'] ?? null;
            }
            return $id ? 'https://www.youtube.com/embed/' . $id : null;
        case 'instagram':
            if (preg_match('/\/reel\/([A-Za-z0-9_-]+)/', $url, $m))
                return 'https://www.instagram.com/reel/' . $m[1] . '/embed';
            if (preg_match('/\/p\/([A-Za-z0-9_-]+)/', $url, $m))
                return 'https://www.instagram.com/p/' . $m[1] . '/embed';
            return null;
        case 'facebook':
            return 'https://www.facebook.com/plugins/video.php?href=' . urlencode($url) . '&show_text=false&width=560';
        default:
            return null;
    }
}

function render_video($url, $height = 480) {
    if (!$url) { echo '<div class="video-error"><i class="ti ti-video-off"></i><p>No video submitted.</p></div>'; return; }
    $embed = get_embed_url($url);
    $type  = detect_video_type($url);
    if (!$embed) {
        echo '<div class="video-error"><i class="ti ti-video-off"></i><p>Cannot embed this link. <a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">Open original</a></p></div>';
        return;
    }
    $class = $type === 'tiktok' ? 'video-wrap video-tiktok' : 'video-wrap';
    echo '<div class="' . $class . '"><iframe src="' . htmlspecialchars($embed) . '" width="100%" height="' . $height . '" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen title="Submitted video"></iframe></div>';
    echo '<p class="video-link"><i class="ti ti-external-link"></i><a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener">Open on ' . ucfirst($type) . '</a></p>';
}

function platform_badge($type) {
    $labels = ['tiktok'=>'TikTok','youtube'=>'YouTube','instagram'=>'Instagram','facebook'=>'Facebook','x'=>'X','other'=>'Other'];
    $label  = $labels[$type] ?? 'Other';
    return '<span class="badge badge-' . $type . '">' . $label . '</span>';
}
