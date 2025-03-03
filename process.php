<?php
header('Content-Type: text/plain; charset=utf-8');

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    die("无效的输入数据");
}

$action = $input['action'];
$email = $input['email'];
$apiKey = $input['apiKey'];
$domains = explode("\n", trim($input['domains']));
$records = explode("\n", trim($input['records']));

// 验证输入
if (empty($email) || empty($apiKey)) {
    die("请提供Email和API Key");
}

if (empty($domains)) {
    die("请提供至少一个域名");
}

// 设置header
$header = array(
    "X-Auth-Email:" . $email,
    "X-Auth-Key:" . $apiKey,
    "Content-Type:application/json"
);

function post_data($url, $post=null, $header=array(), $timeout=8, $https=0, $method='POST')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    if ($header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    if ($method != 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
        }
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}

foreach ($domains as $v_domain) {
    $v_domain = trim($v_domain);
    if (empty($v_domain)) continue;

    if ($action === 'add') {
        // 添加域名
        $url = "https://api.cloudflare.com/client/v4/zones";
        $post = json_encode([
            "name" => $v_domain,
            "jump_start" => true
        ]);

        $rs = post_data($url, $post, $header, 8, 1);
        $rs = json_decode($rs, true);

        if ($rs['success'] == false) {
            echo "添加域名 {$v_domain} 失败：{$rs['errors'][0]['message']}\n";
            continue;
        }

        $zoneid = $rs['result']['id'];
        echo "添加域名 {$v_domain} 成功，ID：{$zoneid}\n";

        // 添加解析记录
        foreach ($records as $v_record) {
            if (empty(trim($v_record))) continue;
            
            $record_detail = explode(',', $v_record);
            if (count($record_detail) < 3) continue;

            $name = strtolower($record_detail[0]);
            $type = strtoupper($record_detail[1]);
            $ip = $record_detail[2];
            
            $url_add_records = "https://api.cloudflare.com/client/v4/zones/$zoneid/dns_records";
            $post = json_encode([
                "type" => $type,
                "name" => $name,
                "content" => $ip,
                "ttl" => 3600,
                "priority" => 10,
                "proxied" => true
            ]);

            $add_records_rs = post_data($url_add_records, $post, $header, 8, 1);
            $rs = json_decode($add_records_rs, true);
            
            if ($rs['success'] == false) {
                echo "添加记录 {$name} 失败：{$rs['errors'][0]['message']}\n";
            } else {
                echo "添加记录 {$name} 成功\n";
            }
        }

        // 设置HTTPS
        if ($input['httpsEnabled']) {
            $url_https = "https://api.cloudflare.com/client/v4/zones/{$zoneid}/settings/always_use_https";
            $patch_data = json_encode(['value' => 'on']);
            $https_rs = post_data($url_https, $patch_data, $header, 8, 1, 'PATCH');
            $https_rs = json_decode($https_rs, true);

            if ($https_rs['success']) {
                echo "已开启始终使用HTTPS\n";
            } else {
                echo "开启HTTPS失败：{$https_rs['errors'][0]['message']}\n";
            }
        }

    } else if ($action === 'delete') {
        // 获取域名ID
        $url = "https://api.cloudflare.com/client/v4/zones?name=" . urlencode($v_domain);
        $rs = post_data($url, null, $header, 8, 1, 'GET');
        $rs = json_decode($rs, true);

        if (!$rs['success'] || empty($rs['result'])) {
            echo "未找到域名 {$v_domain}\n";
            continue;
        }

        $zoneid = $rs['result'][0]['id'];

        // 删除域名及其所有记录
        $url_delete = "https://api.cloudflare.com/client/v4/zones/{$zoneid}";
        $rs = post_data($url_delete, null, $header, 8, 1, 'DELETE');
        $rs = json_decode($rs, true);

        if ($rs['success']) {
            echo "成功删除域名 {$v_domain} 及其所有记录\n";
        } else {
            echo "删除域名 {$v_domain} 失败：{$rs['errors'][0]['message']}\n";
        }
    }
} 