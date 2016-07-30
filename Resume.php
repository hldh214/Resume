<?php
/**
 * Class Resume
 */
class Resume
{
    /**
     * @var array config
     */
    private static $config = [
        'zhaopin_headers' => [
            'Cookie' => '',
        ],
        '51job_headers' => [
            'Cookie' => '',
        ],
        'zhaopin_resumeId' => '',
        '51job_resumeId' => '',
    ];

    /**
     * 基于 file_get_contents 的 GET / POST(x-www-form-urlencoded) 请求
     *
     * @param string $url
     * @param array  $headers
     * @param array  $data
     * @param string $method
     * @return string
     */
    public static function request($url, $headers = null, $data = null)
    {
        $opts = [
            'http' => [
                'method' => empty($data) ? 'GET' : 'POST',
                'header' => "",
            ]
        ];

        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $opts['http']['header'] .= "{$key}: {$value}\r\n";
            }
        }

        if (!empty($data)) {
            $opts['http']['header'] .= "Content-type: application/x-www-form-urlencoded\r\n";
            $opts['http']['header'] .= "Content-length:" . strlen(http_build_query($data)) . "\r\n";
            $opts['http']['content'] = http_build_query($data);
        }

        $opts['http']['header'] .= "\r\n";

        $opts = stream_context_create($opts);

        return file_get_contents($url, false, $opts);
    }

    /**
     * 简历刷新核心逻辑
     */
    public function refresh()
    {
        // 智联招聘简历刷新
        $res = self::request(
            'http://m.zhaopin.com/resume/refreshresume',
            self::$config['zhaopin_headers'],
            ['resumeId' => self::$config['zhaopin_resumeId']]
        );
        $res = json_decode($res, true);
        //print_r($res);exit;
        $result = $res['StatusDescription'] == '成功' ?: 0;
        echo $result;

        // 前程无忧简历刷新
        $res = self::request(
            'http://my.51job.com/cv/CResume/RefreshResume.php?Read=0',
            self::$config['51job_headers'],
            ['ReSumeID' => self::$config['51job_resumeId']]
        );
        $res = iconv('gbk', 'utf-8', $res);
        //echo $res;exit;

        $result = preg_match('/成功/', $res) ?: 0;
        echo $result;
    }
}

$r = new Resume();
$r->refresh();
